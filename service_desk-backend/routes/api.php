<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketStatusController;
use App\Http\Controllers\RootcauseController;
use App\Http\Controllers\TicketPriorityController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\TicketTrackingController;
use App\Http\Controllers\TrackingPointController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\SolusiController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;


// --- Authenticated user with division ---
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('divisi', 'roles');
});

// --- Ticket Management ---
Route::middleware('permission:tickets.view')->get('/tickets', [TicketController::class, 'index']);
Route::middleware('permission:tickets.view')->get('/tickets/{id}', [TicketController::class, 'show']);
Route::middleware('permission:tickets.create')->post('/tickets', [TicketController::class, 'store']);
Route::middleware('permission:tickets.update')->put('/tickets/{id}', [TicketController::class, 'update']);
Route::middleware('permission:tickets.delete')->delete('/tickets/{id}', [TicketController::class, 'destroy']);

Route::middleware('permission:tickets.view')->get('/tickets/{id}/logs', [TicketController::class, 'getTicketLogs']);
Route::middleware('permission:tickets.view')->get('/tickets/{id}/tracking', [TicketController::class, 'getTrackingPoints']);
Route::middleware('permission:tickets.update')->post('/tickets/{ticket}/tracking/{tracking}/comment', [TicketController::class, 'submitPicComment']);
Route::middleware('permission:tickets.update')->post('/tickets/{ticket}/tracking/{tracking}/cancel', [TicketController::class, 'submitCancelComment']);

// --- User Management ---
Route::middleware('permission:users.view')->get('/users', [UserController::class, 'index']);
Route::middleware('permission:users.view')->get('/users/{id}', [UserController::class, 'show']);
Route::middleware('permission:users.create')->post('/users', [UserController::class, 'store']);
Route::middleware('permission:users.update')->put('/users/{id}', [UserController::class, 'update']);
Route::middleware('permission:users.delete')->delete('/users/{id}', [UserController::class, 'destroy']);

// --- Divisi Management ---
Route::middleware('permission:divisions.view')->get('/divisions', [DivisiController::class, 'index']);
Route::middleware('permission:divisions.view')->get('/divisions/{id}', [DivisiController::class, 'show']);
Route::middleware('permission:divisions.create')->post('/divisions', [DivisiController::class, 'store']);
Route::middleware('permission:divisions.update')->put('/divisions/{id}', [DivisiController::class, 'update']);
Route::middleware('permission:divisions.delete')->delete('/divisions/{id}', [DivisiController::class, 'destroy']);

// --- Ticket Priorities Management ---
Route::middleware('permission:priorities.view')->get('/priorities', [TicketPriorityController::class, 'index']);
Route::middleware('permission:priorities.view')->get('/priorities/{id}', [TicketPriorityController::class, 'show']);
Route::middleware('permission:priorities.create')->post('/priorities', [TicketPriorityController::class, 'store']);
Route::middleware('permission:priorities.update')->put('/priorities/{id}', [TicketPriorityController::class, 'update']);
Route::middleware('permission:priorities.delete')->delete('/priorities/{id}', [TicketPriorityController::class, 'destroy']);

// --- Layanan Management ---
Route::middleware('permission:layanans.view')->get('/layanans', [LayananController::class, 'index']);
Route::middleware('permission:layanans.view')->get('/layanans/{id}', [LayananController::class, 'show']);
Route::middleware('permission:layanans.create')->post('/layanans', [LayananController::class, 'store']);
Route::middleware('permission:layanans.update')->put('/layanans/{id}', [LayananController::class, 'update']);
Route::middleware('permission:layanans.delete')->delete('/layanans/{id}', [LayananController::class, 'destroy']);

// --- Rootcause Management ---
Route::middleware('permission:rootcauses.view')->get('/rootcauses', [RootcauseController::class, 'index']);
Route::middleware('permission:rootcauses.view')->get('/rootcauses/{id}', [RootcauseController::class, 'show']);
Route::middleware('permission:rootcauses.create')->post('/rootcauses', [RootcauseController::class, 'store']);
Route::middleware('permission:rootcauses.update')->put('/rootcauses/{id}', [RootcauseController::class, 'update']);
Route::middleware('permission:rootcauses.delete')->delete('/rootcauses/{id}', [RootcauseController::class, 'destroy']);

