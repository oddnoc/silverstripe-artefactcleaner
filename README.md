# SilverStripe Artefact Cleaner

Find and optionally delete unused tables, columns and indexes in a SilverStripe database.

SilverStripe uses an automatic schema creation tool that leaves behind old and obsolete tables, columns, and indexes.

This package displays and optionally deletes those artefacts.

## Installation

```sh
composer require --dev oddnoc/silverstripe-artefactcleaner
```

## Usage

Invoke the task via the command line or the browser. If you are running mariadb version 10+, you can add `ifexists=1` to the invocation. This will defend against errors should the column or index not exist at the time of the `dropping=1` invocation.

```sh
vendor/bin/sake dev/tasks/ArtefactCleanTask
vendor/bin/sake dev/tasks/ArtefactCleanTask dropping=1
vendor/bin/sake dev/tasks/ArtefactCleanTask dropping=1&ifexists=1
```

* https://example.org/dev/tasks/ArtefactCleanTask
* https://example.org/dev/tasks/ArtefactCleanTask?ifexists=1
* https://example.org/dev/tasks/ArtefactCleanTask?dropping=1
* https://example.org/dev/tasks/ArtefactCleanTask?dropping=1&ifexists=1

## Credits:

- https://github.com/smindel/silverstripe-dbplumber

## Version

4.0.1
