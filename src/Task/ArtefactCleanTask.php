<?php

namespace Oddnoc\ArtefactCleaner\Task;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Connect\TempDatabase;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * SilverStripe task that deletes unused Tables, Columns and Indexes.
 */
class ArtefactCleanTask extends BuildTask
{
    private const string IFEXISTS = 'IF EXISTS';

    protected static string $description = 'Display and optionally run queries to delete obsolete columns, indexes, and tables.';

    protected string $title = 'Display [remove] Database Artefacts';

    private $if_exists;

    protected static string $commandName = 'artefact-clean';

    private PolyOutput $output;

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $this->output = $output;
        $dropping = (bool)$input->getOption('dropping');
        $this->if_exists = $input->getOption('ifexists') ? self::IFEXISTS : '';
        $artefacts = $this->artefacts();
        if ($artefacts === []) {
            $this->headerLine('Schema is clean; nothing to drop.');
            return Command::SUCCESS;
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
                // @TODO (SS6 upgrade) - recursively re-running the task is not supported in the new architecture
                // Re-run the task manually if needed
                break;
            case false:
                $this->writeLine('Delete the artefacts (IRREVERSIBLE!):');
                $this->writeLine('');
                $commandName = static::$commandName;
                $this->writeLine("- vendor/bin/sake tasks:{$commandName} --dropping");
                $this->writeLine("- vendor/bin/sake tasks:{$commandName} --dropping --ifexists");
                break;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    private function artefacts(): array
    {
        $oldSchema = [];
        $newSchema = [];
        $current = DB::get_conn()->getSelectedDatabase();
        foreach (DB::table_list() as $lowercase => $dbTableName) {
            $oldSchema[$dbTableName] = ['indexes' => [], 'fields' => []];
            $oldSchema[$dbTableName]['indexes'] = DB::get_schema()->indexList($dbTableName);
            $oldSchema[$dbTableName]['fields'] = DB::field_list($dbTableName);
        }

        $test = TempDatabase::create();
        $test->build();
        foreach (DB::table_list() as $lowercase => $dbTableName) {
            $newSchema[$lowercase] = ['indexes' => [], 'fields' => []];
            $newSchema[$lowercase]['indexes'] = DB::get_schema()->indexList($dbTableName);
            $newSchema[$lowercase]['fields'] = DB::field_list($dbTableName);
        }

        $test->kill();
        DB::get_conn()->selectDatabase($current);
        $artefacts = [];
        foreach ($oldSchema as $table => $data) {
            if (!isset($newSchema[strtolower((string) $table)])) {
                $artefacts[$table] = $table;
                continue;
            }

            foreach (array_keys($data['fields']) as $field) {
                if (!isset($newSchema[strtolower((string) $table)]['fields'][$field])) {
                    $artefacts[$table]['fields'][$field] = $field;
                }
            }

            foreach (array_keys($data['indexes']) as $index) {
                if (!isset($newSchema[strtolower((string) $table)]['indexes'][$index])) {
                    $artefacts[$table]['indexes'][$index] = $index;
                }
            }
        }

        return $artefacts;
    }

    private function cleanTable(string $table, $drop, bool $dropping): void
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

    private function dropColumns(string $table, array $columns, bool $dropping): string
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

    private function dropIndexes(string $table, array $indexes, bool $dropping): string
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

    private function dropTable(string $table, bool $dropping): string
    {
        $query = sprintf('DROP TABLE %s `%s`', $this->if_exists, $table);
        if ($dropping) {
            DB::query($query);
        }

        return $query;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('dropping', 'd', InputOption::VALUE_NONE, 'Execute the drop queries (IRREVERSIBLE!)'),
            new InputOption('ifexists', 'i', InputOption::VALUE_NONE, 'Add "IF EXISTS" clause to drop statements'),
        ];
    }

    private function headerLine(string $message): void
    {
        $this->output->writeln(sprintf('<info>## %s ##</info>', $message));
    }

    private function writeLine(string $message): void
    {
        $this->output->writeln($message);
    }
}
