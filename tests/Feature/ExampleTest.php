<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_dashboard_renders_without_requiring_frontend_build_for_api_tests(): void
    {
        $this->withoutVite()->get('/')->assertOk()
            ->assertSee('Tim kurir Anda.')
            ->assertSee('Tambah kurir')
            ->assertSee('courier-form');
    }
}
