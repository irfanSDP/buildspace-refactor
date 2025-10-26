@extends('auth.auth_master_layout')

@section('content')
	<div class="wrapper">
		<div class="main content clearfix">
			<div class="banner">
				<h1>{{{ $myCompanyProfile->name }}}</h1>
			</div>

			<div class="block signin-block clearfix">
				<img class="buildspace-img" src="{{ asset('img/buildspace-login-logo.png') }}">

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
					<label class="hidden-label" for="Email">Email</label>
					<input type="email" name="email" id="email" value="{{{ Input::old('email') }}}" autofocus="autofocus"
						   placeholder="E-Mail Address">

					<label class="hidden-label" for="Passwd">{{ trans('auth.password') }}</label>
					<input type="password" name="password" id="Passwd" placeholder="Password">

					<input id="signIn" name="signIn" class="rc-button-submit" type="submit"
						   value="{{ trans('confide::confide.login.submit') }}">
					<input type="hidden" name="remember" value="0">
				{{ Form::close() }}

				<div style="text-align: center;">
					<a href="{{{ route('users.forgotPassword') }}}">{{ trans('auth.forgotPassword?') }}</a>
				</div>

				<div class="ribbon-banner">&nbsp;</div>
			</div>

			<div class="second-block">
				<p class="external-links">
					<?php echo link_to('//forum.buildspace.my', 'Forum & Tutorials', array( 'id' => 'link-forum', 'target' => '_blank' ))?>
					::
					<?php echo link_to('//eepurl.com/LhJNn', 'Subscribe to our newsletter', array( 'id' => 'link-newsletter', 'target' => '_blank' ))?>
				</p>
			</div>
		</div>

		@include('auth.partials.footer')
	</div>
@endsection