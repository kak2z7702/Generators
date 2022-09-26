<?php

namespace Backpack\Generators\Console\Commands;

use Backpack\CRUD\ViewNamespaces;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ColumnBackpackCommand extends GeneratorCommand
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backpack:column';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:column {name} {--from=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Backpack column';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Column';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/column.stub';
    }

    /**
     * Alias for the fire method.
     *
     * In Laravel 5.5 the fire() method has been renamed to handle().
     * This alias provides support for both Laravel 5.4 and 5.5.
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $name = Str::of($this->getNameInput());
        $path = $this->getPath($name);

        if ($this->alreadyExists($this->getNameInput())) {
            $this->error("Error : $this->type $name already existed!");

            return false;
        }

        $src = null;
        if ($this->option('from')) {
            $column = Str::of($this->option('from'));
            $arr = ViewNamespaces::getFor('columns');
            foreach ($arr as $key => $value) {
                $viewPath = $value.'.'.$column;
                if (view()->exists($viewPath)) {
                    $src = view($viewPath)->getPath();
                    break;
                }
            }
            if ($src == null) {
                $this->error("Error : $this->type $column does not exist!");

                return false;
            }
        }

        $this->infoBlock("Creating {$name->replace('_', ' ')->title()} {$this->type}");
        $this->progressBlock("Creating view <fg=blue>resources/views/vendor/backpack/crud/columns/{$name->snake('_')}.blade.php</>");

        $this->makeDirectory($path);
        if ($src != null) {
            $this->files->copy($src, $path);
        } else {
            $this->files->put($path, $this->buildClass($name));
        }

        $this->closeProgressBlock();
        $this->newLine();
        $this->info($this->type.' created successfully.');
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $name
     * @return bool
     */
    protected function alreadyExists($name)
    {
        return $this->files->exists($this->getPath($name));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $file = Str::of($name)->snake('_');

        return resource_path("views/vendor/backpack/crud/columns/$file.blade.php");
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());       

        return $stub;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
