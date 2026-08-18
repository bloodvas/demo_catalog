<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;

Route::get('/groups', [CatalogController::class, 'groups']);
Route::get('/group/{group}', [CatalogController::class, 'groupTree']);
Route::get('/group/products/{group}', [CatalogController::class, 'groupProducts']);
Route::post('/group/products/filter', [CatalogController::class, 'products']);
