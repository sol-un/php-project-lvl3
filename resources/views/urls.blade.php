@extends('layouts.app')

@section('content')
<div class="container-lg">
  <h1 class="mt-5 mb-3">Сайты</h1>
  <div class="table-responsive">
    <table class="table table-bordered table-hover text-wrap">
      <tr>
        <th>ID</th>
        <th>Имя</th>
        <th>Последняя проверка</th>
        <th>Код ответа</th>
      </tr>
      @foreach($urls as $url)
      <tr>
        <td>{{ $url->id }}</td>
        <td><a href="{{ route('urls.show', ['id' => $url->id]) }}">{{ $url->name }}</a></td>
        <td>{{ $url->last_check_date }}</td>
        <td>{{ $url->status_code }}</td>
      </tr>
      @endforeach
    </table>
  </div>
</div>
@endsection