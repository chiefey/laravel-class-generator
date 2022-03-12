<?php

namespace Chiefey\Generator\Console\Commands;

// use Illuminate\Support\Facades\DB;
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
