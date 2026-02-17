<?php

declare(strict_types=1);

namespace Frolax\Typescript\Commands;

use Frolax\Typescript\Mappers\TypeMapperRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ShowMappingsCommand extends Command
{
    protected $signature = 'typescript:mappings';

    protected $description = 'Show the current type mappings for TypeScript generation';

    public function handle(TypeMapperRegistry $mapperRegistry): int
    {
        $this->info('TypeScript Type Mappings');
        $this->newLine();

        // Show custom mappings from config
        $customMappings = Config::get('typescript.mappings.custom', []);
        if (! empty($customMappings)) {
            $this->info('Custom Mappings (from typescript.php config):');
            $this->table(
                ['PHP/DB Type', 'TypeScript Type'],
                collect($customMappings)->map(fn ($tsType, $phpType) => [$phpType, $tsType])->all()
            );
            $this->newLine();
        }

        // Show all supported types
        $this->info('Default Mappings:');
        $defaultTypes = [
            'string', 'text', 'char', 'varchar', 'guid',
            'integer', 'int', 'smallint', 'mediumint', 'bigint',
            'float', 'double', 'decimal', 'year',
            'boolean', 'bool',
            'date', 'datetime', 'timestamp', 'time',
            'uuid', 'ulid', 'json', 'binary', 'enum',
        ];

        $rows = [];
        foreach ($defaultTypes as $type) {
            if ($mapperRegistry->supports($type)) {
                $rows[] = [$type, $mapperRegistry->resolve($type)];
            }
        }

        $this->table(['DB/PHP Type', 'TypeScript Type'], $rows);

        return self::SUCCESS;
    }
}
