@extends('notifications.email.digitalStar.template')

@section('message')
    <p>
        {{ trans('digitalStar/email.youHaveBeenAssignedAsEvaluator') }}
    </p>
@endsection