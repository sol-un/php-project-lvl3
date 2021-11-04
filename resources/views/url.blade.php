@extends('layouts.app')

@section('content')
<div class="container-lg">
  <h1 class="mt-4 mb-3">Сайт: {{ $url->name }}</h1>
  <div class="table-responsive">
    <table class="table table-bordered table-hover text-wrap">
      <tr>
        <td>ID</td>
        <td>{{ $url->id }}</td>
      </tr>
      <tr>
        <td>Имя</td>
        <td>{{ $url->name }}</td>
      </tr>
      <tr>
        <td>Дата создания</td>
        <td>{{ $url->created_at }}</td>
      </tr>
      <tr>
        <td>Дата обновления</td>
        <td>{{ $url->updated_at }}</td>
      </tr>
    </table>
  </div>
  <h2 class="mt-5 mb-3">Проверки</h2>
  <form method="post" action="{{route('urls.check', ['id' => $url->id])}}">
    @csrf
    <input type="submit" class="btn btn-primary" value="Запустить проверку">
  </form>
  <table class="table table-bordered table-hover text-wrap mt-3">
    <tr>
      <th>ID</th>
      <th>Код ответа</th>
      <th>h1</th>
      <th>title</th>
      <th>description</th>
      <th>Дата создания</th>
    </tr>
    @foreach($checks as $check)
    <tr>
      <td>{{ $check->id }}</td>
      <td>{{ $check->status_code }}</td>
      <td>{{ $check->h1 }}</td>
      <td>{{ $check->title }}</td>
      <td>{{ $check->description }}</td>
      <td>{{ $check->created_at }}</td>
    </tr>
    @endforeach
  </table>
</div>
@endsection