// --- Solusi Management ---
Route::middleware('permission:solusi.view')->get('/solusi', [SolusiController::class, 'index']);
Route::middleware('permission:solusi.view')->get('/solusi/{id}', [SolusiController::class, 'show']);
Route::middleware('permission:solusi.create')->post('/solusi', [SolusiController::class, 'store']);
Route::middleware('permission:solusi.update')->put('/solusi/{id}', [SolusiController::class, 'update']);
Route::middleware('permission:solusi.delete')->delete('/solusi/{id}', [SolusiController::class, 'destroy']);

// --- Notification Management ---
Route::middleware('permission:notifications.view')->get('/notifications', [NotificationController::class, 'index']);
Route::middleware('permission:notifications.update')->post('/notifications/{notificationId}/mark-read', [NotificationController::class, 'markAsRead']);
Route::middleware('permission:notifications.update')->post('/notifications/mark-read', [NotificationController::class, 'markAllAsRead']);
Route::middleware('permission:notifications.delete')->delete('/notifications/clear', [NotificationController::class, 'clearAll']);
Route::middleware('permission:notifications.delete')->delete('/notifications/{notificationId}', [NotificationController::class, 'delete']);
Route::middleware('permission:notifications.update')->post('/notifications/mark-read-by-ticket', [NotificationController::class, 'markAsReadByTicket']);

// --- Role Management ---
Route::middleware('permission:roles.view')->get('/roles', [RoleController::class, 'index']);
Route::middleware('permission:roles.view')->get('/roles/{role}', [RoleController::class, 'show']);
Route::middleware('permission:roles.create')->post('/roles', [RoleController::class, 'store']);
Route::middleware('permission:roles.update')->put('/roles/{role}', [RoleController::class, 'update']);
Route::middleware('permission:roles.delete')->delete('/roles/{role}', [RoleController::class, 'destroy']);

// --- Permission Management ---
Route::middleware('permission:permissions.view')->get('/permissions', [PermissionController::class, 'index']);
Route::middleware('permission:permissions.view')->get('/permissions/{permission}', [PermissionController::class, 'show']);
Route::middleware('permission:permissions.create')->post('/permissions', [PermissionController::class, 'store']);
Route::middleware('permission:permissions.update')->put('/permissions/{permission}', [PermissionController::class, 'update']);
Route::middleware('permission:permissions.delete')->delete('/permissions/{permission}', [PermissionController::class, 'destroy']);

// --- Permintaan Management ---
Route::middleware('permission:permintaan.view')->get('/permintaan', [PermintaanController::class, 'index']);
Route::middleware('permission:permintaan.view')->get('/permintaan/{id}', [PermintaanController::class, 'show']);
Route::middleware('permission:permintaan.create')->post('/permintaan', [PermintaanController::class, 'store']);
Route::middleware('permission:permintaan.update')->put('/permintaan/{id}', [PermintaanController::class, 'update']);
Route::middleware('permission:permintaan.delete')->delete('/permintaan/{id}', [PermintaanController::class, 'destroy']);

// --- Report Management ---
Route::middleware('permission:reports.view')->get('/reports', [ReportController::class, 'index']);
Route::middleware('permission:reports.view')->get('/reports/{id}', [ReportController::class, 'show']);
Route::middleware('permission:reports.create')->post('/reports', [ReportController::class, 'store']);
Route::middleware('permission:reports.update')->put('/reports/{id}', [ReportController::class, 'update']);
Route::middleware('permission:reports.delete')->delete('/reports/{id}', [ReportController::class, 'destroy']);
Route::middleware('permission:reports.view')->get('/reports/{id}/pdf', [ReportController::class, 'generatePDF']);
Route::middleware('permission:reports.view')->get('/reports/{id}/excel', [ReportController::class, 'generateExcel']);
