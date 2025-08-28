<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController,AuditController,TelebirrController,InventoryController,POSController};


Route::middleware('auth:sanctum')->group(function(){
Route::get('/dashboard/summary',[DashboardController::class,'summary']);
Route::get('/audit',[AuditController::class,'index']);
Route::post('/telebirr/issue',[TelebirrController::class,'issue'])->middleware('cap:telebirr:issue');
Route::post('/telebirr/repay',[TelebirrController::class,'repay'])->middleware('cap:telebirr:repay');
Route::post('/telebirr/loan',[TelebirrController::class,'loan'])->middleware('cap:telebirr:loan');
Route::post('/telebirr/topup',[TelebirrController::class,'topup'])->middleware('cap:telebirr:topup');
});