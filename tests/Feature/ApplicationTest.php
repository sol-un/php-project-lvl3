<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use App\Models\Url;

class ApplicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    public function testMain(): void
    {
        $response = $this->get(route('main'));
        $response->assertOk();
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
    }

    public function testShow(): void
    {
        $url = new Url();
        $url->name = 'https://www.example.com';
        $url->save();

        $response = $this->get(route('urls.show', [$url]));
        $response->assertOk();
    }

    public function testStore(): void
    {
        $data = ['url' => ['name' => 'https://www.example.com']];
        $response = $this->post(route('urls.store'), $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('urls', $data['url']);
    }

    public function testStoreValidationEmptyError(): void
    {
        $data = ['url' => ['name' => '']];
        $response = $this->post(route('urls.store'), $data);
        $response->assertSessionHasErrors();
    }

    public function testCheck(): void
    {
        $name = 'https://www.example.com';
        $status = 200;

        Http::fake([
            $name => Http::response('body', $status),
        ]);

        $url = new Url();
        $url->name = $name;
        $url->save();

        $response = $this->post(route('urls.check', $url));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $data = [
            'url_id' => $url['id'],
            'status_code' => $status
        ];
        $this->assertDatabaseHas('url_checks', $data);
    }
}
