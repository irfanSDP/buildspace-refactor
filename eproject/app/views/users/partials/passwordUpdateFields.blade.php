<section style="margin-top: 16px;">
	<h3 style="color: green;">{{ trans('users.changePassword') }}</h3>

	<p>{{ trans('users.pleaseFillUp') }}.</p>

	<p>{{ trans('users.yourPasswordMust') }}:</p>

	<div style="padding-left: 25px;">
		<ul>
			<li>{{ trans('users.minimumSixChars') }}</li>
			@if(getenv('CUMBERSOME_PASSWORDS'))
				<li>{{ trans('usres.includeUpperLowerChars') }}</li>
				<li>{{ trans('usres.includeNumbers') }}</li>
				<li>{{ trans('usres.includeSpecialChars') }}</li>
			@endif
		</ul>
	</div>

</section>

<section>
	<label class="label">{{ trans('users.password') }}:</label>
	<label class="input {{{ $errors->has('password') ? 'state-error' : null }}}">
		{{ Form::password('password') }}
	</label>
	{{ $errors->first('password', '<em class="invalid">:message</em>') }}
</section>

<section>
	<label class="label">{{ trans('users.passwordConfirmation') }}:</label>
	<label class="input {{{ $errors->has('password_confirmation') ? 'state-error' : null }}}">
		{{ Form::password('password_confirmation') }}
	</label>
	{{ $errors->first('password_confirmation', '<em class="invalid">:message</em>') }}
</section>