@extends('consultant_management.email.general')

@section('content')

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Your department has been assigned to participate in above planning process.</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Please click the following link to access:</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;"><a style="color:#4d8af0;" href="{{{ $route }}}">{{{ $route }}}</a></p>

@endsection