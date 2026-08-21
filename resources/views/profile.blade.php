@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profile" />

    <div class="space-y-6">
        <x-common.component-card title="Profile Information">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </x-common.component-card>

        <x-common.component-card title="Update Password">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </x-common.component-card>

        <x-common.component-card title="Delete Account">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </x-common.component-card>
    </div>
@endsection
