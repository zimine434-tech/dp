@extends(auth()->user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.student')

@section('title', $competition->name)

@section('content')
    @include('partials.competition-result-detail', [
        'competition' => $competition,
        'backUrl' => route('competitions.results'),
        'backLabel' => 'Назад к результатам',
    ])
@endsection
