@extends('consultant_management.email.general')

@section('content')
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>RFP:</strong> {{{$vendorCategoryName}}}</p>
@if(isset($callingRfpDate))
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{ trans('consultantManagement.callingRfpDate') }}:</strong> {{{ $callingRfpDate }}}</p>
@endif
@if(isset($closingRfpDate))
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{ trans('consultantManagement.closingRfpDate') }}:</strong> {{{ $closingRfpDate }}}</p>
@endif

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">The {{{$moduleName}}} has been approved.</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Please click the following link to access:</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><a style="color:#4d8af0;" href="{{{ $route }}}">{{{ $route }}}</a></p>

@endsection