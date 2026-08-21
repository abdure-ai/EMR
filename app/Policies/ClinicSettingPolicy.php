<?php

namespace App\Policies;

use App\Models\ClinicSetting;
use App\Models\User;

class ClinicSettingPolicy
{
    public function view(User $user, ClinicSetting $clinicSetting): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, ClinicSetting $clinicSetting): bool
    {
        return $user->can('settings.manage');
    }
}
