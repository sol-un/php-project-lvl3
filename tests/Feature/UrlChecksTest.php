<?php

namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

function getFixturePath(string $filename): string
{
    return implode('/', [__DIR__, '..', 'fixtures', $filename]);
}
class UrlChecksTest extends TestCase
{
    private string $dummyName;
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dummyName = 'https://www.example.com';
        $this->id = DB::table('urls')->insertGetId(['name' => $this->dummyName]);
    }

    public function testCheck(): void
    {
        $fixturePath = getFixturePath('example.html');
        $html = file_get_contents($fixturePath);

        if ($html === false) {
            throw new Exception("File not found at {$fixturePath}");
        }

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
            'created_at' => Carbon::now()
        ];
        $this->assertDatabaseHas('url_checks', $data);
    }
}
