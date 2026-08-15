<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Console\Command;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Ensure all Artisan commands have container instance bound
        $app->resolving(Command::class, function (Command $command, $app) {
            $command->setLaravel($app);
        });

        return $app;
    }
}
