# SilverStripe ArtefactCleaner

Find and optionally delete unused tables, columns and indexes in a SilverStripe database.

SilverStripe uses an automatic schema creation tool that leaves behind old and
obsolete tables, columns and indexes.

This packages will show you, and optionally delete for you, those artefacts.

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

```
https://yoursite.com/dev/tasks/ArtefactCleanTask
https://yoursite.com/dev/tasks/ArtefactCleanTask?dropping=1
```

## Credits:

- https://github.com/oddnoc/silverstripe-artefactcleaner
- https://github.com/smindel/silverstripe-dbplumber
