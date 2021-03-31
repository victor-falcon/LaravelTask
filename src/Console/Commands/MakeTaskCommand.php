<?php

namespace VictorFalcon\LaravelTask\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeTaskCommand extends GeneratorCommand
{
    protected $name = 'task:make';

    protected $description = 'Create a new task class';

    protected function getStub()
    {
        return __DIR__ . '/../../stub/model.stub';
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\'.$this->getDefaultPath();
    }

    protected function getDefaultPath(): string
    {
        $default = config('laravel-task.folders', 'app/Tasks');
        if (is_array($default)) {
            $default = $default[0];
        }

        $default = Str::replaceFirst('app/', '', $default);

        return $default;
    }
}
