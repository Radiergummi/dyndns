<?php

use Radiergummi\DynDns\WebRoute as Route;

$routes = [
    Route::get( '/', 'home' ),

    Route::get( '/zones', 'zone' ),
    Route::get( '/zones/{zone}', 'zone@single' ),
    Route::get( '/zones/{zone}/{hostname}', 'host@index' ),
    Route::put( '/zones/{zone}/{hostname}/update', 'host@update' ),
    Route::get( '/zones/{zone}/{hostname}/update', 'host@update' )
];

return $routes;
