<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Extended\SendingWaController;

Route::get('sending/wajobs', [SendingWaController::class, 'WaJobs']);
Route::get('schedule/wajobs', [SendingWaController::class, 'WaSchecuduleJobs']);
