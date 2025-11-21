<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/register', 'AuthController@register');
$router->post('/login', 'AuthController@login');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/monitors', 'MonitorController@index');
    $router->post('/monitors', 'MonitorController@store');
    $router->get('/monitors/{id}', 'MonitorController@show');
    $router->put('/monitors/{id}', 'MonitorController@update');
    $router->delete('/monitors/{id}', 'MonitorController@destroy');
    $router->get('/monitors/{id}/checks', 'MonitorController@checks');
    $router->get('/monitors/{id}/statistics', 'MonitorController@statistics');
});
