@php
    $textareaClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $inputClass = $textareaClass;
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $categoryLabels = ['lab' => 'Lab', 'imaging' => 'Imaging', 'procedure' => 'Procedure'];
    $categoryColors = ['lab' => 'info', 'imaging' => 'primary', 'procedure' => 'warning'];
    $tabs = [
        'note' => [
            'label' => 'Patient Note',
            'icon' => '<path d="M4 3.5A1.5 1.5 0 0 1 5.5 2h5.379a1.5 1.5 0 0 1 1.06.44l2.622 2.621A1.5 1.5 0 0 1 15 6.121V16.5a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 4 16.5v-13Zm3 5a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5H7Zm0 3a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 0-1.5H7Z" />',
        ],
        'investigations' => [
            'label' => 'Ordered Investigation',
            'icon' => '<path fill-rule="evenodd" d="M7 2.75A.75.75 0 0 1 7.75 2h4.5a.75.75 0 0 1 0 1.5h-.5v2.6l3.256 6.716A2.75 2.75 0 0 1 12.53 17H7.47a2.75 2.75 0 0 1-2.476-4.184L8.25 6.1V3.5h-.5A.75.75 0 0 1 7 2.75Zm2.75.75v3a.75.75 0 0 1-.08.336L8.4 9.5h3.2l-1.27-2.664A.75.75 0 0 1 10.25 6.5v-3h-.5Z" clip-rule="evenodd" />',
        ],
        'medications' => [
            'label' => 'Medication',
            'icon' => '<path fill-rule="evenodd" d="M4.318 4.318a4.5 4.5 0 0 1 6.364 0l5 5a4.5 4.5 0 1 1-6.364 6.364l-5-5a4.5 4.5 0 0 1 0-6.364Zm4.5 1.415L7 7.55l5.5 5.5 1.818-1.818-5.5-5.5Z" clip-rule="evenodd" />',
        ],
        'results' => [
            'label' => 'Results',
            'icon' => '<path fill-rule="evenodd" d="M6.75 2A1.75 1.75 0 0 0 5 3.75v.25H4.5A1.5 1.5 0 0 0 3 5.5v10A1.5 1.5 0 0 0 4.5 17h9a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 4H13v-.25A1.75 1.75 0 0 0 11.25 2h-4.5ZM6.5 3.75a.25.25 0 0 1 .25-.25h4.5a.25.25 0 0 1 .25.25v.5h-5v-.5Zm6.03 5.03-4 4a.75.75 0 0 1-1.06 0l-1.5-1.5a.75.75 0 1 1 1.06-1.06l.97.97 3.47-3.47a.75.75 0 1 1 1.06 1.06Z" clip-rule="evenodd" />',
        ],
    ];
@endphp

