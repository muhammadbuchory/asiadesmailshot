<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\WelcomeController;
use App\Http\Controllers\Extended\ReportController;
use App\Http\Middleware\WelcomesNewUsers;
use App\Livewire\AccountComponent;
use App\Livewire\EditUserComponent;
use App\Livewire\UsersComponent;
use App\Livewire\Watemplates\WatemplatesListComponent;
use App\Livewire\Watemplates\WatemplatesEditComponent;
use App\Livewire\Wasenders\WasendersListComponent;
use App\Livewire\Wasenders\WasendersEditComponent;
use App\Livewire\Wacampaigns\WacampaignsListComponent;
use App\Livewire\Wacampaigns\WacampaignsSettingsComponent;
use App\Livewire\Wacampaigns\WacampaignsContentComponent;
use App\Livewire\Wacampaigns\WacampaignsDeliveryComponent;
use App\Livewire\Wacampaigns\WacampaignsSummaryComponent;
use App\Livewire\Wacampaigns\WacampaignsOutboxComponent;
use Illuminate\Support\Facades\Route;
use Spatie\Mailcoach\Http\App\Middleware\BootstrapSettingsNavigation;
use Spatie\Mailcoach\Livewire\Templates\TemplatesComponent;
use Spatie\Mailcoach\Mailcoach;

Route::middleware('guest')->group(function () {
    Route::redirect('/', '/login');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot-password');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');



Route::middleware('web', WelcomesNewUsers::class)->group(function () {
    Route::get('welcome/{user}', [WelcomeController::class, 'showWelcomeForm'])->name('welcome');
    Route::post('welcome/{user}', [WelcomeController::class, 'savePassword']);
});



Route::middleware('auth')->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::middleware(array_merge(config('mailcoach.middleware.web'), [
        BootstrapSettingsNavigation::class,
    ]))->group(function () {
        Route::get('account', AccountComponent::class)->name('account');

        Route::prefix('users')->group(function () {
            Route::get('/', UsersComponent::class)->name('users');
            Route::get('{user}', EditUserComponent::class)->name('users.edit');
        });
        
        Route::prefix('watemplates')->group(function () {
            Route::get('/', WatemplatesListComponent::class)->name('watemplates.list');
            Route::get('{id}', WatemplatesEditComponent::class)->name('watemplates.edit');
        });

        Route::prefix('wasenders')->group(function () {
            Route::get('/', WasendersListComponent::class)->name('wasenders.list');
            Route::get('{id}', WasendersEditComponent::class)->name('wasenders.edit');
        });

        Route::prefix('wacampaigns')->group(function () {
            Route::get('/', WacampaignsListComponent::class)->name('wacampaigns.list');
            Route::get('settings/{id}', WacampaignsSettingsComponent::class)->name('wacampaigns.settings');
            Route::get('content/{id}', WacampaignsContentComponent::class)->name('wacampaigns.content');
            Route::get('delivery/{id}', WacampaignsDeliveryComponent::class)->name('wacampaigns.delivery');
            Route::get('summary/{id}', WacampaignsSummaryComponent::class)->name('wacampaigns.summary');
            Route::get('outbox/{id}', WacampaignsOutboxComponent::class)->name('wacampaigns.outbox');
        });
       
    });
    
    
    Route::get('/reportsent', [ReportController::class, 'Exportsent']);

});



