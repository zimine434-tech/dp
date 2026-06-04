@extends('layouts.guest')

@section('title', $competition->name)

@section('content')
    @include('partials.competition-result-detail', [
        'competition' => $competition,
        'backUrl' => route('guest.results'),
        'backLabel' => 'Назад к результатам',
    ])
@endsection
