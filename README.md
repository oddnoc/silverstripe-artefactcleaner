silverstripe-artefactcleaner
============================

Find and optionally delete disused tables and fields in a SilverStripe database.

During development of a SilverStripe application it is common to delete a data
object class or remove a field from a data object. This leaves obsolete columns
and tables in your database. Because these columns or tables may contain data
that you still want, the SilverStripe framework doesn't delete those
automatically. This task displays the obsolete columns and tables in the form of
SQL `DROP` and `ALTER` commands. It also provides a way to delete them. If you
do that, **there is no undo**, so always make a backup first.

This task runs only on the CLI, so it needs an empty parameter to do the actual
dropping:

```sh
sake dev/tasks/ArtefactCleanTask
sake dev/tasks/ArtefactCleanTask '' dropping=1
```

To install:

```sh
composer require --dev oddnoc/silverstripe-artefactcleaner:^2.0.0
```

![helpfulrobot](https://helpfulrobot.io/oddnoc/silverstripe-artefactcleaner/badge)

Version: 3.0.0
