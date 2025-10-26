@extends('notifications.email.digitalStar.template')

@section('message')
    <p>
        @if (empty($projectTitle))
            {{ trans('digitalStar/email.companyEvaluationFormHasBeenRejectedBy') }}
        @else
            {{ trans('digitalStar/email.projectEvaluationFormHasBeenRejectedBy') }}
        @endif
        {{ ! empty($actionBy) ? ' '.$actionBy : '' }}
    </p>
@endsection