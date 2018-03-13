# Islandora CLAW REST Ingester

Script to ingest a batch of nodes and accompanying JPEG, TIFF, or JP2 images using Islandora CLAW's REST interface.

## Installation

1. `git https://github.com/mjordan/claw_rest_ingester.git`
1. `cd claw_rest_ingester`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## Usage

You may need to adjust the following variables at the top of `ingest.php`:

```
$host = 'http://localhost:8000';
$username = 'admin';
$password = 'islandora';
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';
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

## Ingesting your own images

You can load your own nodes and accompanying imamges as long as the nodes have the `islandora_image` content type and the images are either JPEGs, TIFFs, or JP2s.

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

The names of the images can take any form you want since they are included in the CSV file (which can also be named whatever you want). That file must contain three columns, `File`, `Title`, and `Description`, corresponding to the fields that the default CLAW `islandora_image` content type has. The `File` column contains the full filename of the image file, and the other two columns contain the corresponding values for your nodes.

If you load you own data, be sure that the values of the `$input_dir` and `$csv_file` point to the right paths.

## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, open an issue first.
  * By opening a pull request, you agree to placing your contribution into the public domain.

## License

The Unlicense
