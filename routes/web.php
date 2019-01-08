<?php

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

$router->get('/key', function() {
    return str_random(32);
});

//Restful Api
$router->group(['middleware' => 'checkSecretKey'], function() use ($router)
{
    //TEST
    $router->get('/index', 'Api\TestController@index');
    $router->get('/get', 'Api\TestController@get');
    $router->post('/post', 'Api\TestController@post');

    //LogIn LogOut
    $router->get('/logService/getLogInLogOut', 'Api\LogInLogOutController@getLogInLogOut');
    $router->post('/logService/postLogInLogOut', 'Api\LogInLogOutController@postLogInLogOut');
});