<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DiDom\Document;

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

    ['scheme' => $scheme, 'host' => $host] = parse_url($url);
    $name = $scheme . '://' . $host;
    $nameValidator = Validator::make(
        ['name'  => $name],
        ['name' => 'unique:urls']
    );

    if ($nameValidator->fails()) {
        /** @var mixed $url */
        $url = DB::table('urls')->where('name', $name)->first();
        flash('Страница уже существует')->info();
        return redirect()->route('urls.show', ['id' => $url->id]);
    }

    $id = DB::table('urls')->insertGetId([
        'name' => $name,
        "created_at" =>  \Carbon\Carbon::now(),
        "updated_at" => \Carbon\Carbon::now(),
    ]);

    return redirect()->route('urls.show', ['id' => $id]);
})->name('urls.store');

Route::post('urls/{id}/checks', function ($id): Illuminate\Http\RedirectResponse {
    $url = DB::table('urls')->find($id);
    $response = Http::get($url->name);

    if ($response->failed()) {
        flash('Не удалось установить соединение')->error();
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
        "created_at" =>  \Carbon\Carbon::now(),
        "updated_at" => \Carbon\Carbon::now(),
    ]);

    return redirect()->route('urls.show', ['id' => $id]);
})->name('urls.check');
