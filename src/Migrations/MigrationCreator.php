<?php

namespace Chiefey\Generator\Migrations;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     *
     * @throws \Exception
     */
    public function create($name, $path, $table = null, $create = false, $definition = null)
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateStub($name, $stub, $table, $definition)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            $type = 'migration';
        } elseif ($create) {
            $type = 'migration.create';
        } else {
            $type = 'migration.update';
        }

        if($this->files->exists($customPath = $this->customStubPath."/$type.stub")){
            $stub = $customPath;
        } else if ($this->files->exists($customPath = __DIR__.'/stubs'."/$type.stub")){
            $stub = $customPath;
        } else {
            $stub = $this->stubPath()."/$type.stub";
        }

        return $this->files->get($stub);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string|null  $table
     * @return string
     */
    protected function populateStub($name, $stub, $table, $definition = null)
    {
        $blueprint = '';

        foreach (json_decode($definition, true)['attributes'] as $att) {
            if (in_array($att['name'], ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            if ($att['name'] == 'remember_token') {
                $column = 'rememberToken()';
            } else {
                $extra = '';
                if (!empty($att['unique'])) {
                    $extra = '->unique()';
                } else if (!empty($att['nullable'])) {
                    $extra = '->nullable()';
                }
                $column = "{$att['type']}('{$att['name']}')$extra";
            }
            $blueprint .= "
            \$table->$column;";
        }

        return str_replace(
            ['{{ blueprint }}'],
            $blueprint,
            parent::populateStub($name, $stub, $table)
        );
    }
}