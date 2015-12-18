<?php

/**
 * Adapted from DB Plumber module
 * https://github.com/smindel/silverstripe-dbplumber.
 */
class ArtefactCleanTask extends BuildTask
{
    protected $title = 'Show or Remove Database Artefacts';
    protected $description = <<<EOT
During development of a SilverStripe application it is common to delete
a data object class or remove a field from a data object. This leaves
obsolete columns and tables in your database. Because these columns or
tables may contain data that you still want, the SilverStripe framework
doesn't delete those automatically. This task displays the obsolete
columns and tables. It also provides a way to delete them. If you do
that, there is no undo.
EOT;

    public function run($request)
    {
        $dropping = (boolean) $request->requestVar('dropping');
        $artefacts = $this->artefacts();
        if (empty($artefacts)) {
            $this->writeln('<h2>Schema is clean; nothing to drop.</h2>');
        } else {
            if ($dropping) {
                $this->writeln('<h2>Dropping artefacts</h2>');
            } else {
                $this->writeln('<h2>Listing artefacts</h2>');
            }
            $this->writeln('<ul>');
            if ($dropping) {
                foreach ($artefacts as $table => $drop) {
                    if (is_array($drop)) {
                        $this->writeln('<li>'.$this->redText($this->dropColumns($table, $drop)));
                    } else {
                        $this->writeln('<li>'.$this->redText($this->dropTable($table)));
                    }
                }
            } else {
                foreach ($artefacts as $table => $drop) {
                    if (is_array($drop)) {
                        $this->writeln("<li>column {$table}.".implode("<li>column {$table}.", $drop));
                    } else {
                        $this->writeln("<li>table $table");
                    }
                }
            }
            $this->writeln('</ul>');
            $this->writeln('<h2>Next steps</h2>');
            if ($dropping) {
                $this->writeln('<a href="/dev/tasks/'.__class__.'">Re-check for artefacts</a><br>');
            } else {
                $this->writeln('<a href="/dev/tasks/'.__class__.'?dropping=1">Delete the artefacts (irreversible)</a><br>');
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

    private function redText($s)
    {
        return "<span style=\"color: red;\">$s</span>";
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
