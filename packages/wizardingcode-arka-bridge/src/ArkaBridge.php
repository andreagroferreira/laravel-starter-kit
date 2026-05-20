<?php

declare(strict_types=1);

namespace WizardingCode\ArkaBridge;

use Symfony\Component\Yaml\Yaml;

final class ArkaBridge
{
    /**
     * @return array<string, mixed>
     */
    public function projectConfig(): array
    {
        $path = base_path('.arka/project.yaml');
        if (! file_exists($path)) {
            return [];
        }

        if (function_exists('yaml_parse_file')) {
            $parsed = yaml_parse_file($path);

            return is_array($parsed) ? $parsed : [];
        }

        // Fallback: very basic YAML parser using Symfony Yaml if installed
        if (class_exists(Yaml::class)) {
            $parsed = Yaml::parseFile($path);

            return is_array($parsed) ? $parsed : [];
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function compatibility(): array
    {
        $path = base_path('.arka/compatibility.yaml');
        if (! file_exists($path)) {
            return [];
        }

        if (function_exists('yaml_parse_file')) {
            $parsed = yaml_parse_file($path);

            return is_array($parsed) ? $parsed : [];
        }

        if (class_exists(Yaml::class)) {
            $parsed = Yaml::parseFile($path);

            return is_array($parsed) ? $parsed : [];
        }

        return [];
    }
}
