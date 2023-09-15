# SilverStripe Database Artefact Cleaner
The SilverStripe Database Artefact Cleaner identifies and offers the option to remove unused tables, columns, and indexes in a SilverStripe database.

Over time, as the database schema evolves, SilverStripe's schema management tools may leave behind obsolete tables, columns, and indexes. This package assists developers by displaying these unnecessary artefacts and provides the option to delete them, ensuring a cleaner and optimized database.

## Installation
To install the Artefact Cleaner, use the following composer command:

```sh
composer require --dev oddnoc/silverstripe-artefactcleaner
```

## Usage
You can run the cleaner task using either the command line or directly through the browser. For MariaDB users (version 10+), the `ifexists=1` option can be added to prevent errors if the targeted column or index doesn't exist during the `dropping=1` operation.

### Command Line:
```sh
vendor/bin/sake dev/tasks/ArtefactCleanTask
vendor/bin/sake dev/tasks/ArtefactCleanTask dropping=1
vendor/bin/sake dev/tasks/ArtefactCleanTask dropping=1 ifexists=1
```

### Browser:
* https://example.org/dev/tasks/ArtefactCleanTask
* https://example.org/dev/tasks/ArtefactCleanTask?ifexists=1
* https://example.org/dev/tasks/ArtefactCleanTask?dropping=1
* https://example.org/dev/tasks/ArtefactCleanTask?dropping=1&ifexists=1

## Acknowledgements:
This package was inspired by:

[silverstripe-dbplumber](https://github.com/smindel/silverstripe-dbplumber) by smindel

## Version

5.0.0
