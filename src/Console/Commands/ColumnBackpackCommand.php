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
     * View Namespace.
     *
     * @var string
     */
    protected $viewNamespace = 'columns';

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
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return Str::of($this->argument('name'))
            ->trim()
            ->snake('_')
            ->value;
    }
}
