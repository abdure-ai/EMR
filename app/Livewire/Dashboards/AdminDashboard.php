<?php

namespace App\Livewire\Dashboards;

use App\Models\Patient;
use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboards.admin-dashboard', [
            'totalUsers' => User::count(),
            'totalPatients' => Patient::count(),
            'roleCounts' => Role::withCount('users')->get(),
        ])->extends('layouts.app')->title('Administration');
    }
}
