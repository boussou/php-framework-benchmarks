<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
    return $app->welcome();
});
$app->get('{path:.*}', function($path)
{
      header('Content-Type: '.('application/json').'; '.'charset=utf-8');
      echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => $_SERVER['REQUEST_METHOD']));
        
});
