<?php

require 'vendor/autoload.php';
use League\Csv\Reader;
use GuzzleHttp\Client as GuzzleClient;

$host = 'http://localhost:8000';
$credentials = array('admin', 'islandora');
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';

$csv = Reader::createFromPath($csv_file);
$records = $csv->fetchAssoc();

$client = new GuzzleClient($client_defaults);

foreach ($records as $record) {
    $node = array(
        array('type' => array('target_id' => 'islandora_image', 'target_type' => 'node_type')),
        array('title' => array('value' => $record['Title'])),
        array('field_description' => array('value' => $record['Description'])),
    );
    $json = json_encode($node);

    // Create the node.
    $endpoint = $host . '/node';
    $node_response = $client->request('POST', $endpoint, ['auth' => $credentials, 'json' => $json]);
    // Add the JPEG file.
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $response->getHeader('Location');
        // curl -v -u admin:islandora -H "Content-Type: image/jpeg" -H "Content-Disposition: attachment; filename=\"test.jpeg\"" --data-binary @test.jpeg http://localhost:8000/node/4/media/field_web_content/add/web_content
        $file_path = $input_dir . '/' . $record['File'];
        if (file_exists($file_path)) {
            $pathinfo = pathinfo($path);
            $headers = aray('Content-Type' => 'image/jpeg', 'Content-Disposition' => 'attachment; filename=' . $pathinfo['basename']);
            $image_file_contents = file_get_contents($file_path);
            $endpoint = $node_uri . '/media/field_web_content/add/web_content'
            $file_response = $client->request('POST', $endpoint, ['auth' => $credentials, 'headers' => $headers, 'body' => $image_file_contents]);
        }
        else {
            print "File " . $record['File'] . " does not exist\n";
        }
    }
    else {
        print "Node " . $record['Title'] . " not created (HTTP response code " . $response->getStatusCode() . "\n";
    }
}


