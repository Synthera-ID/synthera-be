<?php

use App\Models\Category;

Route::get('/test-category', function () {
    return Category::all();
});