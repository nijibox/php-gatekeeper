@extends('register.layout')


@section('content')
    @foreach($messages as $message)
    <p>{{ $message }}</p>
    @endforeach
@endsection
