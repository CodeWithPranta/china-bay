<?php

namespace App\Translation;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Translation\FileLoader;

/**
 * File-first loader with admin-managed DB overrides on top.
 * lang/*.php and lang/*.json keep working as fallback/defaults.
 */
class DatabaseLoader implements Loader
{
    public function __construct(private FileLoader $files) {}

    public function load($locale, $group, $namespace = null): array
    {
        $lines = $this->files->load($locale, $group, $namespace);

        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }

        foreach (TranslationCache::overrides($locale, $group) as $key => $value) {
            $lines[$key] = $value;
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->files->addJsonPath($path);
    }

    public function namespaces(): array
    {
        return $this->files->namespaces();
    }
}
