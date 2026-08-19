<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogController;

// order matters: POST /products должен быть ДО GET /products/{id}
Route::post('/products', [CatalogController::class, 'products']);
Route::get('/groups', [CatalogController::class, 'groups']);
Route::get('/groups/{id}/breadcrumbs', [CatalogController::class, 'groupBreadcrumbs']);
Route::get('/products/{id}', [CatalogController::class, 'show']);
Route::get('/products/{id}/breadcrumbs', [CatalogController::class, 'productBreadcrumbs']);
Route::get('/group/{group}/products', [CatalogController::class, 'groupProducts']);
