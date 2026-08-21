@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 disabled:cursor-not-allowed disabled:opacity-50';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Register Patient" />

    <x-common.component-card title="Patient Details">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="first_name" class="{{ $labelClass }}">First name</label>
                    <input id="first_name" wire:model="first_name" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <label for="middle_name" class="{{ $labelClass }}">Middle name</label>
                    <input id="middle_name" wire:model="middle_name" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                </div>
                <div>
                    <label for="last_name" class="{{ $labelClass }}">Last name</label>
                    <input id="last_name" wire:model="last_name" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>

                <div>
                    <label for="sex" class="{{ $labelClass }}">Gender</label>
                    <select id="sex" wire:model="sex" class="{{ $inputClass }}">
                        <option value="">Select...</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                    <x-input-error :messages="$errors->get('sex')" class="mt-2" />
                </div>
                <div>
                    <label for="age" class="{{ $labelClass }}">Age</label>
                    <input id="age" wire:model="age" type="number" min="0" max="120" placeholder="years" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('age')" class="mt-2" />
                </div>
                <div>
                    <label for="phone" class="{{ $labelClass }}">Phone</label>
                    <input id="phone" wire:model="phone" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 dark:border-gray-800">
                <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Address</h3>

                <div
                    x-data="{
                        boundaries: {},
                        region: @entangle('region'),
                        zone: @entangle('zone'),
                        woreda: @entangle('woreda'),
                        get zones() {
                            return this.region ? Object.keys(this.boundaries[this.region] ?? {}) : [];
                        },
                        get woredas() {
                            return (this.region && this.zone) ? (this.boundaries[this.region]?.[this.zone] ?? []) : [];
                        },
                        async init() {
                            const res = await fetch('/data/eth-boundaries.json');
                            this.boundaries = await res.json();
                        },
                    }"
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div>
                        <label class="{{ $labelClass }}">Region</label>
                        <select x-model="region" @change="zone = ''; woreda = ''" class="{{ $inputClass }}">
                            <option value="">Select region...</option>
                            <template x-for="r in Object.keys(boundaries)" :key="r">
                                <option :value="r" x-text="r"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Zone</label>
                        <select x-model="zone" @change="woreda = ''" :disabled="!region" class="{{ $inputClass }}">
                            <option value="">Select zone...</option>
                            <template x-for="z in zones" :key="z">
                                <option :value="z" x-text="z"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Woreda</label>
                        <select x-model="woreda" :disabled="!zone" class="{{ $inputClass }}">
                            <option value="">Select woreda...</option>
                            <template x-for="w in woredas" :key="w">
                                <option :value="w" x-text="w"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="kebele" class="{{ $labelClass }}">Kebele</label>
                        <input id="kebele" wire:model="kebele" type="text" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="house_no" class="{{ $labelClass }}">House No</label>
                        <input id="house_no" wire:model="house_no" type="text" class="{{ $inputClass }}">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-400">
                    Region/Zone/Woreda are from the national administrative boundary list; Kebele isn't available in that
                    dataset, so it's entered by hand.
                </p>
            </div>

            <div class="border-t border-gray-100 pt-6 dark:border-gray-800">
                <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Assign for today's visit</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="department_id" class="{{ $labelClass }}">Department</label>
                        <select id="department_id" wire:model.live="department_id" class="{{ $inputClass }}">
                            <option value="">Select...</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="service_id" class="{{ $labelClass }}">Service</label>
                        <select id="service_id" wire:model="service_id" class="{{ $inputClass }}" @disabled(! $department_id)>
                            <option value="">Select...</option>
                            @foreach ($this->services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="practitioner_id" class="{{ $labelClass }}">Doctor</label>
                        <select id="practitioner_id" wire:model="practitioner_id" class="{{ $inputClass }}">
                            <option value="">Select...</option>
                            @foreach ($practitioners as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('practitioner_id')" class="mt-2" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-400">
                    The patient is sent straight to the cashier for this visit's fee (and their registration fee, if any) -
                    they join the doctor's queue as soon as the cashier confirms payment.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('patients.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Register Patient') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
