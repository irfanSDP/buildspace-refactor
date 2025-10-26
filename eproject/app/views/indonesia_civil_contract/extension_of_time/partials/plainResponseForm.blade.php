{{ Form::open(array('route' => array('indonesiaCivilContract.extensionOfTime.response.plain.submit', $project->id, $eot->id),'method' => 'POST', 'id'=> 'responseForm')) }}
    <fieldset class="border-top">
        <section>
            <h3>{{ trans('extensionOfTime.responseForm') }}</h3>
            <hr/>
        </section>
        <section>
            <strong>{{ trans('extensionOfTime.extensionOfTimeReference') }}:</strong><br>
            <label class="input">
                {{{ $eot->reference }}}
            </label>
        </section>
        <section>
            <label class="label">{{ trans('extensionOfTime.subject') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
                {{ Form::text('subject', Input::old('subject'), array('required' => 'required')) }}
            </label>
            {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('extensionOfTime.response') }}<span class="required">*</span>:</label>
            <label class="input {{{ $errors->has('content') ? 'state-error' : null }}}">
                {{ Form::textArea('content', Input::old('content'), array('required' => 'required', 'rows' => "4", 'class' => 'fill-horizontal')) }}
            </label>
            {{ $errors->first('content', '<em class="invalid">:message</em>') }}
        </section>
        <section>
            <label class="label">{{ trans('general.attachments') }}:</label>

            @include('file_uploads.partials.upload_file_modal')
        </section>
    </fieldset>
    <footer>

        <button type="submit" class="btn btn-primary">{{ trans('forms.reply') }}</button>
    </footer>
{{ Form::close() }}