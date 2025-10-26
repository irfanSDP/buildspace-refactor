@extends('notifications.email.digitalStar.template')

@section('message')
    <p>
        @if (empty($projectTitle))
            {{ trans('digitalStar/email.companyEvaluationHasBeenSubmittedBy') }}
        @else
            {{ trans('digitalStar/email.projectEvaluationHasBeenSubmittedBy') }}
        @endif
        {{ ! empty($actionBy) ? ' '.$actionBy : '' }}
    </p>
@endsection