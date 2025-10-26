@extends('consultant_management.email.general')

@section('content')
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>RFP:</strong> {{{$vendorCategoryName}}}</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>Consultant:</strong> {{{$companyName}}}</p>

@if(!isset($forConsultant) or !$forConsultant)
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">A questionnaire has been {{{$actionName}}} by {{{$creator}}}.</p>
@else
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">A questionnaire has been {{{$actionName}}}.</p>
@endif
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Please click the following link to access:</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><a style="color:#4d8af0;" href="{{{ $route }}}">{{{ $route }}}</a></p>

@endsection