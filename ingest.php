<?php

// You may need to adjust these variables.
$host = 'http://localhost:8000';
$credentials = array('admin', 'islandora');
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';

// You do not need to adjust anything below this line.

require 'vendor/autoload.php';
use League\Csv\Reader;
use GuzzleHttp\Client as GuzzleClient;

$csv = Reader::createFromPath($csv_file);
$records = $csv->fetchAssoc();

$client = new GuzzleClient(array('http_errors' => false));

foreach ($records as $record) {
    $node = array(
        'type' => array(array('target_id' => 'islandora_image', 'target_type' => 'node_type')),
        'title' => array(array('value' => $record['Title'])),
        'field_description' => array(array('value' => $record['Description'])),
    );
    $json = json_encode($node);

    // Create the node.
    $endpoint = $host . '/node';

    // This request results in a 403.
    $node_response = $client->request('POST', $endpoint, ['auth' => $credentials, 'headers' => array('Content-Type' => 'application/json'), 'body' => $json]);

    // Add the JPEG file.
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $node_response->getHeader('Location');
        $file_path = $input_dir . '/' . $record['File'];
        if (file_exists($file_path)) {
            $pathinfo = pathinfo($path);
            $headers = array('Content-Type' => 'image/jpeg', 'Content-Disposition' => 'attachment; filename=' . $pathinfo['basename']);
            $image_file_contents = file_get_contents($file_path);
            $endpoint = $node_uri . '/media/field_web_content/add/web_content';
            $file_response = $client->request('POST', $endpoint, ['auth' => $credentials, 'headers' => $headers, 'body' => $image_file_contents]);
        }
        else {
            print "File " . $record['File'] . " does not exist; node " . $node_uri . " created, but no file attached.\n";
        }
    }
    else {
        print 'Node "' . $record['Title'] . '" not created (HTTP response code ' . $node_response->getStatusCode() . ")\n";
    }
}


