@extends('notifications.email.base')

@section('content')
    <p>Company Name : {{{ $companyName }}}</p>
    <p>Project : {{{ $projectTitle }}}</p>
    <p>Tender : {{{ $tender }}}</p>
    <p>Tender Closing Date : {{{ $dateOfClosingTender }}}</p>
    <p>The tender will be closed soon. Please finalize all submissions before closing date.</p>
@endsection