<?php

use Radiergummi\DynDns\ConsoleRoute as Route;

$routes = [
    Route::add( 'auth:decrypt', 'decrypt' ),
    Route::add( 'auth:encrypt', 'encrypt' ),
    Route::add( 'update', 'update' )
];

return $routes;
