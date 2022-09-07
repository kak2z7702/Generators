<?php

namespace Backpack\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class FieldBackpackCommand extends GeneratorCommand
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backpack:field';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:field {name} {--from=} {--withAssets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Backpack field';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Field';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $field = "text";
        if ($this->option('from')) {
            $field = Str::of($this->option('from'));
        }

        return base_path("vendor/backpack/crud/src/resources/views/crud/fields/$field.blade.php");
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
            $this->error("Error : $this->type already existed!");

            return false;
        }

        $this->infoBlock("Creating {$name->replace('_', ' ')->title()} {$this->type}");
        $this->progressBlock("Creating view <fg=blue>resources/views/vendor/backpack/crud/fields/{$name->snake('_')}.blade.php</>");
        
        try {
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($name));
        } catch (\Throwable $th) {
            $this->newLine();
            $this->newLine();
            $this->error("Error : $this->type ".$th->getMessage());

            return false;
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

        return resource_path("views/vendor/backpack/crud/fields/$file.blade.php");
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub =  $this->files->get($this->getStub());
        if ($this->option('withAssets')) {
            $asset = $this->files->get(__DIR__.'/../stubs/with-assets.stub');
            $asset = str_replace('my_field', $name->snake('_'), $asset);
            $asset = str_replace('myField', $name->camel(), $asset);
            $asset = str_replace('MyField', $name->studly(), $asset);
            $stub.=$asset;
        }

        return $stub;

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [

        ];
    }
}
