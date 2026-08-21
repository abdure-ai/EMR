<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Route each role to its own dashboard. First matching role wins; a user
     * with no role yet (freshly created, not assigned) falls back to their
     * profile page rather than a 404/403.
     */
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('Super Admin') => redirect()->route('dashboard.admin'),
            $user->hasRole('Clinic Manager') => redirect()->route('dashboard.management'),
            $user->hasRole('Reception') => redirect()->route('dashboard.reception'),
            $user->hasRole('Practitioner') => redirect()->route('dashboard.practitioner'),
            $user->hasRole('Pharmacist') => redirect()->route('dashboard.pharmacy'),
            $user->hasRole('Cashier') => redirect()->route('dashboard.cashier'),
            default => redirect()->route('profile'),
        };
    }
}
