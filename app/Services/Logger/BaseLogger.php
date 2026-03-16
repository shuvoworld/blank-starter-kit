<?php

namespace App\Services\Logger;

use Illuminate\Support\Facades\Log;

abstract class BaseLogger
{
    abstract protected function write(array $data): void;

    public function log(array $data): void
    {
        try {
            $this->write($data);
        } catch (\Throwable $e) {
            Log::error(static::class.' failed to write log: '.$e->getMessage());
        }
    }
}
