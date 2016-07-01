<?php

/**
 * Adapted from DB Plumber module
 * https://github.com/smindel/silverstripe-dbplumber.
 */
class ArtefactCleanTask extends BuildTask
{
    protected $title = 'Show or Remove Database Artefacts';
    protected $description = <<<'EOT'
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
        $dropping = (bool) $request->requestVar('dropping');
        $artefacts = $this->artefacts();
        if (empty($artefacts)) {
            $this->headerln('Schema is clean; nothing to drop.');
        } else {
            if ($dropping) {
                $this->headerln('Dropping artefacts');
            } else {
                $this->headerln('Listing artefacts');
            }
            foreach ($artefacts as $table => $drop) {
                if (is_array($drop)) {
                    $this->writeln('* '.$this->dropColumns($table, $drop, $dropping));
                } else {
                    $this->writeln('* '.$this->dropTable($table, $dropping));
                }
            }
            $this->headerln('Next step');
            if ($dropping) {
                $this->writeln('Re-check for artefacts: sake /dev/tasks/'.__class__);
            } else {
                $this->writeln('Delete the artefacts (IRREVERSIBLE!): sake dev/tasks/'.__class__." '' dropping=1");
            }
        }
    }

    /**
     * @return array
     */
    private function artefacts()
    {
        $oldschema = [];
        $newschema = [];
        $current = DB::get_conn()->getSelectedDatabase();
        foreach (DB::table_list() as $lowercase => $dbtablename) {
            $oldschema[$dbtablename] = DB::field_list($dbtablename);
        }

        $test = new SapphireTest();
        $test->create_temp_db();
        foreach (DB::table_list() as $lowercase => $dbtablename) {
            $newschema[$lowercase] = DB ::field_list($dbtablename);
        }
        $test->kill_temp_db();
        DB::get_conn()->selectDatabase($current);

        $artefacts = [];
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

    private function dropTable($table, $dropping = false)
    {
        $q = "DROP TABLE \"$table\"";
        if ($dropping) {
            DB::query($q);
        }

        return $q;
    }

    private function dropColumns($table, $columns, $dropping = false)
    {
        $q = "ALTER TABLE \"$table\" DROP \"".implode('", DROP "', $columns).'"';
        if ($dropping) {
            DB::query($q);
        }

        return $q;
    }

    private function headerln($s)
    {
        echo "\n## $s ##\n\n";
    }

    private function writeln($s)
    {
        echo "$s\n";
        flush();
    }
}
