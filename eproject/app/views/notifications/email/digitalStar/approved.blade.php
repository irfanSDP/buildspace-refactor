@extends('notifications.email.digitalStar.template')

@section('message')
    <p>
        @if (empty($projectTitle))
            {{ trans('digitalStar/email.companyEvaluationFormHasBeenApprovedBy') }}
        @else
            {{ trans('digitalStar/email.projectEvaluationFormHasBeenApprovedBy') }}
        @endif
        {{ ! empty($actionBy) ? ' '.$actionBy : '' }}
    </p>
@endsection