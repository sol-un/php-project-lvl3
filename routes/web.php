<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Url;
use App\Models\UrlCheck;
use DiDom\Document;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (): Illuminate\View\View {
    return view('main');
})->name('main');

Route::get('urls', function (): Illuminate\View\View {
    $urls = collect(Url::all())
        ->map(function ($url): App\Models\Url {
            $id = $url['id'];
            $lastcheck = UrlCheck::orderBy('created_at', 'desc')
                ->where('url_id', $id)
                ->first();
            $url['last_check_date'] = optional($lastcheck)->created_at;
            $url['status_code'] = optional($lastcheck)->status_code;
            return $url;
        });
    return view('urls', compact('urls'));
})->name('urls.index');

Route::get('urls/{id}', function ($id): Illuminate\View\View {
    $url = Url::findOrFail($id);
    $checks = UrlCheck::orderBy('created_at', 'desc')
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
        return redirect('/')->withErrors($urlValidator);
    }

    ['scheme' => $scheme, 'host' => $host] = parse_url($url);
    $name = $scheme . '://' . $host;
    $nameValidator = Validator::make(
        ['name'  => $name],
        ['name' => 'unique:urls']
    );
    if ($nameValidator->fails()) {
        $url = Url::where('name', $name)->first();
        flash('Страница уже существует')->info();
        return redirect()->route('urls.show', [$url]);
    }

    $url = new Url();
    $url->name = $name;
    $url->save();
    return redirect()->route('urls.show', [$url]);
})->name('urls.store');

Route::post('urls/{id}/checks', function ($id): Illuminate\Http\RedirectResponse {
    /** @var \App\Models\Url $url */
    $url = Url::findOrFail($id);
    $response = Http::get($url->name);

    $document = new Document($response->body());
    $header = optional($document->first('h1'))->text();
    $keywords = optional($document->first('meta[name="keywords"]'))->attr('content');
    $description = optional($document->first('meta[name="description"]'))->attr('content');

    $urlcheck = new UrlCheck();
    $urlcheck->url_id = $id;
    $urlcheck->status_code = $response->status();
    $urlcheck->h1 = $header;
    $urlcheck->keywords = $keywords;
    $urlcheck->description = $description;
    $urlcheck->save();
    return redirect()->route('urls.show', ['id' => $id]);
})->name('urls.check');
