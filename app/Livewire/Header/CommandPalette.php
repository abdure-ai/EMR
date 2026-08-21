<?php

namespace App\Livewire\Header;

use App\Helpers\MenuHelper;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Str;
use Livewire\Component;

class CommandPalette extends Component
{
    public string $query = '';

    protected function moduleLinks(): array
    {
        return collect(MenuHelper::getMainNavItems())
            ->reject(fn ($item) => isset($item['subItems']))
            ->values()
            ->all();
    }

    public function render()
    {
        $term = trim($this->query);
        $user = auth()->user();

        $modules = collect($this->moduleLinks());
        $patients = collect();
        $invoices = collect();

        if ($term !== '') {
            $modules = $modules->filter(fn ($item) => str_contains(Str::lower($item['name']), Str::lower($term)))->values();

            if (mb_strlen($term) >= 2 && $user->can('viewAny', Patient::class)) {
                $patients = Patient::query()
                    ->when($user->hasRole('Practitioner'), fn ($q) => $q->whereHas('queueEntries', fn ($qq) => $qq->where('practitioner_id', $user->id)
                        ->whereIn('status', ['waiting', 'with_practitioner'])))
                    ->where(function ($q) use ($term) {
                        $q->where('patient_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    })
                    ->limit(6)
                    ->get();
            }

            if (mb_strlen($term) >= 2 && $user->can('billing.view')) {
                $invoices = Invoice::where('invoice_number', 'like', "%{$term}%")->limit(5)->get();
            }
        }

        return view('livewire.header.command-palette', [
            'modules' => $modules,
            'patients' => $patients,
            'invoices' => $invoices,
            'term' => $term,
        ]);
    }
}
