<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
	<div class="jarviswidget jarviswidget-sortable">
		<header role="heading">
			<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
			<h2>{{ trans('users.updateMyProfile') }}</h2>
		</header>

		<!-- widget div-->
		<div role="content">
			<!-- widget content -->
			<div class="widget-body no-padding">
				{{ Form::model($user, array('class' => 'smart-form', 'method' => 'PUT')) }}
					<fieldset>
						@if ( $user->company )
							<section>
								<label class="label">{{ trans('users.company') }}:</label>
								{{{ $user->company->name }}}
							</section>
						@endif

						<section>
							<label class="label">{{ trans('users.name') }}<span class="required">*</span>:</label>
							<label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
								{{ Form::text('name', Input::old('name'), array('required' => 'required')) }}
							</label>
							{{ $errors->first('name', '<em class="invalid">:message</em>') }}
						</section>

						<section>
							<label class="label">{{ trans('users.contactNumber') }}<span class="required">*</span>:</label>
							<label class="input {{{ $errors->has('contact_number') ? 'state-error' : null }}}">
								{{ Form::text('contact_number', Input::old('contact_number'), array('required' => 'required')) }}
							</label>
							{{ $errors->first('contact_number', '<em class="invalid">:message</em>') }}
						</section>

						<section>
							<label class="label">{{ trans('users.email') }}:</label>
							{{{ $user->email }}}
						</section>

						<hr/>

						@include('users.partials.passwordUpdateFields')
					</fieldset>

					<footer>
						<button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
					</footer>
				{{ Form::close() }}
			</div>
			<!-- end widget content -->

		</div>
		<!-- end widget div -->
	</div>
</article>