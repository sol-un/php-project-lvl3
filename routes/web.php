<?php

use Illuminate\Support\Collection;
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
    $urls = collect(DB::table('urls')->get())
        ->map(function ($url) {
            $id = $url->id;

            $lastcheck = DB::table('url_checks')
                ->orderBy('created_at', 'desc')
                ->where('url_id', $id)
                ->first();

            $url->last_check_date = optional($lastcheck)->created_at;
            $url->status_code = optional($lastcheck)->status_code;

            return $url;
        });
    return view('urls', compact('urls'));
})->name('urls.index');

Route::get('urls/{id}', function ($id): Illuminate\View\View {
    $url = DB::table('urls')->find($id);

    $checks = DB::table('url_checks')
        ->orderBy('created_at', 'desc')
        ->where('url_id', $id)
        ->get();

    return view('url', compact('url', 'checks'));
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
