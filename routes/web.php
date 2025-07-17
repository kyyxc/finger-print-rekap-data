<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
Route::prefix('admins')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('login', [AdminController::class, 'login'])->name('admins.login');
        Route::post('login', [AdminController::class, 'authenticate'])->name('admins.authenticate');
        Route::post('logout', [AdminController::class, 'logout'])->name('admins.logout')->middleware(['auth.admin']);
    });
    Route::middleware(['auth.admin'])->group(function () {
        Route::post('/', [AdminController::class, 'createAdmin'])->name('admins.create');
        Route::delete('/', [AdminController::class, 'deleteAccount'])->name('admins.delete');
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admins.dashboard');
        Route::post('sync-users', [AdminController::class, 'syncUsers'])->name('admins.users.sync');
        Route::get('users', [AdminController::class, 'users'])->name('admins.users');
        Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admins.users.destroy');
        Route::delete('all-users', [AdminController::class, 'deleteAllUsers'])->name('admins.users.destroy.all');
        Route::post('import-users', [AdminController::class, 'importUsers'])->name('admins.users.import');
        Route::post('import-photos', [AdminController::class, 'importPhotos'])->name('admins.users.import.photos');
        Route::get('export-attendances', [AdminController::class, 'exportAttendances'])->name('admins.attendances.export');
        Route::post('change-password', [AdminController::class, 'changePassword'])->name('admins.change-password');
    });
});
