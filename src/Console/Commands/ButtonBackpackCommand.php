<?php

namespace Backpack\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ButtonBackpackCommand extends GeneratorCommand
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backpack:button';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:button {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Backpack button';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Button';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../stubs/button.stub';
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
            $this->error($this->type.' already existed!');

            return false;
        }

        $this->infoBlock("Creating {$name->replace('_', ' ')->title()} {$this->type}");
        $this->progressBlock("Creating view <fg=blue>resources/views/vendor/backpack/crud/buttons/${name}.blade.php</>");

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        
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
        return resource_path("views/vendor/backpack/crud/buttons/$name.blade.php");        
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
        $stub = str_replace('dummy', $name, $stub);
        
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
