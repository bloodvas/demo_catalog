<?php

use Illuminate\Support\Facades\Route;

Route::get('/groups', [CatalogController::class, 'groups']);
Route::get('/groups/{id}', [CatalogController::class, 'group']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{id}', [CatalogController::class, 'productByGroup']);


