<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SekretarisController;
use App\Http\Controllers\UserController;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'postLogin'])->name('authenticate');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware(['auth.admin']);

Route::prefix('admin')->group(function () {
    Route::middleware(['auth.admin'])->group(function () {
        Route::post('/', [AdminController::class, 'createAdmin'])->name('admin.create');
        Route::delete('/', [AdminController::class, 'deleteAccount'])->name('admin.delete');

        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::post('sync-users', [AdminController::class, 'syncUsers'])->name('admin.users.sync');
        Route::get('users', [AdminController::class, 'users'])->name('admin.users');
        Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('import-users', [AdminController::class, 'importUsers'])->name('admin.users.import');
        Route::post('import-photos', [AdminController::class, 'importPhotos'])->name('admin.users.import.photos');
        Route::get('export-attendances', [AdminController::class, 'exportAttendances'])->name('admin.attendances.export');
        Route::post('change-password', [AdminController::class, 'changePassword'])->name('admin.change-password');

        // Kelola Admin, Sekretaris, Kelas
        Route::get('admins', [AdminController::class, 'admins'])->name('admin.admins');
        Route::get('sekretaris', [AdminController::class, 'sekretaris'])->name('admin.sekretaris');
        Route::get('grades', [AdminController::class, 'grades'])->name('admin.grades');
    });
});

Route::prefix('sekretaris')->group(function () {
    Route::middleware(['auth.admin'])->group(function () {
        Route::get('dashboard', [SekretarisController::class, 'dashboard'])->name('sekretaris.dashboard');

        // Route::post('sync-users', [AdminController::class, 'syncUsers'])->name('admins.users.sync');
        // Route::get('users', [AdminController::class, 'users'])->name('admins.users');
        // Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admins.users.destroy');
        // Route::get('export-attendances', [AdminController::class, 'exportAttendances'])->name('admins.attendances.export');
        // Route::post('change-password', [AdminController::class, 'changePassword'])->name('admins.change-password');
    });
});
