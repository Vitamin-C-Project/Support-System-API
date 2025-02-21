<?php

use App\Events\TestEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // TestEvent::dispatch("Welcome");
    return view('welcome');
});
