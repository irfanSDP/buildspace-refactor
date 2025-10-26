@extends('auth.auth_master_layout')

@section('content')
	<div class="wrapper">
		<div class="main content clearfix">
			<div class="banner">
				<h1>{{{ $myCompanyProfile->name }}}</h1>
				<h2 class="hidden-small">
					{{ trans('auth.accountRecoveryLinkHelp') }}
				</h2>
			</div>

			<div class="block signin-block clearfix">
				<img class="buildspace-img" src="{{ $themeSettings->img_path }}" alt="{{ $themeSettings->img_title }}">

				@if (Session::get('error'))
					<div class="alert alert-danger" style="margin-bottom: 10px; color: red; text-align: center;">
						<i class="fa-fw fa fa-times"></i>
						{{{ Session::get('error') }}}
					</div>
				@endif

				@if (Session::get('notice'))
					<div class="alert alert-warning" style="margin-bottom: 10px; color: green; text-align: center;">
						<i class="fa-fw fa fa-exclamation-triangle"></i>
						{{{ Session::get('notice') }}}
					</div>
				@endif

				{{ Form::open(array('id' => 'login-form')) }}
					<label class="hidden-label" for="Email">{{ trans('users.email') }}</label>
					<input class="form-control" placeholder="{{{ trans('confide::confide.e_mail') }}}" type="text" name="email" id="email" value="{{{ Input::old('email') }}}">

					<input name="forgot_password" class="rc-button-submit" type="submit" value="{{ trans('auth.forgotPassword') }}">
				{{ Form::close() }}

				<div class="ribbon-banner">&nbsp;</div>
			</div>
		</div>

		@include('auth.partials.footer')
	</div>
@endsection