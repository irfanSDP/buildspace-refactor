<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

            <h2>{{{ $pageTitle }}}</h2>
        </header>

        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                {{ Form::open(array('class' => 'smart-form', 'method' => (isset($type)) ? 'PUT' : 'POST')) }}
                <fieldset>
                    <section>
                        <label class="label">{{{ trans('users.name') }}}<span class="required">*</span>:</label>
                        <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                            {{ Form::text('name', (isset($user)) ? $user->name : Input::old('name'), array('required' => 'required', 'autofocus')) }}
                        </label>
                        {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                    </section>

                    <section>
                        <label class="label">{{{ trans('users.designation') }}}:</label>
                        <label class="input">
                            {{ Form::text('designation', (isset($user)) ? $user->designation : Input::old('designation'), array('autofocus')) }}
                        </label>
                        {{ $errors->first('designation', '<em class="invalid">:message</em>') }}
                    </section>

                    <section>
                        <label class="label">{{{ trans('users.contactNumber') }}}<span class="required">*</span>:</label>
                        <label class="input {{{ $errors->has('contact_number') ? 'state-error' : null }}}">
                            {{ Form::text('contact_number', (isset($user)) ? $user->contact_number : Input::old('contact_number'), array('required' => 'required')) }}
                        </label>
                        {{ $errors->first('contact_number', '<em class="invalid">:message</em>') }}
                    </section>

                    @if (! isset($type))
                        <section>
                            <label class="label">{{{ trans('users.email') }}}<span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('email') ? 'state-error' : null }}}">
                                {{ Form::email('email', (isset($user)) ? $user->email : Input::old('email'), array('required' => 'required')) }}
                            </label>
                            {{ $errors->first('email', '<em class="invalid">:message</em>') }}
                        </section>
                    @else
                        <section>
                            <label class="label">{{{ trans('users.email') }}}<span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('email') ? 'state-error' : null }}}">
                                {{ Form::email('email', (isset($user)) ? $user->email : Input::old('email'), array('required' => 'required', 'disabled' => 'disabled')) }}
                            </label>
                            {{ $errors->first('email', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif

                    @if ($currentUser->isSuperAdmin())
                        <section>
                            <label class="checkbox {{{ $errors->has('account_blocked_status') ? 'state-error' : null }}}">
                                {{ Form::checkbox('account_blocked_status', true, (isset($user)) ? $user->account_blocked_status : Input::old('account_blocked_status'), array('id' => 'isUserBlockCheckbox')) }}
                                <i></i>{{{ trans('users.blockAccount') }}}
                            </label>
                            {{ $errors->first('account_blocked_status', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif

                    @if ($currentUser->isSuperAdmin() || $currentUser->isGroupAdmin())
                        <section>
                            <label class="checkbox {{{ $errors->has('is_admin') ? 'state-error' : null }}}">
                                {{ Form::checkbox('is_admin', true, (isset($user)) ? $user->is_admin : Input::old('is_admin')) }}
                                <i></i>{{{ trans('users.isAdmin') }}}
                            </label>
                            {{ $errors->first('is_admin', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif

                    @if ($currentUser->isSuperAdmin())
                        <section>
                            <label class="checkbox {{{ $errors->has('allow_access_to_buildspace') ? 'state-error' : null }}}">
                                {{ Form::checkbox('allow_access_to_buildspace', true, (isset($user)) ? $user->allow_access_to_buildspace : (Input::old('allow_access_to_buildspace') ?? $company->giveDefaultAccessToBuildSpace()))}}
                                <i></i>{{{ trans('users.accessToBuildSpacePro') }}}
                            </label>
                            {{ $errors->first('allow_access_to_buildspace', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif

                    @if ($currentUser->isSuperAdmin())
                        <section>
                            <label class="checkbox {{{ $errors->has('allow_access_to_gp') ? 'state-error' : null }}}">
                                {{ Form::checkbox('allow_access_to_gp', true, (isset($user)) ? $user->allow_access_to_gp  : (Input::old('allow_access_to_gp') ?? false))}}
                                <i></i>{{{ trans('users.accessToGeneralProcurement') }}}
                            </label>
                            {{ $errors->first('allow_access_to_gp', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif

                    @if ($currentUser->isSuperAdmin())
                        <section>
                            <label class="checkbox {{{ $errors->has('is_gp_admin') ? 'state-error' : null }}}">
                                {{ Form::checkbox('is_gp_admin', true, (isset($user)) ? $user->is_gp_admin  : (Input::old('is_gp_admin') ?? false))}}
                                <i></i>{{{ trans('users.adminAccessToGeneralProcurement') }}}
                            </label>
                            {{ $errors->first('is_gp_admin', '<em class="invalid">:message</em>') }}
                        </section>
                    @endif
                </fieldset>

                <footer>
                    <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
                </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->
    </div>
</article>