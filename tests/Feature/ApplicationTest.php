<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function testMain(): void
    {
        $response = $this->get(route('main'));
        $response->assertOk();
    }
}
