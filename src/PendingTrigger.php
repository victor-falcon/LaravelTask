<?php

declare(strict_types=1);

namespace VictorFalcon\LaravelTask;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;

class PendingTrigger
{
    protected bool $error = false;
    protected bool $executed = false;
    protected Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function withValid(array $data): self
    {
        try {
            $validator = Validator::make(
                $data,
                $this->task->rules(),
                $this->task->messages(),
                $this->task->customAttributes()
            );

            if ($validator->fails()) {
                $this->error = true;
                $this->task->validationError($validator);
            }

            $this->task->validated($validator->validated());
        } catch (\Exception $exception) {
            $this->error = true;
            throw $exception;
        }

        return $this;
    }

    public function by(Authenticatable $user): self
    {
        $this->task->withUser($user);

        return $this;
    }

    public function forceResult()
    {
        return $this->execute();
    }

    private function execute()
    {
        $this->executed = true;

        return app()->call([$this->task, 'handle']);
    }

    public function __destruct()
    {
        if ($this->executed === false && $this->error === false) {
            $this->result();
        }
    }

    public function result()
    {
        $this->resolveAuthorization();

        return $this->execute();
    }

    private function resolveAuthorization(): void
    {
        if (method_exists($this->task, 'authorize')) {
            throw_unless(
                $this->task->authorize(),
                new AuthorizationException('You are unauthorized to trigger this action')
            );
        }
    }
}
