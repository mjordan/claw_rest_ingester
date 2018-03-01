# Islandora CLAW REST Ingester

Proof of concept script to ingest a batch of nodes and accompanying JPEG images using Islandora CLAW's REST interface. REST workflow is based on that described in https://github.com/Islandora-CLAW/islandora/pull/76.

## Installation

1. `git https://github.com/mjordan/claw_rest_ingester.git`
1. `cd claw_rest_ingester`
1. `php composer.phar install` (or equivalent on your system, e.g., `./composer install`)

## Usage

You may need to adjust the following variables at the top of `ingest.php`:

```
$host = 'http://localhost:8000';
$credentials = array('admin', 'islandora');
$input_dir = 'input_data';
$csv_file = $input_dir . '/metadata.csv';
```

To execute the script using the sample data, run this:

`php ingest.php`

## Ingesting your own images

You can load your own nodes and accompanying imamges as long as the nodes have the `islandora_image` content type and the images are JPEGs.

The directory that contains the data to be ingested is arranged like this:

```
your_folder/
├── IMG_1410.JPG
├── IMG_2549.JPG
├── IMG_2940.JPG
├── IMG_2958.JPG
├── IMG_5083.JPG
└── metadata.csv
```

The names of the images can take any form you want since they are included in the CSV file (which can also be named whatever you want). That file must contain three columns, `File`, `Title`, and `Description`, corresponding to the fields that the default CLAW `islandora_image` content type has. The `File` column contains the full filename of the JPEG, and the other two columns contain the corresponding values for your nodes.

If you load you own data, be sure that the values of the `$input_dir` and `$csv_file` point to the right paths.

## Maintainer

* [Mark Jordan](https://github.com/mjordan)

## Development and feedback

* If you discover a bug, or have a use case not documented here, open an issue.
* If you want to open a pull request, open an issue first.
  * By opening a pull request, you agree to placing your contribution into the public domain.

## License

The Unlicense
