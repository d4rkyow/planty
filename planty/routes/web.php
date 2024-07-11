<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubsCategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleriesController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\AccordionController;

Route::get('/', function () {
    return view('about_us');
});

Route::get('/fun', [BenefitController::class, 'benefit']);
Route::get('/subs', [AccordionController::class, 'accordion']);

Route::get('/subs/details', function () {
    return view('productdetail');
});

Route::get('/about-us', function () {
    return view('about_us');
});

Route::get('/plant-care', function () {
    return view('plant-care');
});

Route::get('/gallery', [GalleriesController::class, 'readGalleries']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/tutorial', function () {
    return view('tutorial');
});

Route::get('/diseases', function () {
    return view('disease');
});
Route::get('/donts', function () {
    return view('donts');
});

Route::get('/productBeginner', [SubsCategoriesController::class, 'productBeginner'])->name('productBeginner');

Route::get('/productEnthusiast', [SubsCategoriesController::class, 'productEnthusiast'])->name('productEnthusiast');

Route::get('/paymentDetail', function () {
    return view('payment_detail');
})->name('paymentDetail');
