<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Url;

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

Route::get('/', function () {
    return view('main');
})->name('main');

Route::get('urls', function () {
    $urls = Url::all();
    return view('urls', ['urls' => $urls]);
})->name('urls.index');

Route::get('urls/{id}', function (Request $request, Response $response, $id) {
    $url = Url::findOrFail($id);
    return view('url', ['url' => $url]);
})->name('urls.show');

Route::post('urls', function (Request $request) {
    $url = $request['url']['name'];
    $urlValidator = Validator::make(
        ['url' => $url],
        ['url' => 'required|url|max:255']
    );
    if ($urlValidator->fails()) {
        flash('Некорректный URL')->error();
        return redirect('/');
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
