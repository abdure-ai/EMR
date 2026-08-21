<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\QueueEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class ReportIndex extends Component
{
    #[Url(history: true)]
    public string $preset = 'this_month';

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        if (! $this->from || ! $this->to) {
            $this->applyPreset($this->preset);
        }
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
        $this->applyPreset($preset);
        $this->dispatchChartUpdate();
    }

    public function updatedFrom(): void
    {
        $this->preset = 'custom';
        $this->dispatchChartUpdate();
    }

    public function updatedTo(): void
    {
        $this->preset = 'custom';
        $this->dispatchChartUpdate();
    }

    /**
     * Streams a CSV of every payment in the selected range. Kept as a plain
     * Livewire action (not a route) so it always reflects the same filters
     * currently on screen.
     */
    public function exportCsv()
    {
        abort_unless(auth()->user()->can('reports.view'), 403);

        [$from, $to] = $this->range();

        $payments = Payment::query()
            ->with(['invoice.patient', 'invoice.service', 'invoice.practitioner'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $filename = 'nesiha-revenue-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Invoice #', 'Patient', 'Type', 'Service', 'Practitioner', 'Method', 'Amount (ETB)']);

            foreach ($payments as $payment) {
                $invoice = $payment->invoice;
                fputcsv($handle, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $invoice?->invoice_number,
                    $invoice?->patient?->full_name,
                    $invoice?->type,
                    $invoice?->service?->name ?? '—',
                    $invoice?->practitioner?->name ?? '—',
                    $payment->method,
                    number_format((float) $payment->amount, 2),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function applyPreset(string $preset): void
    {
        [$from, $to] = match ($preset) {
            'today' => [today(), today()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_30_days' => [today()->subDays(29), today()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $this->from ? Carbon::parse($this->from) : today(),
                $this->to ? Carbon::parse($this->to) : today(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->from = $from->format('Y-m-d');
        $this->to = $to->format('Y-m-d');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function range(): array
    {
        $from = Carbon::parse($this->from ?: today())->startOfDay();
        $to = Carbon::parse($this->to ?: today())->endOfDay();

        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    protected function dispatchChartUpdate(): void
    {
        $trend = $this->revenueTrend();

        $this->dispatch('revenue-chart-updated', categories: $trend['categories'], series: $trend['series']);
    }

    /**
     * Daily buckets for ranges up to ~2 months; monthly buckets beyond that,
     * so a "This Year" report doesn't render a 365-tick x-axis. Bucketed in
     * PHP rather than via DATE_FORMAT/strftime so this works identically on
     * MySQL (production) and SQLite (tests).
     */
    protected function revenueTrend(): array
    {
        [$from, $to] = $this->range();
        $groupByMonth = $from->diffInDays($to) > 62;

        $rows = Payment::query()
            ->whereBetween('created_at', [$from, $to])
            ->get(['created_at', 'amount'])
            ->groupBy(fn ($payment) => $payment->created_at->format($groupByMonth ? 'Y-m' : 'Y-m-d'))
            ->map(fn ($group) => (float) $group->sum('amount'));

        $categories = [];
        $series = [];
        $cursor = $groupByMonth ? $from->copy()->startOfMonth() : $from->copy()->startOfDay();

        while ($cursor->lte($to)) {
            $key = $cursor->format($groupByMonth ? 'Y-m' : 'Y-m-d');
            $categories[] = $cursor->format($groupByMonth ? 'M Y' : 'M j');
            $series[] = (float) ($rows[$key] ?? 0);

            if ($groupByMonth) {
                $cursor->addMonth();
            } else {
                $cursor->addDay();
            }
        }

        return ['categories' => $categories, 'series' => $series];
    }

    protected function summary(): array
    {
        [$from, $to] = $this->range();

        return [
            'revenue' => (float) Payment::whereBetween('created_at', [$from, $to])->sum('amount'),
            'newPatients' => Patient::whereBetween('created_at', [$from, $to])->count(),
            'visitsCompleted' => QueueEntry::where('status', 'completed')
                ->whereBetween('completed_at', [$from, $to])->count(),
            'prescriptionsDispensed' => Prescription::where('status', 'dispensed')
                ->whereBetween('dispensed_at', [$from, $to])->count(),
            'outstanding' => (float) Invoice::where('status', 'pending')
                ->whereBetween('created_at', [$from, $to])->sum('total_amount'),
        ];
    }

    protected function revenueByService(): Collection
    {
        [$from, $to] = $this->range();

        return Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->leftJoin('services', 'services.id', '=', 'invoices.service_id')
            ->whereBetween('payments.created_at', [$from, $to])
            ->selectRaw("COALESCE(services.name, 'Registration Fee') as service_name, SUM(payments.amount) as total, COUNT(DISTINCT invoices.id) as invoice_count")
            ->groupBy('service_name')
            ->orderByDesc('total')
            ->get();
    }

    protected function revenueByPractitioner(): Collection
    {
        [$from, $to] = $this->range();

        return Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('users', 'users.id', '=', 'invoices.practitioner_id')
            ->whereBetween('payments.created_at', [$from, $to])
            ->selectRaw('users.id as practitioner_id, users.name as practitioner_name, SUM(payments.amount) as total, COUNT(DISTINCT invoices.id) as invoice_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();
    }

    protected function revenueByMethod(): Collection
    {
        [$from, $to] = $this->range();

        return Payment::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as payment_count')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();
    }

    public function render()
    {
        [$from, $to] = $this->range();

        return view('livewire.reports.report-index', [
            'summary' => $this->summary(),
            'trend' => $this->revenueTrend(),
            'byService' => $this->revenueByService(),
            'byPractitioner' => $this->revenueByPractitioner(),
            'byMethod' => $this->revenueByMethod(),
            'rangeLabel' => $from->isSameDay($to)
                ? $from->format('M j, Y')
                : $from->format('M j, Y').' – '.$to->format('M j, Y'),
        ])->extends('layouts.app')->title('Reports');
    }
}
