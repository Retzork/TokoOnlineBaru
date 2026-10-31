<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL redirects to the public storefront.
     */
    public function test_the_application_redirects_to_the_storefront(): void
    {
        $this->get('/')->assertRedirect(route('beranda'));
    }
}
