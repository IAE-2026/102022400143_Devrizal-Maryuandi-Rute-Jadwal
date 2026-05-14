<?php

use Illuminate\Support\Facades\Route;

Route::get('/graphiql', function () {
    return view('graphiql');
});
