# Upgrade to SilverStripe 6

## Dependencies

⚠️ Update `composer.json` to require `silverstripe/framework: ^6.0` (previously supported `^4.0 || ^5.0`)

## Task Architecture Changes

⚠️ **BuildTask has been replaced with Symfony Console Commands**

### Command Registration

- Replace `private static $segment` with `protected static string $commandName`
- Command is now invoked via `vendor/bin/sake tasks:{commandName}` instead of `dev/tasks/{segment}`

### Method Signatures

⚠️ Replace `run($request): void` method with `execute(InputInterface $input, PolyOutput $output): int`

- Return `Command::SUCCESS` instead of void
- Access request parameters via `$input->getOption()` instead of `$request->requestVar()`
- Store `PolyOutput $output` as instance property for output methods

### Input Options

Implement `getOptions(): array` method returning `InputOption[]` objects to define CLI options:
```php
new InputOption('dropping', 'd', InputOption::VALUE_NONE, 'Description')
```

### Output Methods

- Remove `Director::is_cli()` and `CLI::text()` usage
- Replace with `$this->output->writeln()` for all output
- Use `<info>text</info>` tags for colored/styled output

### Property Type Declarations

Add type declarations to all properties:
- `private const IFEXISTS` → `private const string IFEXISTS`
- `protected $description` → `protected static string $description`
- `protected $title` → `protected string $title`
- `private static $segment` → `protected static string $commandName`

### Breaking Changes

🔍 **Recursive task execution removed** - The pattern of recursively calling `$this->run($request)` is no longer supported. Manual re-execution required after dropping artefacts.

## Removed Imports

- `SilverStripe\Control\Director`
- `SilverStripe\Dev\CLI`

## New Imports

- `SilverStripe\PolyExecution\PolyOutput`
- `Symfony\Component\Console\Command\Command`
- `Symfony\Component\Console\Input\InputInterface`
- `Symfony\Component\Console\Input\InputOption`
