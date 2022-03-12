<?php

namespace Chiefey\Generator\Console\Commands;

use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Foundation\Console\ModelMakeCommand as IlluminateModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends IlluminateModelMakeCommand
{
    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
            '--api' => $this->option('api'),
            '--requests' => $this->option('requests') || $this->option('all'),
            '--force' => $this->option('force'),
            '--definition' => $this->option('definition'),
        ]));
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $this->definition = json_decode($this->option('definition'), true);

        if ($this->definition) {
            $attributes = [];
            foreach ($this->definition['attributes'] as $attribute) {
                $attributes[] = new ColumnDefinition($attribute);
            }
            $this->definition['attributes'] = collect($attributes);
        }

        $replace = [];

        $replace = $this->buildFillableReplacements($replace);

        $replace = $this->buildHiddenReplacements($replace);

        $replace = $this->buildCastsReplacements($replace);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildFillableReplacements(array $replace)
    {
        $fillable = '';
        if (!empty($this->definition['fillable'])) {
            $fillable = collect($this->definition['fillable'])->map(function ($item) {
                return "'$item'";
            })->implode(",\n        ");
        }

        return array_merge($replace, [
            '{{ fillable }}' => $fillable,
        ]);
    }

    /**
     * Replace the hidden for the given stub.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildHiddenReplacements(array $replace)
    {
        $hidden = '';
        if (!empty($this->definition['hidden'])) {
            $hidden = collect($this->definition['hidden'])->map(function ($item) {
                return "'$item'";
            })->implode(",\n        ");
        }

        return array_merge($replace, [
            '{{ hidden }}' => $hidden,
        ]);
    }

    /**
     * Replace the casts for the given stub.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildCastsReplacements(array $replace)
    {
        $casts = '';
        if (!empty($this->definition['casts'])) {
            $casts = collect($this->definition['casts'])->map(function ($item, $key) {
                return "'$key' => '$item'";
            })->implode(",\n        ");
        }

        return array_merge($replace, [
            '{{ casts }}' => $casts,
        ]);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        if (file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))) {
            return $customPath;
        } else if (file_exists($customPath = __DIR__ . $stub)) {
            return $customPath;
        } else {
            return parent::resolveStubPath($stub);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['definition', null, InputOption::VALUE_REQUIRED, 'Indicates the definition of the generated model'],
        ]);
    }
}
