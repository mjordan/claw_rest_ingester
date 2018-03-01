<?php

require 'vendor/autoload.php';

$uri = 'http://localhost:8000/node';
$json = '{"type":[{"target_id":"islandora_image","target_type":"node_type"}],"title":[{"value":"Test title"}],"field_description":[{"value":"Test description."}]}';

$response = \Httpful\Request::post($uri)
    ->sendsJson()
    ->authenticateWith('admin', 'islandora')
    ->body($json)
    ->send(); 

var_dump($response->code);
