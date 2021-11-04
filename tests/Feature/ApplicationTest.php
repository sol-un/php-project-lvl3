<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApplicationTest extends TestCase
{
    private string $dummyName = 'https://www.example.com';

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
        $id = DB::table('urls')->insertGetId(['name' => $this->dummyName]);

        $response = $this->get(route('urls.show', ['id' => $id]));
        $response->assertOk();
    }

    public function testStore(): void
    {
        $data = ['url' => ['name' => $this->dummyName]];
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
        $name = $this->dummyName;
        $status = 200;

        Http::fake([
            $name => Http::response('body', $status),
        ]);

        $id = DB::table('urls')->insertGetId(['name' => $this->dummyName]);

        $response = $this->post(route('urls.check', ['id' => $id]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $data = [
            'url_id' => $id,
            'status_code' => $status
        ];
        $this->assertDatabaseHas('url_checks', $data);
    }
}
