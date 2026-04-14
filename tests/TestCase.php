<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Exécuter les migrations pour chaque test
        $this->artisan('migrate');
    }

    protected function tearDown(): void
    {
        // Nettoyer la base de données après chaque test
        $this->artisan('migrate:rollback');
        
        parent::tearDown();
    }
}
