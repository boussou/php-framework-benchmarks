<?php
      header('Content-Type: '.('application/json').'; '.'charset=utf-8');
      echo json_encode(array('id'=>rand(),'name' => 'mickey', 'state' => ['REQUEST_METHOD']));
