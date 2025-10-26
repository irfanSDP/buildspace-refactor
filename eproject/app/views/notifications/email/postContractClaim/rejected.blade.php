@extends('notifications.email.base')

@section('content')
    @if($parentProject)
        <p>Main Project: {{{ $parentProject['title'] }}}</p>
    @endif

    <p>Project: {{{ $project['title'] }}}</p>

    <p>
        <strong>{{{ $moduleName }}}</strong>: {{{ $itemDescription }}}
    </p>

    <p>
        This {{{ $moduleName }}} has been rejected.
    </p>
@endsection