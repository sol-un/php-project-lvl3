<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DiDom\Document;
use Carbon\Carbon;

Route::get('/', function (): Illuminate\View\View {
    return view('main');
})->name('main');

Route::get('urls', function (): Illuminate\View\View {
    $urls = DB::table('urls')->get();

    $lastChecks = DB::table('url_checks')
        ->orderBy('created_at')
        ->get()
        ->keyBy('url_id');

    return view('urls.index', compact('urls', 'lastChecks'));
})->name('urls.index');

Route::get('urls/{id}', function ($id): Illuminate\View\View {
    $url = DB::table('urls')->find($id);
    abort_unless($url, 404);

    $checks = DB::table('url_checks')
        ->orderBy('created_at', 'desc')
        ->where('url_id', $id)
        ->get();

    return view('urls.show', compact('url', 'checks'));
})->name('urls.show');

Route::post('urls', function (Request $request): Illuminate\Http\RedirectResponse {
    $url = $request['url']['name'];
    $urlValidator = Validator::make(
        ['url' => $url],
        ['url' => 'required|url|max:255']
    );

    if ($urlValidator->fails()) {
        flash('Некорректный URL')->error();
        return redirect()
            ->route('main')
            ->withErrors($urlValidator);
    }

    $parsedUrl = parse_url($url);
    $name = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

    $url = DB::table('urls')->where('name', $name)->first();
    if ($url === null) {
        $id = DB::table('urls')->insertGetId([
            'name' => $name,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);
    } else {
        flash('Страница уже существует')->info();
        $id = $url->id;
    }

    return redirect()->route('urls.show', ['id' => $id]);
})->name('urls.store');

Route::post('urls/{id}/checks', function ($id): Illuminate\Http\RedirectResponse {
    $url = DB::table('urls')->find($id);
    abort_unless($url, 404);

    $response = Http::get($url->name);
    if ($response->clientError()) {
        flash('Ошибка на стороне клиента')->error();
        return redirect()->route('urls.show', ['id' => $id]);
    }
    if ($response->serverError()) {
        flash('Ошибка на стороне сервера')->error();
        return redirect()->route('urls.show', ['id' => $id]);
    }

    $document = new Document($response->body());
    $header = optional($document->first('h1'))->text();
    $title = optional($document->first('title'))->text();
    $description = optional($document->first('meta[name="description"]'))->attr('content');

    DB::table('url_checks')->insert([
        'url_id' => $id,
        'status_code' => $response->status(),
        'h1' => $header,
        'title' => $title,
        'description' => $description,
        "created_at" => Carbon::now(),
        "updated_at" => Carbon::now(),
    ]);

    return redirect()->route('urls.show', ['id' => $id]);
})->name('urls.check');
