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
if (count($records) == 0) {
    print "There are no records in the CSV filea at $csv_file.\n";
}

$client = new GuzzleClient(array('http_errors' => false));
$authen_string = base64_encode($username . ':' . $password);

$mimetypes = array(
    'image/jpeg' => array('endpoint' => '/media/field_web_content/add/web_content', 'id' => 'web_content'),
    'image/tiff' => array('endpoint' => '/media/field_tiff/add/image_tiff', 'id' => 'image_tiff'),
    'image/jp2' => array('endpoint' => '/media/field_jp2/add/jp2', 'id' => 'jp2'),
);

foreach ($records as $record) {
    // Create the node.
    $node = array(
        'type' => array(array('target_id' => 'islandora_image', 'target_type' => 'node_type')),
        'title' => array(array('value' => $record['title'])),
        'field_description' => array(array('value' => $record['field_description'])),
    );
    // Add any custom fields.
    foreach ($record as $custom_field_header => $custom_field_value) {
      $not_custom = array('file', 'title', 'field_descripton');
      if (!in_array($custom_field_header, $not_custom) && !preg_match('/^media:/', $custom_field_header)) {
          $node[$custom_field_header] = array(array('value' => $record[$custom_field_header]));
      }
    }
    $json = json_encode($node);

    $endpoint = $host . '/node?_format=json';
    $node_response = $client->request('POST', $endpoint, ['auth' => [$username, $password], 'headers' => array('Content-Type' => 'application/json'), 'body' => $json]);
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $node_response->getHeader('Location');
        print 'Node "' . $record['title'] . '" (' . $node_uri[0] . ") created.\n";
    }
    else {
        print 'Node "' . $record['title'] . '" not created (HTTP response code ' . $node_response->getStatusCode() . ")\n";
        continue;
    }

    // Add the binary resource.
    if ($node_response->getStatusCode() == 201) {
        $node_uri = $node_response->getHeader('Location');
        $file_path = $input_dir . '/' . $record['file'];
        if (file_exists($file_path)) {
            $pathinfo = pathinfo($file_path);
            $mimetype = $mimes->getMimeType($pathinfo['extension']);
            switch ($mimetype) {
                case "image/jpeg":
                    $endpoint_path = $mimetypes['image/jpeg']['endpoint'];
                    break;
                case "image/tiff":
                    $endpoint_path = $mimetypes['image/tiff']['endpoint'];
                    break;
                case "image/jp2":
                    $endpoint_path = $mimetypes['image/jp2']['endpoint'];
                    break;
                default:
                    print "Image type $mimetype not recognized, not ingesting binary resource\n";
                    continue; 
            }
            $headers = array('Content-Type' => $mimetype, 'Content-Disposition' => 'attachment; filename="' . $pathinfo['basename'] . '"');
            $image_file_contents = fopen($file_path, 'r');
            $endpoint = $node_uri[0] . $endpoint_path;
            $binary_response = $client->request('POST', $endpoint, ['auth' => [$username, $password], 'headers' => $headers, 'body' => $image_file_contents]);
            if ($binary_response->getStatusCode() == 201) {
                $binary_uri = $binary_response->getHeader('Location');
                print " Binary resource " . $binary_uri[0] . " from file " . $file_path . " added to " . $node_uri[0] . ".\n";
                add_custom_fields_to_binary_resource($client, $record, $mimetype, $binary_uri[0]);
            }
            else {
                print " Binary resource (" . $mimetype . ") from file " . $file_path . " not added to " . $node_uri[0] .
                    " (HTTP response code " . $binary_response->getStatusCode() .")\n";
            }
        }
        else {
            print " File " . $record['file'] . " does not exist, so no binary resources added to " . $node_uri[0] . ".\n";
            continue;
        }
    }
}

function add_custom_fields_to_binary_resource($client, $record, $mimetype, $binary_uri) {
    global $mimetypes;
    global $username;
    global $password;

    $binary = array(
        'bundle' => array(array('target_id' => $mimetypes[$mimetype]['id'], 'target_type' => 'media_bundle')),
    );
    foreach ($record as $column_header => $value) {
        $media_fields_present = false;
        if (preg_match('/^media:/', $column_header)) {
            $media_fields_present = true;
            $field_name = preg_replace('/^media:/', '', $column_header);
            $binary[$field_name] = array(array('value' => $value));
        }

        if ($media_fields_present) {
            $json = json_encode($binary);
            var_dump($json);
            $headers = array('Content-Type' => 'application/json');
            $response = $client->request('PATCH', $binary_uri . '?_format=json', ['auth' => [$username, $password], 'headers' => $headers, 'body' => $json]);

            print "PATCH status code is " . $response->getStatusCode() . "\n";

        }
    }


}
