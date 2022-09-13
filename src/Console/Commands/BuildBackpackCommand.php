<?php

namespace Backpack\Generators\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BuildBackpackCommand extends Command
{
    use \Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:build
        {--validation=request : Validation type, must be request, array or field}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create CRUDs for all Models that don\'t already have one.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // make a list of all models
        $models = $this->getModels(base_path('app'));

        if (! count($models)) {
            $this->errorBlock('No models found.');

            return;
        }

        foreach ($models as $model) {
            $this->call('backpack:crud', ['name' => $model, '--validation' => $this->option('validation')]);
            $this->line('  <fg=gray>----------</>');
        }

        $this->deleteLines();
    }

    private function getModels($path)
    {
        $out = [];
        $results = scandir($path);

        foreach ($results as $result) {
            if ($result === '.' or $result === '..') {
                continue;
            }
            $filename = $path.'/'.$result;

            if (is_dir($filename)) {
                $out = array_merge($out, $this->getModels($filename));
            } else {
                require_once $filename;

                // Try to load it by path as namespace
                $class = Str::of($filename)
                    ->after(base_path())
                    ->trim('\\/')
                    ->replace('/', '\\')
                    ->before('.php')
                    ->ucfirst();

                if (is_a($class->value(), Model::class, true)) {
                    $out[] = $class->afterLast('\\');
                    continue;
                }

                // Try to load it from file content
                $fileContent = Str::of(file_get_contents($filename));
                $namespace = $fileContent->match('/namespace (.*);/')->value();
                $classname = $fileContent->match('/class (\w+)/')->value();

                if ($namespace && $classname && is_a("$namespace\\$classname", \Illuminate\Database\Eloquent\Model::class, true)) {
                    $out[] = $classname;
                    continue;
                }
            }
        }

        return $out;
    }
}
