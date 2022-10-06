<?php

namespace Backpack\Generators\Console\Commands;

use Backpack\CRUD\ViewNamespaces;
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
    protected $signature = 'backpack:button {name} {--from=}';

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
     * View Namespace.
     *
     * @var string
     */
    protected $viewNamespace = 'buttons';

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
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name = Str::of($this->getNameInput());
        $path = Str::of($this->getPath($name));
        $pathRelative = $path->after(base_path())->replace('\\', '/')->trim('/');

        $this->infoBlock("Creating {$name->replace('_', ' ')->title()} {$this->type}");
        $this->progressBlock("Creating view <fg=blue>{$pathRelative}</>");

        if ($this->alreadyExists($name)) {
            $this->closeProgressBlock('Already existed', 'yellow');

            return false;
        }

        $source = null;
        if ($this->option('from')) {
            $from = $this->option('from');
            $namespaces = ViewNamespaces::getFor($this->viewNamespace);
            foreach ($namespaces as $namespace) {
                $viewPath = "$namespace.$from";
                if (view()->exists($viewPath)) {
                    $source = view($viewPath)->getPath();
                    break;
                }
            }

            // full file path may be provided
            if (file_exists($from)) {
                $source = $from;
            }

            if (! $source) {
                $this->errorProgressBlock();
                $this->note("$this->type '$from' does not exist!", 'red');
                $this->newLine();

                return false;
            }
        }

        $this->makeDirectory($path);

        if ($source) {
            $this->files->copy($source, $path);
        } else {
            $this->files->put($path, $this->buildClass($name));
        }

        $this->closeProgressBlock();
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
}
