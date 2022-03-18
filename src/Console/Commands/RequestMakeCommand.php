<?php

namespace Chiefey\Generator\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RequestMakeCommand extends \Illuminate\Foundation\Console\RequestMakeCommand
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
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        $isUpdate = Str::contains($this->argument('name'), 'Update');

        if (($def = json_decode($this->option('definition'), true)) && !empty($def['attributes'])) {
            $modelName = $this->option('model');
            $modelClass = $this->qualifyModel($modelName);
            $model = new $modelClass;

            $columns = collect($def['attributes'])->pluck('name');

            $filtered = $columns->filter(function ($value) use ($model) {
                return in_array($value, $model->getFillable());
            });

            $prepareForValidation = $filtered->map(function ($item) {return "'$item'";})->implode(',');

            $rules = $filtered->map(function ($item) use ($isUpdate) {
                $sometimes = $isUpdate ? "'sometimes', " : '';
                return "// '$item' => [$sometimes'required']";
            })->implode(',
            ');

            $replace['{{ prepareForValidation }}'] = "// \$this->replace(\$this->except([$prepareForValidation]));";
            $replace['{{ rules }}'] = $rules;
        } else {
            $replace['{{ prepareForValidation }}'] = '//';
            $replace['{{ rules }}'] = '//';
        }

        if ($isUpdate) {
            $replace['{{ withValidator }}'] = '$validator->after(function ($validator) {
            if (empty($this->request->all())) {
                $validator->errors()->add(\'requestBody\', \'At least one attribute must be specified\');
            }
        });';
        } else {
            $replace['{{ withValidator }}'] = '//';
        }

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Indicates the model tied to this request.'],
            ['definition', null, InputOption::VALUE_REQUIRED, 'Indicates the definition of the generated model'],
        ];
    }
}