<?php

namespace App\Services\BaseService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseService
{
    protected string $modelClass;

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $this->beforeCreate($data);
            $model = ($this->modelClass)::create($data);
            $this->afterCreate($model, $data);
            return $model;
        });
    }

    public function update(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $this->beforeUpdate($record, $data);
            $record->update($data);
            $this->afterUpdate($record, $data);
            return $record;
        });
    }

    protected function beforeCreate(array $data): void {}
    protected function afterCreate(Model $model, array $data): void {}
    protected function beforeUpdate(Model $record, array $data): void {}
    protected function afterUpdate(Model $record, array $data): void {}
}