<?php

namespace App\Console\Commands;

use App\Application\Licenses\Services\CheckLicenseExpirationsService;

use Illuminate\Console\Command;

final class CheckLicenseExpirationsCommand extends Command
{

    protected $signature = 'licenses:check-expiration';

    public function handle(CheckLicenseExpirationsService $service)
    {
        $service->execute();
    }

}