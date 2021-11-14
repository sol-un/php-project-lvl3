<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UrlCheckTest extends TestCase
{
    private string $dummyName;
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        $this->dummyName = 'https://www.example.com';
        $this->id = DB::table('urls')->insertGetId(['name' => $this->dummyName]);
    }

    public function testCheck(): void
    {
        $html = file_get_contents(__DIR__ . "/../fixtures/example.html");

        Http::fake([
            $this->dummyName => Http::response($html),
        ]);

        $response = $this->post(route('urls.check', ['id' => $this->id]));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $data = [
            'url_id' => $this->id,
            'status_code' => 200,
            'h1' => 'Example Header',
            'title' => 'Document',
            'description' => 'Example description',
        ];
        $this->assertDatabaseHas('url_checks', $data);
    }
}
