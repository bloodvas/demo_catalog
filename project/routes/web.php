<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CatalogController;

Route::get('/', function () {
    return Inertia::render('Catalog');
});

Route::get('/product/{id}', function ($id) {
    return Inertia::render('ProductDetail', ['id' => (int)$id]);
})->where('id', '[0-9]+');

Route::get('/group/{id}', function ($id) {
    return Inertia::render('Catalog');
})->where('id', '[0-9]+');


Route::get('/product/{id}', [CatalogController::class, 'show']);
