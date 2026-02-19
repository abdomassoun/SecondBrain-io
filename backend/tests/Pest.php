<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest Global Test Configuration
|--------------------------------------------------------------------------
|
| Apply Laravel testing traits to the appropriate layers:
| - Domain: pure, no Laravel helpers
| - Application & Presentation: use RefreshDatabase for DB tests
|
*/

// Apply RefreshDatabase for Application & Presentation layers
uses(RefreshDatabase::class)->in('Application', 'Presentation');

// Domain tests remain framework-free

pest()->extend(TestCase::class)->in('Feature');


