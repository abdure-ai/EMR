<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TelegramWebhookController;
use App\Livewire\Admin\PermissionCreate;
use App\Livewire\Admin\PermissionIndex;
use App\Livewire\Admin\RoleCreate;
use App\Livewire\Admin\RoleEdit;
use App\Livewire\Admin\RoleIndex;
use App\Livewire\Admin\UserCreate;
use App\Livewire\Admin\UserEdit;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Appointments\AppointmentCreate;
use App\Livewire\Appointments\AppointmentEdit;
use App\Livewire\Appointments\AppointmentIndex;
use App\Livewire\Admin\ContactMessageIndex;
use App\Livewire\Admin\NewsCreate;
use App\Livewire\Admin\NewsEdit;
use App\Livewire\Admin\NewsIndex as AdminNewsIndex;
use App\Livewire\Admin\SiteContentEdit;
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
use App\Livewire\Site\About as SiteAbout;
use App\Livewire\Site\Contact as SiteContact;
use App\Livewire\Site\Home as SiteHome;
use App\Livewire\Site\NewsIndex as SiteNewsIndex;
use App\Livewire\Site\NewsShow as SiteNewsShow;
use App\Livewire\Site\Services as SiteServices;
use Illuminate\Support\Facades\Route;

// Language switch - available whether logged in or not, redirects back to
// wherever the request came from.
Route::get('language/{locale}', LanguageController::class)->name('language.switch');

// Telegram webhook - called by Telegram's servers, not a browser. Public
// and CSRF-exempt (see bootstrap/app.php); authenticated instead via the
// X-Telegram-Bot-Api-Secret-Token header, checked inside the controller.
Route::post('telegram/webhook', TelegramWebhookController::class)->name('telegram.webhook');

// Public marketing site - no auth required.
Route::get('/', SiteHome::class)->name('site.home');
Route::get('/about', SiteAbout::class)->name('site.about');
Route::get('/services', SiteServices::class)->name('site.services');
Route::get('/news', SiteNewsIndex::class)->name('site.news.index');
Route::get('/news/{post:slug}', SiteNewsShow::class)->name('site.news.show');
Route::get('/contact', SiteContact::class)->name('site.contact');

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
    Route::get('admin/users/{user}/edit', UserEdit::class)->name('admin.users.edit');

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

    // Prefixed admin/ - the public marketing site owns the bare /services path.
    Route::get('admin/services', ServiceIndex::class)->name('services.index');
    Route::get('admin/services/create', ServiceCreate::class)->name('services.create');
    Route::get('admin/services/{service}/edit', ServiceEdit::class)->name('services.edit');

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

    Route::get('admin/website/content', SiteContentEdit::class)->name('admin.site-content.edit');

    Route::get('admin/website/news', AdminNewsIndex::class)->name('admin.news.index');
    Route::get('admin/website/news/create', NewsCreate::class)->name('admin.news.create');
    Route::get('admin/website/news/{post}/edit', NewsEdit::class)->name('admin.news.edit');

    Route::get('admin/website/messages', ContactMessageIndex::class)->name('admin.contact-messages.index');

    Route::get('settings', ClinicSettingsEdit::class)->name('settings.index');

    Route::view('profile', 'profile')->name('profile');

    Route::post('logout', LogoutController::class)->name('logout');
});

require __DIR__.'/auth.php';
