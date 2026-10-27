<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use UnitEnum;

class GenerateEnumsToTs extends Command
{
    protected $signature = 'enums:ts';
    protected $description = 'Generate TypeScript enums from PHP enums';

    public function handle(): void
    {
        $enumPath = app_path('Enums');
        $outputPath = resource_path('js/generated/enums.ts');

        $files = File::allFiles($enumPath);

        $output = "/** AUTO-GENERATED FILE. DO NOT EDIT. */\n\n";

        foreach ($files as $file) {
            $class = $this->getClassFromFile($file->getPathname());

            if (!$class || !class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            // Ensure it's actually a PHP Enum
            if (!$ref->isEnum()) {
                continue;
            }

            /** @var class-string<UnitEnum> $class */
            $cases = $class::cases();

            $enumName = $ref->getShortName();

            $output .= "export const {$enumName} = {\n";

            foreach ($cases as $case) {
                $value = is_string($case->value)
                    ? "'{$case->value}'"
                    : $case->value;

                $output .= "  {$case->name}: {$value},\n";
            }

            $output .= "} as const;\n\n";

            $output .= "export type {$enumName}Type = typeof {$enumName}[keyof typeof {$enumName}];\n\n";
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $output);

        $this->info("Enums exported successfully to TS.");
    }

    private function getClassFromFile(string $path): ?string
    {
        $content = file_get_contents($path);

        preg_match('/namespace\s+([^;]+);/', $content, $ns);
        preg_match('/enum\s+(\w+)/', $content, $name);

        if (!isset($ns[1]) || !isset($name[1])) {
            return null;
        }

        return $ns[1] . '\\' . $name[1];
    }
}