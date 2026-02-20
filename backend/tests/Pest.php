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
| - Application & Presentation: use RefreshDatabase for DB tests|
*/

// Application layer tests (Unit tests that need DB)
uses(RefreshDatabase::class)->in('Application');

// Feature tests use both TestCase and RefreshDatabase  
uses(TestCase::class, RefreshDatabase::class)->in('Feature');

// Domain tests remain framework-free


