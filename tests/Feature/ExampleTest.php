<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_redirects_to_the_protected_dashboard_entry(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
