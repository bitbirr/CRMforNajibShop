<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::middleware(['web','auth'])->group(function(){
Route::get('/', fn()=>Inertia::render('Dashboard/Index'));
Route::get('/pos', fn()=>Inertia::render('POS/Index'))->middleware('cap:sales:checkout');
Route::get('/inventory', fn()=>Inertia::render('Inventory/Index'))->middleware('cap:inventory:view_stock');
Route::get('/telebirr', fn()=>Inertia::render('Telebirr/Index'))->middleware('cap:telebirr:issue');
// ...
});