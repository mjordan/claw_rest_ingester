<?php

// You may need to adjust these variables.

$host = 'http://localhost:8000';
$username = 'admin';
$password = 'islandora';
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';

// You do not need to adjust anything below this line.

require 'vendor/autoload.php';
use League\Csv\Reader;
use GuzzleHttp\Client as GuzzleClient;

$mimes_builder = \Mimey\MimeMappingBuilder::create();
$mimes_builder->add('image/jp2', 'jp2');
$mimes = new \Mimey\MimeTypes($mimes_builder->getMapping());

$csv = Reader::createFromPath($csv_file);
$records = $csv->fetchAssoc();

$client = new GuzzleClient(array('http_errors' => false));
$authen_string = base64_encode($username . ':' . $password);

foreach ($records as $record) {
    // Create the node.
    $node = array(
        'type' => array(array('target_id' => 'islandora_image', 'target_type' => 'node_type')),
        'title' => array(array('value' => $record['Title'])),
        'field_description' => array(array('value' => $record['Description'])),
    );
    $json = json_encode($node);

    $endpoint = $host . '/node';
    $node_response = $client->request('POST', $endpoint,
        ['headers' => array('Authorization' => 'Basic ' . $authen_string, 'Content-Type' => 'application/json'), 'body' => $json]);
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $node_response->getHeader('Location');
        print 'Node "' . $record['Title'] . '" (' . $node_uri[0] . ") created.\n";
    }
    else {
        print 'Node "' . $record['Title'] . '" not created (HTTP response code ' . $node_response->getStatusCode() . ")\n";
        continue;
    }

    // Add the binary resource.
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $node_response->getHeader('Location');
        $file_path = $input_dir . '/' . $record['File'];
        if (file_exists($file_path)) {
            $pathinfo = pathinfo($file_path);
            $mimetype = $mimes->getMimeType($pathinfo['extension']);
            switch ($mimetype) {
                case "image/jpeg":
                    $endpoint_path = '/media/field_web_content/add/web_content';
                    break;
                case "image/tiff":
                    $endpoint_path = '/media/field_tiff/add/image_tiff';
                    break;
                case "image/jp2":
                    $endpoint_path = '/media/field_jp2/add/jp2';
                    break;
                default:
                    print "Image type $mimetype not recognized, not ingesting binary resource\n";
                    continue; 
            }
            $headers = array('Authorization' => 'Basic ' . $authen_string, 'Content-Type' => $mimetype,
                'Content-Disposition' => 'attachment; filename="' . $pathinfo['basename'] . '"');
            $image_file_contents = fopen($file_path, 'r');
            $endpoint = $node_uri[0] . $endpoint_path;
            $binary_response = $client->request('POST', $endpoint, ['headers' => $headers, 'body' => $image_file_contents]);
            if ($binary_response->getStatusCode() == 201) {
                print " Binary resource (" . $mimetype . ") from file " . $file_path . " added to " . $node_uri[0] . ".\n";
            }
            else {
                print " Binary resource (" . $mimetype . ") from file " . $file_path . " not added to " . $node_uri[0] .
                    " (HTTP response code " . $binary_response->getStatusCode() .")\n";
            }
        }
        else {
            print " File " . $record['File'] . " does not exist, so no binary resources added to " . $node_uri[0] . ".\n";
            continue;
        }
    }
}
