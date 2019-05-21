# SilverStripe Artefact Cleaner

Find and optionally delete unused tables, columns and indexes in a SilverStripe database.

SilverStripe uses an automatic schema creation tool that leaves behind old and
obsolete tables, columns, and indexes.

This package displays and optionally deletes those artefacts.

## Installation

```sh
composer require --dev oddnoc/silverstripe-artefactcleaner
```

## Usage

Invoke the task via the command line or the browser.

```sh
vendor/bin/sake dev/tasks/ArtefactCleanTask
vendor/bin/sake dev/tasks/ArtefactCleanTask dropping=1
```

* https://example.org/dev/tasks/ArtefactCleanTask
* https://example.org/dev/tasks/ArtefactCleanTask?dropping=1

## Credits:

- https://github.com/smindel/silverstripe-dbplumber

## Version

3.0.1
