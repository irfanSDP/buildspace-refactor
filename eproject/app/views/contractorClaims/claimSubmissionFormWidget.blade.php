<div class="jarviswidget">
    <header>
        <h2 class="font-md">{{{ trans('finance.submitClaims') }}}</h2>
    </header>

    <div class="widget-body">

        {{ Form::open(array('class' => 'smart-form', 'id' => 'claim-submission-form', 'files' => true)) }}
            <fieldset>
                <div class="row">
                    <div class="col col-xs-12 col-md-3 col-lg-4">
                        <section>
                            <h3>{{ trans('finance.submitClaims') }}</h3>
                        </section>

                        <section>
                            <label class="label">{{ trans('finance.claimsFile') }} (.{{{ \PCK\Helpers\Files::EXTENSION_CLAIM }}}):</label>
                            <label class="input {{{ $errors->has('claims') ? 'state-error' : null }}}">
                                {{ Form::file('claims', array('style' => 'height:100%')) }}
                            </label>
                            {{ $errors->first('claims', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <div class="col col-xs-12 col-md-1 col-lg-1">
                    </div>
                    <div class="col col-xs-12 col-md-8 col-lg-7">
                        <section>
                            <label class="label">{{{ trans('forms.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal')
                        </section>
                    </div>
                </div>
            </fieldset>

            <footer>
                <button type="submit" class="btn btn-primary" data-intercept="confirmation" data-confirmation-message="{{ trans('finance.claimSubmissionConfirmation') }}">
                    <i class="fa fa-upload"></i>
                    {{ trans('finance.submitClaims') }}
                </button>
            </footer>
        {{ Form::close() }}
    </div>
</div>