<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $patient->full_name }}
                <span class="ms-2 font-mono text-sm text-gray-400">{{ $patient->patient_id }}</span>
                <x-ui.badge size="sm" variant="solid" :color="$encounter->status === 'completed' ? 'success' : 'warning'">
                    {{ str($encounter->status)->headline() }}
                </x-ui.badge>
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $encounter->created_at->format('Y-m-d, H:i') }}
                @if ($encounter->practitioner)
                    &middot; {{ $encounter->practitioner->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Back to patient</a>
    </div>

    <div class="space-y-6">
        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ tab: 'note' }">
            <div class="border-b border-gray-200 px-4 dark:border-gray-800 sm:px-6">
                <nav class="-mb-px flex flex-wrap gap-6">
                    @foreach ($tabs as $key => $tabData)
                        <button type="button" @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-brand-500 text-brand-500' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="flex items-center gap-1.5 border-b-2 py-3 text-sm font-medium transition">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">{!! $tabData['icon'] !!}</svg>
                            {{ $tabData['label'] }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-4 sm:p-6">
                @if ($canEdit)
                    <form wire:submit.prevent>
                        <div x-show="tab === 'note'">
                            <div class="mb-1.5 flex items-center justify-between">
                                <label for="patient_note" class="text-sm font-medium text-gray-700 dark:text-gray-400">Patient note</label>
                                @if ($noteSavedAt)
                                    <span class="text-xs text-success-500" wire:key="note-saved-{{ $noteSavedAt }}">Autosaved at {{ $noteSavedAt }}</span>
                                @endif
                            </div>
                            <textarea id="patient_note" wire:model.live.debounce.1000ms="patient_note" rows="8" class="{{ $textareaClass }}" placeholder="Chief complaint, history, examination findings..."></textarea>
                            <x-input-error :messages="$errors->get('patient_note')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-400">Saved as a draft automatically while you type.</p>
                        </div>
                        <div x-show="tab === 'investigations'" x-cloak>
                            <label class="{{ $labelClass }}">Ordered investigation</label>
                            <div class="space-y-5">
                                @forelse ($investigationCatalog as $category => $items)
                                    <div>
                                        <x-ui.badge size="sm" variant="solid" :color="$categoryColors[$category] ?? 'light'">
                                            {{ $categoryLabels[$category] ?? ucfirst($category) }}
                                        </x-ui.badge>
                                        <div class="mt-3 space-y-4">
                                            @foreach ($items->groupBy(fn ($i) => $i->subcategory ?: 'Other') as $subcategory => $subItems)
                                                <div>
                                                    <p class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $subcategory }}</p>
                                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                                        @foreach ($subItems as $item)
                                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:border-gray-700 dark:text-gray-300 dark:has-[:checked]:bg-brand-500/10">
                                                                <input type="checkbox" wire:model="selectedInvestigations" value="{{ $item->id }}" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                                                <span>{{ $item->name }}</span>
                                                                @if ($item->price !== null)
                                                                    <span class="ms-auto text-xs text-gray-400">{{ number_format($item->price, 2) }} ETB</span>
                                                                @endif
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400">No investigations configured yet.</p>
                                @endforelse
                            </div>
                        </div>
                        <div x-show="tab === 'medications'" x-cloak>
                            <label class="{{ $labelClass }}">Prescription</label>

                            @if (count($prescriptionItems))
                                <div class="mb-4 space-y-2">
                                    @foreach ($prescriptionItems as $index => $item)
                                        <div class="flex items-start justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                                            <div>
                                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $item['name'] }}</span>
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    {{ collect([$item['dosage'], $item['frequency'], $item['duration']])->filter()->implode(' · ') }}
                                                </span>
                                                @if ($item['instructions'])
                                                    <span class="block text-xs text-gray-400">{{ $item['instructions'] }}</span>
                                                @endif
                                            </div>
                                            <button type="button" wire:click="removePrescriptionItem({{ $index }})" class="shrink-0 text-gray-400 hover:text-error-500">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-gray-700">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Medication (formulary)</label>
                                        <select wire:model="newMedicationId" class="{{ $inputClass }}">
                                            <option value="">Not in formulary...</option>
                                            @foreach ($medicationCatalog as $med)
                                                <option value="{{ $med->id }}">{{ $med->name }}{{ $med->strength ? " ({$med->strength})" : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Or custom name</label>
                                        <input type="text" wire:model="newCustomName" class="{{ $inputClass }}" placeholder="Drug name" @disabled($newMedicationId)>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Dosage</label>
                                        <input type="text" wire:model="newDosage" class="{{ $inputClass }}" placeholder="e.g. 500mg">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Frequency</label>
                                        <input type="text" wire:model="newFrequency" class="{{ $inputClass }}" placeholder="e.g. Twice daily">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Duration</label>
                                        <input type="text" wire:model="newDuration" class="{{ $inputClass }}" placeholder="e.g. 5 days">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Quantity</label>
                                        <input type="number" min="1" wire:model="newQuantity" class="{{ $inputClass }}">
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-3">
                                        <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Instructions</label>
                                        <input type="text" wire:model="newInstructions" class="{{ $inputClass }}" placeholder="e.g. Take after meals">
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('newCustomName')" class="mt-2" />
                                <div class="mt-3 flex justify-end">
                                    <button type="button" wire:click="addPrescriptionItem" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                                        {{ __('Add to prescription') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div x-show="tab === 'results'" x-cloak>
                            <label for="results" class="{{ $labelClass }}">Results</label>
                            <textarea id="results" wire:model="results" rows="8" class="{{ $textareaClass }}" placeholder="Investigation results, findings..."></textarea>
                            <x-input-error :messages="$errors->get('results')" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <label class="{{ $labelClass }}">Follow-up (optional)</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <input type="date" wire:model="follow_up_date" min="{{ now()->toDateString() }}" class="{{ $inputClass }}">
                                    <x-input-error :messages="$errors->get('follow_up_date')" class="mt-2" />
                                </div>
                                <div>
                                    <input type="text" wire:model="follow_up_reason" placeholder="Reason, e.g. Recheck blood pressure" class="{{ $inputClass }}">
                                    <x-input-error :messages="$errors->get('follow_up_reason')" class="mt-2" />
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Ask the patient to come back on this date - it'll show up on reception's follow-up list once due.</p>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button type="button" wire:click="saveDraft" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                                {{ __('Save as Draft') }}
                            </button>
                            <x-ui.button type="button" wire:click="save">{{ __('Save') }}</x-ui.button>
                        </div>
                    </form>
                @else
                    <div x-show="tab === 'note'">
                        <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Patient note</p>
                        <p class="whitespace-pre-line text-sm text-gray-800 dark:text-white/90">{{ $encounter->patient_note ?: '—' }}</p>
                    </div>
                    <div x-show="tab === 'investigations'" x-cloak>
                        <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-400">Ordered investigation</p>
                        @forelse ($orderedInvestigations as $category => $items)
                            <div class="mb-4">
                                <x-ui.badge size="sm" variant="solid" :color="$categoryColors[$category] ?? 'light'">
                                    {{ $categoryLabels[$category] ?? ucfirst($category) }}
                                </x-ui.badge>
                                <ul class="mt-2 space-y-1 text-sm text-gray-800 dark:text-white/90">
                                    @foreach ($items as $item)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $item->subcategory ? "{$item->subcategory} — " : '' }}{{ $item->name }}</span>
                                            @if ($item->pivot->price !== null)
                                                <span class="text-xs text-gray-400">{{ number_format($item->pivot->price, 2) }} ETB</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-sm text-gray-800 dark:text-white/90">—</p>
                        @endforelse
                    </div>
                    <div x-show="tab === 'medications'" x-cloak>
                        <div class="mb-1.5 flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-400">Prescription</p>
                            @if ($encounter->prescription && $encounter->prescription->status !== 'pending')
                                <x-ui.badge size="sm" variant="solid" color="success">{{ ucfirst($encounter->prescription->status) }}</x-ui.badge>
                            @endif
                        </div>
                        @php $prescriptionItemsList = $encounter->prescription?->items ?? collect(); @endphp
                        @if ($prescriptionItemsList->isNotEmpty())
                            <ul class="space-y-2 text-sm text-gray-800 dark:text-white/90">
                                @foreach ($prescriptionItemsList as $item)
                                    <li>
                                        <span class="font-medium">{{ $item->name }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ collect([$item->dosage, $item->frequency, $item->duration])->filter()->implode(' · ') }}
                                        </span>
                                        @if ($item->instructions)
                                            <span class="block text-xs text-gray-400">{{ $item->instructions }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-800 dark:text-white/90">—</p>
                        @endif
                    </div>
                    <div x-show="tab === 'results'" x-cloak>
                        <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Results</p>
                        <p class="whitespace-pre-line text-sm text-gray-800 dark:text-white/90">{{ $encounter->results ?: '—' }}</p>
                    </div>

                    @if ($encounter->follow_up_date)
                        <div class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Follow-up</p>
                            <p class="text-sm text-gray-800 dark:text-white/90">
                                {{ $encounter->follow_up_date->format('Y-m-d') }}
                                @if ($encounter->follow_up_reason)
                                    &middot; {{ $encounter->follow_up_reason }}
                                @endif
                                @if ($encounter->follow_up_dismissed_at)
                                    <x-ui.badge size="sm" variant="solid" color="light">Dismissed</x-ui.badge>
                                @endif
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-6 py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Medical record</h3>
                @if ($canUpdateMedical && ! $editingMedical)
                    <button wire:click="startEditingMedical" class="text-sm text-brand-500 hover:text-brand-600">Edit</button>
                @endif
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                @if ($editingMedical)
                    <form wire:submit="saveMedical" class="space-y-5">
                        <div>
                            <label for="main_complaint" class="{{ $labelClass }}">Main complaint</label>
                            <textarea id="main_complaint" wire:model="main_complaint" rows="2" class="{{ $inputClass }}"></textarea>
                        </div>
                        <div>
                            <label for="medical_history" class="{{ $labelClass }}">Medical history</label>
                            <textarea id="medical_history" wire:model="medical_history" rows="3" class="{{ $inputClass }}"></textarea>
                        </div>
                        <div>
                            <label for="allergies" class="{{ $labelClass }}">Allergies</label>
                            <textarea id="allergies" wire:model="allergies" rows="2" class="{{ $inputClass }}"></textarea>
                        </div>
                        <div>
                            <label for="current_medications" class="{{ $labelClass }}">Current medications</label>
                            <textarea id="current_medications" wire:model="current_medications" rows="2" class="{{ $inputClass }}"></textarea>
                        </div>
                        <div>
                            <label for="previous_treatments" class="{{ $labelClass }}">Previous treatments</label>
                            <textarea id="previous_treatments" wire:model="previous_treatments" rows="3" class="{{ $inputClass }}"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" wire:click="$set('editingMedical', false)" class="text-sm text-gray-600 dark:text-gray-300">Cancel</button>
                            <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
                        </div>
                    </form>
                @else
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400">Main complaint</dt><dd class="text-gray-800 dark:text-white/90">{{ $main_complaint ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Medical history</dt><dd class="text-gray-800 dark:text-white/90">{{ $medical_history ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Allergies</dt><dd class="text-gray-800 dark:text-white/90">{{ $allergies ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Current medications</dt><dd class="text-gray-800 dark:text-white/90">{{ $current_medications ?: '—' }}</dd></div>
                        <div><dt class="text-gray-500 dark:text-gray-400">Previous treatments</dt><dd class="text-gray-800 dark:text-white/90">{{ $previous_treatments ?: '—' }}</dd></div>
                    </dl>
                @endif
            </div>
        </div>
    </div>
</div>
