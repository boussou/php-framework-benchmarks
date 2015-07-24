<?php
namespace Controller;
class Foo {
        function bar(\Base $f3, $params) {
    header('Content-Type: '.('application/json').'; '.'charset=utf-8');
      echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => $_SERVER['REQUEST_METHOD']));

        }
}

