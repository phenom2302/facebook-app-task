<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$app->get('/', ['as'=>'homepage','uses'=>'HomeController@index']);
$app->get('/login', ['as'=>'login','uses'=>'AuthController@login']);
$app->get('/login-callback', ['as'=>'login-callback','uses'=>'AuthController@loginCallback']);
$app->get('/profile', ['as'=>'profile','uses'=>'AuthController@profile']);
$app->get('/logout', ['as'=>'logout','uses'=>'AuthController@logout']);
$app->get('/deauthorize-callback', ['as'=>'deauthorize-callback','uses'=>'AuthController@deauthorizeCallback']);