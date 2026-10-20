<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicTrackingController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RepairInterventionController;
use App\Http\Controllers\RepairTicketController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\WorkshopSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Tracking Routes (Zero authentication needed)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/suivi/{token}', [PublicTrackingController::class, 'show'])->name('public.tracking.show');
    Route::get('/suivi/{token}/document/{attachment}', [PublicTrackingController::class, 'attachment'])->name('public.tracking.attachment');
});

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Multi-Tenant Workspace Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers (Clients)
    Route::resource('customers', CustomerController::class);

    // Devices (Appareils)
    Route::resource('devices', DeviceController::class);

    // Repair Tickets (Tickets de réparation)
    Route::resource('tickets', RepairTicketController::class);
    Route::post('/tickets/{ticket}/transition', [RepairTicketController::class, 'transition'])->name('tickets.transition');
    Route::get('/tickets/{ticket}/receipt', [RepairTicketController::class, 'receipt'])->name('tickets.receipt');
    Route::post('/tickets/{ticket}/rotate-token', [RepairTicketController::class, 'rotateTrackingToken'])->name('tickets.rotate-token');
    Route::post('/tickets/{ticket}/interventions', [RepairInterventionController::class, 'store'])->name('tickets.interventions.store');

    // Quotes (Devis)
    Route::resource('quotes', QuoteController::class);
    Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('/quotes/{quote}/refuse', [QuoteController::class, 'refuse'])->name('quotes.refuse');
    Route::get('/quotes/{quote}/print', [QuoteController::class, 'print'])->name('quotes.print');

    // Invoices (Factures) & Payments (Règlements)
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

    // Attachments & Photos
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Team Collaborators (Propriétaire uniquement)
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::put('/team/{membership}', [TeamController::class, 'update'])->name('team.update');
    Route::post('/team/{membership}/toggle-status', [TeamController::class, 'toggleStatus'])->name('team.toggle-status');

    // Workshop Settings (Propriétaire uniquement)
    Route::get('/settings', [WorkshopSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [WorkshopSettingsController::class, 'update'])->name('settings.update');

    // Reports & Audit (Admin & Propriétaire)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
