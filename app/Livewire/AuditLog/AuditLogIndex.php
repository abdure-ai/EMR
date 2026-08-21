<?php

namespace App\Livewire\AuditLog;

use App\Models\AuditLog;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $entityType = '';

    #[Url(history: true)]
    public string $action = '';

    #[Url(history: true)]
    public string $from = '';

    #[Url(history: true)]
    public string $to = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('audit.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEntityType(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'entityType', 'action', 'from', 'to');
        $this->resetPage();
    }

    public static function humanizeEntityType(string $fqcn): string
    {
        return Str::of(class_basename($fqcn))->headline()->toString();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $term = $this->search;

                $query->where(function ($q) use ($term) {
                    $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));

                    if (is_numeric($term)) {
                        $q->orWhere('entity_id', (int) $term);
                    }
                });
            })
            ->when($this->entityType, fn ($query) => $query->where('entity_type', $this->entityType))
            ->when($this->action, fn ($query) => $query->where('action', $this->action))
            ->when($this->from, fn ($query) => $query->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($query) => $query->whereDate('created_at', '<=', $this->to))
            ->latest('id')
            ->paginate(20);

        return view('livewire.audit-log.audit-log-index', [
            'logs' => $logs,
            'entityTypeOptions' => AuditLog::query()
                ->select('entity_type')
                ->distinct()
                ->orderBy('entity_type')
                ->pluck('entity_type'),
        ])->extends('layouts.app')->title('Audit Log');
    }
}
