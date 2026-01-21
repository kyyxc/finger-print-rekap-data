<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SekretarisController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppBotController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
Route::get('/wa-test', [WhatsAppBotController::class, 'testPage'])->name('wa-test');

// WhatsApp Bot Routes
Route::post('/whatsapp-bot/send-test', [WhatsAppBotController::class, 'sendTestMessage']);

Route::get('login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'postLogin'])->name('authenticate');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware(['auth.admin']);

Route::prefix('admin')->group(function () {
    Route::middleware(['auth.admin'])->group(function () {
        Route::post('/', action: [AdminController::class, 'createAdmin'])->name('admin.create');

        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('profile', [AdminController::class, 'profile'])->name('admin.profile');
        Route::patch('profile', [AdminController::class, 'patchProfile'])->name('admin.change-password');
        Route::delete('profile', [AdminController::class, 'deleteProfile'])->name('admin.delete');


        Route::post('sync-users', [AdminController::class, 'syncUsers'])->name('admin.users.sync');
        Route::get('users', [AdminController::class, 'users'])->name('admin.users');
        Route::patch('users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('import-users', [AdminController::class, 'importUsers'])->name('admin.users.import');
        Route::post('import-photos', [AdminController::class, 'importPhotos'])->name('admin.users.import.photos');
        Route::get('export-attendances', [AdminController::class, 'exportAttendances'])->name('admin.attendances.export');

        // Kelola Admin, Sekretaris, Kelas
        Route::get('admins', [AdminController::class, 'admins'])->name('admin.admins');
        Route::delete('admins/{id}', [AdminController::class, 'deleteAdmin'])->name('admin.admins.destroy');

        Route::get('sekretaris', [AdminController::class, 'sekretaris'])->name('admin.sekretaris');
        Route::post('sekretaris', [AdminController::class, 'createSekretaris'])->name('admin.sekretaris.store');
        Route::patch('sekretaris/{id}', [AdminController::class, 'updateSekretaris'])->name('admin.sekretaris.update');
        Route::delete('sekretaris/{id}', [AdminController::class, 'deleteSekretaris'])->name('admin.sekretaris.destroy');

        Route::get('grades', [AdminController::class, 'grades'])->name('admin.grades');
        Route::post('grades', [AdminController::class, 'createGrade'])->name('admin.grades.store');
        Route::patch('grades/{id}', [AdminController::class, 'updateGrade'])->name('admin.grades.update');
        Route::delete('grades/{id}', [AdminController::class, 'deleteGrade'])->name('admin.grades.destroy');
    });
});

Route::prefix('sekretaris')->group(function () {
    Route::middleware(['auth.admin'])->group(function () {
        Route::get('dashboard', [SekretarisController::class, 'dashboard'])->name('sekretaris.dashboard');
        Route::get('users', [SekretarisController::class, 'users'])->name('sekretaris.users');
        Route::get('absensi', [SekretarisController::class, 'absensi'])->name('sekretaris.absensi');
        Route::get('kelola-absen', [SekretarisController::class, 'kelolaAbsen'])->name('sekretaris.kelola-absen');
        Route::post('kelola-absen', [SekretarisController::class, 'updateAbsen'])->name('sekretaris.kelola-absen.update');
        Route::delete('kelola-absen/{id}', [SekretarisController::class, 'deleteAbsen'])->name('sekretaris.kelola-absen.delete');
    });
});
