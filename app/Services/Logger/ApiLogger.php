<?php

namespace App\Services\Logger;

class ApiLogger extends BaseLogger
{
    protected function write(array $data): void
    {
        // TODO: implement when api_logs table is created
        // ApiLog::create([...]);
    }
}
