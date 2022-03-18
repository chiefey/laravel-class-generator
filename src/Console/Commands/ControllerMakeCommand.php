<?php

namespace Chiefey\Generator\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends \Illuminate\Routing\Console\ControllerMakeCommand
{
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
     * Generate the form requests for the given model and classes.
     *
     * @param  string  $modelName
     * @param  string  $storeRequestClass
     * @param  string  $updateRequestClass
     * @return array
     */
    protected function generateFormRequests($modelClass, $storeRequestClass, $updateRequestClass)
    {
        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        $this->call('make:request', array_filter([
            'name' => $storeRequestClass,
            '--model' => class_basename($modelClass),
            '--definition' => $this->option('definition'),
        ]));

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        $this->call('make:request', array_filter([
            'name' => $updateRequestClass,
            '--model' => class_basename($modelClass),
            '--definition' => $this->option('definition'),
        ]));

        return [$storeRequestClass, $updateRequestClass];
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        return array_merge(parent::buildModelReplacements($replace), [
            '{{ modelSnake }}' => Str::snake(class_basename($modelClass))
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['definition', null, InputOption::VALUE_REQUIRED, 'Indicates the definition of the generated model']
        ]);
    }
}
