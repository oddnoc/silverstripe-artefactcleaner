<?php

/**
 * Adapted from DB Plumber module
 * https://github.com/smindel/silverstripe-dbplumber.
 */
class ArtefactCleanTask extends BuildTask
{
    protected $title = 'Show or Remove Database Artefacts';
    protected $description = <<<EOT
(CLI only) During development of a SilverStripe application it is common to delete
a data object class or remove a field from a data object. This leaves
obsolete columns and tables in your database. Because these columns or
tables may contain data that you still want, the SilverStripe framework
doesn't delete those automatically. This task displays the obsolete
columns and tables. It also provides a way to delete them. If you do
that, there is no undo.
EOT;

    public function run($request)
    {
        if (!Director::is_cli()) {
            $this->writeln('This task works only on the command line. Exiting.');
            return;
        }
        $dropping = (boolean) $request->requestVar('dropping');
        $artefacts = $this->artefacts();
        if (empty($artefacts)) {
            $this->writeln('## Schema is clean; nothing to drop. ##');
        } else {
            if ($dropping) {
                $this->writeln('## Dropping artefacts ##');
            } else {
                $this->writeln('## Listing artefacts ##');
            }
            if ($dropping) {
                foreach ($artefacts as $table => $drop) {
                    if (is_array($drop)) {
                        $this->writeln('* '.$this->writeln($this->dropColumns($table, $drop)));
                    } else {
                        $this->writeln('* '.$this->writeln($this->dropTable($table)));
                    }
                }
            } else {
                foreach ($artefacts as $table => $drop) {
                    if (is_array($drop)) {
                        $this->writeln("* column {$table}.".implode("* column {$table}.", $drop));
                    } else {
                        $this->writeln("* table $table");
                    }
                }
            }
            $this->writeln('## Next steps ##');
            if ($dropping) {
                $this->writeln('Re-check for artefacts: sake /dev/tasks/'.__class__);
            } else {
                $this->writeln("Delete the artefacts (IRREVERSIBLE!): sake dev/tasks/".__class__." '' dropping=1");
            }
        }
        $this->writeln('<a href="/dev/tasks">Task list</a>');
    }

    private function artefacts()
    {
        $oldschema = array();
        $newschema = array();
        $current = DB::getConn()->currentDatabase();
        foreach (DB::getConn()->tableList() as $lowercase => $dbtablename) {
            $oldschema[$dbtablename] = DB::getConn()->fieldList($dbtablename);
        }

        DB::getConn()->selectDatabase('tmpdb');
        $test = new SapphireTest();
        $test->create_temp_db();
        foreach (DB::getConn()->tableList() as $lowercase => $dbtablename) {
            $newschema[$lowercase] = DB::getConn()->fieldList($dbtablename);
        }
        $test->kill_temp_db();
        DB::getConn()->selectDatabase($current);

        $artefacts = array();
        foreach ($oldschema as $table => $fields) {
            if (!isset($newschema[strtolower($table)])) {
                $artefacts[$table] = $table;
                continue;
            }

            foreach ($fields as $field => $spec) {
                if (!isset($newschema[strtolower($table)][$field])) {
                    $artefacts[$table][$field] = $field;
                }
            }
        }

        return $artefacts;
    }

    private function dropTable($table)
    {
        $q = "DROP TABLE \"$table\"";
        DB::query($q);

        return $q;
    }

    private function dropColumns($table, $columns)
    {
        $q = "ALTER TABLE \"$table\" DROP \"".implode('", DROP "', $columns).'"';
        DB::query($q);

        return $q;
    }

    private function writeln($s)
    {
        if (Director::is_cli()) {
            $s = strip_tags($s, '<a>');
        }
        echo "$s\n";
        flush();
    }
}
