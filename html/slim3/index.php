<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$app->get('/api/fake/:name', function ($name) {
    header('Content-Type: '.('application/json').'; '.'charset=utf-8');
     echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => $_SERVER['REQUEST_METHOD']));

});
$app->run();
