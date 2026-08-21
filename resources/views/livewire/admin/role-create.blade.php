<div>
    <x-common.page-breadcrumb pageTitle="New Role" />

    <x-common.component-card title="New Role" desc="Name the role, then choose which permissions it grants.">
        <form wire:submit="save" class="max-w-2xl space-y-6">
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Role name</label>
                <input id="name" wire:model="name" type="text" placeholder="e.g. Lab Technician"
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Permissions</label>
                <div class="space-y-4">
                    @foreach ($permissionGroups as $group => $permissions)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $group }}</p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                               class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700">
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('selectedPermissions')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Create Role') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
