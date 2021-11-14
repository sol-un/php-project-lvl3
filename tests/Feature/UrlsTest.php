<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class UrlsTest extends TestCase
{
    private string $dummyName;
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dummyName = 'https://www.example.com';
        $this->id = DB::table('urls')->insertGetId(['name' => $this->dummyName]);
        DB::table('url_checks')->insert(['url_id' => $this->id]);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('urls.index'));
        $response->assertOk();
        $response->assertSee($this->dummyName);
    }

    public function testShow(): void
    {
        $response = $this->get(route('urls.show', ['id' => $this->id]));
        $response->assertOk();
        $response->assertSee($this->dummyName);
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
}
