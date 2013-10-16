silverstripe-artefactcleaner
============================

Find and optionally delete disused tables and fields in a SilverStripe
database.

During development of a SilverStripe application it is common to delete
a data object class or remove a field from a data object. This leaves
obsolete columns and tables in your database. Because these columns or
tables may contain data that you still want, the SilverStripe framework
doesn't delete those automatically. This task displays the obsolete
columns and tables. It also provides a way to delete them. If you do
that, there is no undo.

Version: 1.0.2
