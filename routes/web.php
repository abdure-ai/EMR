<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Admin\PermissionCreate;
use App\Livewire\Admin\PermissionIndex;
use App\Livewire\Admin\RoleCreate;
use App\Livewire\Admin\RoleEdit;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\UserCreate;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Appointments\AppointmentCreate;
use App\Livewire\Appointments\AppointmentEdit;
use App\Livewire\Appointments\AppointmentIndex;
use App\Livewire\AuditLog\AuditLogIndex;
use App\Livewire\Billing\InvoiceIndex;
use App\Livewire\Billing\InvoiceShow;
use App\Livewire\Dashboards\AdminDashboard;
use App\Livewire\Dashboards\CashierDashboard;
use App\Livewire\Dashboards\ManagementDashboard;
use App\Livewire\Dashboards\PharmacyDashboard;
use App\Livewire\Dashboards\PractitionerDashboard;
use App\Livewire\Dashboards\ReceptionDashboard;
use App\Livewire\Departments\DepartmentCreate;
use App\Livewire\Departments\DepartmentIndex;
use App\Livewire\Encounters\EncounterShow;
use App\Livewire\FollowUps\FollowUpIndex;
use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\MedicationInventory;
use App\Livewire\Inventory\StockMovementIndex;
use App\Livewire\Investigations\InvestigationCreate;
use App\Livewire\Investigations\InvestigationEdit;
use App\Livewire\Investigations\InvestigationIndex;
use App\Livewire\Medications\MedicationCreate;
use App\Livewire\Medications\MedicationEdit;
use App\Livewire\Medications\MedicationIndex;
use App\Livewire\Patients\PatientCheckIn;
use App\Livewire\Patients\PatientCreate;
use App\Livewire\Patients\PatientEdit;
use App\Livewire\Patients\PatientIndex;
use App\Livewire\Patients\PatientShow;
use App\Livewire\Prescriptions\PrescriptionIndex;
use App\Livewire\Prescriptions\PrescriptionShow;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Services\ServiceCreate;
use App\Livewire\Services\ServiceEdit;
use App\Livewire\Services\ServiceIndex;
use App\Livewire\Settings\ClinicSettingsEdit;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('dashboard/admin', AdminDashboard::class)
        ->middleware('role:Super Admin')->name('dashboard.admin');
    Route::get('dashboard/management', ManagementDashboard::class)
        ->middleware('role:Clinic Manager|Super Admin')->name('dashboard.management');
    Route::get('dashboard/reception', ReceptionDashboard::class)
        ->middleware('role:Reception|Super Admin')->name('dashboard.reception');
    Route::get('dashboard/practitioner', PractitionerDashboard::class)
        ->middleware('role:Practitioner|Super Admin')->name('dashboard.practitioner');
    Route::get('dashboard/pharmacy', PharmacyDashboard::class)
        ->middleware('role:Pharmacist|Super Admin')->name('dashboard.pharmacy');
    Route::get('dashboard/cashier', CashierDashboard::class)
        ->middleware('role:Cashier|Super Admin')->name('dashboard.cashier');

    Route::get('admin/users', UserIndex::class)->name('admin.users.index');
    Route::get('admin/users/create', UserCreate::class)->name('admin.users.create');

    Route::get('admin/roles', RoleIndex::class)->name('admin.roles.index');
    Route::get('admin/roles/create', RoleCreate::class)->name('admin.roles.create');
    Route::get('admin/roles/{role}/edit', RoleEdit::class)->name('admin.roles.edit');

    Route::get('admin/permissions', PermissionIndex::class)->name('admin.permissions.index');
    Route::get('admin/permissions/create', PermissionCreate::class)->name('admin.permissions.create');

    Route::get('patients', PatientIndex::class)->name('patients.index');
    Route::get('patients/create', PatientCreate::class)->name('patients.create');
    Route::get('patients/{patient}/edit', PatientEdit::class)->name('patients.edit');
    Route::get('patients/{patient}/check-in', PatientCheckIn::class)->name('patients.check-in');
    Route::get('patients/{patient}', PatientShow::class)->name('patients.show');
    Route::get('patients/{patient}/encounters/{encounter}', EncounterShow::class)->name('encounters.show');

    Route::get('appointments', AppointmentIndex::class)->name('appointments.index');
    Route::get('appointments/create', AppointmentCreate::class)->name('appointments.create');
    Route::get('appointments/{appointment}/edit', AppointmentEdit::class)->name('appointments.edit');

    Route::get('follow-ups', FollowUpIndex::class)->name('follow-ups.index');

    Route::get('departments', DepartmentIndex::class)->name('departments.index');
    Route::get('departments/create', DepartmentCreate::class)->name('departments.create');

    Route::get('services', ServiceIndex::class)->name('services.index');
    Route::get('services/create', ServiceCreate::class)->name('services.create');
    Route::get('services/{service}/edit', ServiceEdit::class)->name('services.edit');

    Route::get('investigations', InvestigationIndex::class)->name('investigations.index');
    Route::get('investigations/create', InvestigationCreate::class)->name('investigations.create');
    Route::get('investigations/{investigation}/edit', InvestigationEdit::class)->name('investigations.edit');

    Route::get('medications', MedicationIndex::class)->name('medications.index');
    Route::get('medications/create', MedicationCreate::class)->name('medications.create');
    Route::get('medications/{medication}/edit', MedicationEdit::class)->name('medications.edit');

    Route::get('prescriptions', PrescriptionIndex::class)->name('prescriptions.index');
    Route::get('prescriptions/{prescription}', PrescriptionShow::class)->name('prescriptions.show');

    Route::get('inventory', InventoryIndex::class)->name('inventory.index');
    Route::get('inventory/movements', StockMovementIndex::class)->name('inventory.movements');
    Route::get('inventory/{medication}', MedicationInventory::class)->name('inventory.medication');

    Route::get('billing', InvoiceIndex::class)->name('billing.index');
    Route::get('billing/{invoice}', InvoiceShow::class)->name('billing.show');

    Route::get('reports', ReportIndex::class)->name('reports.index');

    Route::get('audit-log', AuditLogIndex::class)->name('audit-log.index');

    Route::get('settings', ClinicSettingsEdit::class)->name('settings.index');

    Route::view('profile', 'profile')->name('profile');

    Route::post('logout', LogoutController::class)->name('logout');

    // TailAdmin's own sample/reference pages, kept for design reference only.
    // Restricted to Super Admin - see MenuHelper::getOthersItems() for why.
    Route::prefix('template')->name('template.')->middleware('role:Super Admin')->group(function () {
        Route::get('dashboard', fn () => view('pages.dashboard.ecommerce', ['title' => 'Ecommerce Dashboard']))->name('dashboard');
        Route::get('calendar', fn () => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
        Route::get('form-elements', fn () => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
        Route::get('basic-tables', fn () => view('pages.tables.basic-tables', ['title' => 'Basic Tables']))->name('basic-tables');
        Route::get('blank', fn () => view('pages.blank', ['title' => 'Blank']))->name('blank');
        Route::get('error-404', fn () => view('pages.errors.error-404', ['title' => 'Error 404']))->name('error-404');
        Route::get('line-chart', fn () => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
        Route::get('bar-chart', fn () => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');
        Route::get('alerts', fn () => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
        Route::get('avatars', fn () => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
        Route::get('badge', fn () => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badges');
        Route::get('buttons', fn () => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
        Route::get('image', fn () => view('pages.ui-elements.images', ['title' => 'Images']))->name('images');
        Route::get('videos', fn () => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');
    });
});

require __DIR__.'/auth.php';
