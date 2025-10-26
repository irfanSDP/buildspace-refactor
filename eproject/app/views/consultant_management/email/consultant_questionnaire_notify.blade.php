@extends('consultant_management.email.general')

@section('content')

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
Company {{{ $companyName }}} has replied questionnaires for RFP {{{ $vendorCategoryName }}}.
</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>Submitter:</strong> {{{$submitter}}}</p>

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Please click the following link to access:</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><a style="color:#4d8af0;" href="{{{ $route }}}">{{{ $route }}}</a></p>

@endsection