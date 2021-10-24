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
});

Route::get('urls', function () {
    $urls = Url::all();
    return view('urls', ['urls' => $urls]);
});

Route::get('urls/{id}', function (Request $request, Response $response, $id) {
    $url = Url::find($id);
    return view('url', ['url' => $url]);
});

Route::post('urls', function (Request $request) {
    $urlValidator = Validator::make(
        ['url' => $request['url']['name']],
        ['url' => 'required|url|max:255']
    );
    if ($urlValidator->fails()) {
        flash('Некорректный URL')->error();

        return redirect('/');
    }

    ['scheme' => $scheme, 'host' => $host] = parse_url($request['url']['name']);
    $name = $scheme . '://' . $host;
    $nameValidator = Validator::make(
        ['name'  => $name],
        ['name' => 'unique:urls']
    );
    if ($nameValidator->fails()) {
        flash('Страница уже существует')->info();

        $url = Url::where('name', $name)->get();
        return redirect()->route('urls', [$url]);
    }

    $url = new Url();
    $url->name = $name;
    $url->save();
    return redirect()->route('urls', [$url]);
});
