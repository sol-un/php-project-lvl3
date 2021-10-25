@extends('layouts.app')

@section('content')
<div class="container-lg">
  <h1 class="mt-5 mb-3">Сайты</h1>
  <div class="table-responsive">
    <table class="table table-bordered table-hover text-nowrap">
      <tr>
        <th>ID</th>
        <th>Имя</th>
      </tr>
      @foreach($urls as $url)
      <tr>
        <td>{{ $url['id'] }}</td>
        <td><a href="{{ route('urls.show', [$url]) }}">{{ $url['name'] }}</a></td>
      </tr>
      @endforeach
    </table>
  </div>
</div>
@endsection