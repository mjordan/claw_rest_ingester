# Islandora CLAW REST Ingester

Script to ingest a batch of nodes and accompanying media (images, audio, video, and binary files) using Islandora CLAW's REST interface.

## Installation

1. `git https://github.com/mjordan/claw_rest_ingester.git`
1. `cd claw_rest_ingester`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

Note that you do not need to install this tool on your Islandora server. It can be used from any location that has network access to your server.

## Usage

You may need to adjust the following variables at the top of `ingest.php`:

```
$host = 'http://localhost:8000';
$username = 'admin';
$password = 'islandora';
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';
// The term ID from Islandora's Media Use taxonomy that you want to assign to media.
$media_use_tid = 15;
// The Drupal filesystem where you want the files to be saved.
// $drupal_filesystem = 'public://';
$drupal_filesystem = 'fedora://';
// Term ID from the Islandora Models taxonomy.
$model = '23';
```

To execute the script using the sample data, run this:

`php ingest.php`

You should see the following output (with different node URIs):

```
Node "Small boats in Havana Harbour" (http://localhost:8000/node/8) created.
 Binary resource (image/tiff) from file input_data/IMG_1410.tif added to http://localhost:8000/node/8.
Node "Manhatten Island" (http://localhost:8000/node/9) created.
 Binary resource (image/jp2) from file input_data/IMG_2549.jp2 added to http://localhost:8000/node/9.
Node "Looking across Burrard Inlet" (http://localhost:8000/node/10) created.
 Binary resource (image/jpeg) from file input_data/IMG_2940.JPG added to http://localhost:8000/node/10.
Node "Amsterdam waterfront" (http://localhost:8000/node/11) created.
 Binary resource (image/jpeg) from file input_data/IMG_2958.JPG added to http://localhost:8000/node/11.
Node "Alcatraz Island" (http://localhost:8000/node/12) created.
 Binary resource (image/jpeg) from file input_data/IMG_5083.JPG added to http://localhost:8000/node/12.
```

Go look at your Drupal content and enjoy.

## Ingesting your own objects

You can load your own nodes and accompanying media as long as the nodes have the Repository Item (`islandora_object`) content type.

The directory that contains the data to be ingested is arranged like this:

```
your_folder/
├── image1.JPG
├── pic_saturday.jpg
├── image-27262.jp2
├── IMG_2958.JPG
├── someimage.tif
└── metadata.csv
```

The names of the images can take any form you want since they are included in the CSV file (which can also be named whatever you want). That file must contain three columns, `file`, `title`, and `field_description`, corresponding to the fields that the default CLAW `islandora_object` content type has. The `file` column contains the full filename of the image file, and the other two columns contain the corresponding values for your nodes.

If you load you own data, be sure that the values of the `$input_dir` and `$csv_file` point to the right paths.

## Adding additional or custom fields

The sample CSV file comes with data in the `title` and `field_description` fields, but you can add additional fields to populate your Repository Item objects. In order to do this, add fields to the input CSV using column headers that match the machine name of fields that exist in the Repository Item content type. For example, if you add a field with the machine name `field_rights` to your CSV, you can ingest nodes using the following CSV file:

```
file,title,field_description,field_rights
"IMG_1410.tif","Small boats in Havana Harbour","Taken on vacation in Cuba.","CC BY 4.0"
"IMG_2549.jp2","Manhatten Island","Taken from the ferry from downtown New York to Highlands, NJ. Weather was windy.","CC BY 4.0"
"IMG_2940.JPG","Looking across Burrard Inlet","View from Deep Cove to Burnaby Mountain. Simon Fraser University is visible on the top of the mountain in the distance.","CC0"
"IMG_2958.JPG","Amsterdam waterfront","Amsterdam waterfront on an overcast day.","CC BY 4.0"
"IMG_5083.JPG","Alcatraz Island","Taken from Fisherman's Wharf, San Francisco.","CC0"
```

You can add your own custom fields as well, as long as the column headers match the machine name of the node fields. It important that the fields already exist on the Islandora Repository Item content type before attempting to ingest the nodes. If the column heading don't match the machine name of an existing field, Drupal with respond with a `500` HTTP error and will not ingest the node.

Currently, only fields that of type "Text" can be added. Support for Entity Reference and other field types is under development.


## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, open an issue first.
  * By opening a pull request, you agree to placing your contribution into the public domain.

## License

The Unlicense
