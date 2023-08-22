<?php

namespace Oddnoc\ArtefactCleaner\Tasks;

use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\CLI;
use SilverStripe\ORM\Connect\TempDatabase;
use SilverStripe\ORM\DB;

/**
 * SilverStripe task that deletes unused Tables, Columns and Indexes.
 */
class ArtefactCleanTask extends BuildTask
{
    protected $title = 'Display [remove] Database Artefacts';
    protected $description = 'Display and optionally run queries to delete obsolete columns, indexes, and tables.';
    private const IFEXISTS = 'IF EXISTS';
    private $if_exists;

    public function run($request)
    {
        $dropping = (bool) $request->requestVar('dropping');
        $this->if_exists = $request->requestVar('ifexists') ? self::IFEXISTS : '';
        $artefacts = $this->artefacts();
        if (empty($artefacts)) {
            $this->headerLine('Schema is clean; nothing to drop.');
            return;
        }
        switch ($dropping) {
            case true:
                $this->headerLine('Dropping artefacts');
                break;

            case false:
                $this->headerLine('SQL queries');
                break;
        }
        foreach ($artefacts as $table => $drop) {
            $this->cleanTable($table, $drop, $dropping);
        }
        $this->headerLine('Next step');
        switch ($dropping) {
            case true:
                $this->writeLine('Re-checking for artefacts');
                $request->offsetUnset('dropping');
                $this->run($request);
                break;
            case false:
                $this->writeLine('Delete the artefacts (IRREVERSIBLE!):');
                $this->writeLine('');
                $this->writeLine('  vendor/bin/sake dev/tasks/' . __class__ . ' dropping=1');
                $this->writeLine('  vendor/bin/sake dev/tasks/' . __class__ . ' dropping=1 ifexists=1');
                break;
        }
    }

    /**
     * @return array
     */
    private function artefacts()
    {
        $oldSchema = [];
        $newSchema = [];
        $current = DB::get_conn()->getSelectedDatabase();
        foreach (DB::table_list() as $lowercase => $dbTableName) {
            $oldSchema[$dbTableName] = ['indexes'=> [], 'fields' => []];
            $oldSchema[$dbTableName]['indexes'] = DB::get_schema()->indexList($dbTableName);
            $oldSchema[$dbTableName]['fields'] = DB::field_list($dbTableName);
        }
        $test = new TempDatabase();
        $test->build();
        foreach (DB::table_list() as $lowercase => $dbTableName) {
            $newSchema[$lowercase] = ['indexes'=> [], 'fields' => []];
            $newSchema[$lowercase]['indexes'] = DB::get_schema()->indexList($dbTableName);
            $newSchema[$lowercase]['fields'] = DB::field_list($dbTableName);
        }
        $test->kill();
        DB::get_conn()->selectDatabase($current);
        $artefacts = [];
        foreach ($oldSchema as $table => $data) {
            if (!isset($newSchema[strtolower($table)])) {
                $artefacts[$table] = $table;
                continue;
            }
            foreach ($data['fields'] as $field => $spec) {
                if (!isset($newSchema[strtolower($table)]['fields'][$field])) {
                    $artefacts[$table]['fields'][$field] = $field;
                }
            }
            foreach ($data['indexes'] as $index => $spec) {
                if (!isset($newSchema[strtolower($table)]['indexes'][$index])) {
                    $artefacts[$table]['indexes'][$index] = $index;
                }
            }
        }

        return $artefacts;
    }

    private function cleanTable($table, $drop, $dropping)
    {
        if (is_array($drop)) {
            if (isset($drop['indexes']) && $drop['indexes']) {
                $this->writeLine($this->dropIndexes($table, $drop['indexes'], $dropping));
            }
            if (isset($drop['fields']) && $drop['fields']) {
                $this->writeLine($this->dropColumns($table, $drop['fields'], $dropping));
            }
            return;
        }
        $this->writeLine($this->dropTable($table, $dropping));
    }

    private function dropTable($table, $dropping)
    {
        $query = sprintf('DROP TABLE %s `%s`', $this->if_exists, $table);
        if ($dropping) {
            DB::query($query);
        }
        return $query;
    }

    private function dropColumns($table, $columns, $dropping)
    {
        $query = sprintf(
            'ALTER TABLE `%s` DROP %s `%s`',
            $table,
            $this->if_exists,
            implode(sprintf('`, DROP %s `', $this->if_exists), $columns)
        );
        if ($dropping) {
            DB::query($query);
        }
        return $query;
    }

    private function dropIndexes($table, $indexes, $dropping)
    {
        $query = sprintf(
            'ALTER TABLE `%s` DROP INDEX %s `%s`',
            $table,
            $this->if_exists,
            implode(sprintf('`, DROP INDEX %s `', $this->if_exists), $indexes)
        );
        if ($dropping) {
            DB::query($query);
        }
        return $query;
    }

    private function headerLine($message)
    {
        if (Director::is_cli()) {
            echo CLI::text("\n## $message ##\n", 'cyan');
            return;
        }

        echo CLI::text("<strong>$message</strong>");
    }

    private function writeLine($message)
    {
        if (Director::is_cli()) {
            echo CLI::text("  $message\n", 'yellow');
            return;
        }

        echo CLI::text("<p>$message</p>");
    }
}
