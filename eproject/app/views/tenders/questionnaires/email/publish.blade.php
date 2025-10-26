@extends('tenders.questionnaires.email.general')

@section('content')
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{{ trans('tenders.contractor') }}}:</strong> {{{$companyName}}}</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><strong>{{{ trans('companies.referenceNumber') }}}:</strong> {{{$companyReferenceNumber}}}</p>

@if(!isset($forContractor) or !$forContractor)
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">A questionnaire has been {{{$actionName}}} by {{{$creator}}}.</p>
@else
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">A questionnaire has been {{{$actionName}}}.</p>
@endif
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Please click the following link to access:</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><a style="color:#4d8af0;" href="{{{ $route }}}">{{{ $route }}}</a></p>

@endsection