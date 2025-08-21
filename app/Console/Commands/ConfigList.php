<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConfigList extends Command
{
    protected $signature = 'config:list {key}';
    protected $description = 'Mostra os valores de uma config';

    public function handle()
    {
        $key = $this->argument('key');
        $value = config($key);

        $this->line(print_r($value, true));
    }
}
