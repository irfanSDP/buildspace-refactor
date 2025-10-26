@extends('notifications.email.base')

@section('content')
    @if($parentProject)
        <p>Main Project: {{{ $parentProject['title'] }}}</p>
    @endif

    <p>Project: {{{ $project['title'] }}}</p>

    <p>
        <strong>{{{ $moduleName }}}</strong>
    </p>

    <p>
        @if($customText)
            {{{ $customText }}}
        @else
            A {{{ $moduleName }}} has been approved.
        @endif
    </p>
@endsection