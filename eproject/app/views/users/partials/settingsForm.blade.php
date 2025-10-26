{{ Form::open(array('class' => 'smart-form', 'method' => 'PUT')) }}
    <fieldset>
        <section class="col col-8">
            <label class="label">{{ trans('settings.language') }}:</label>
            {{ Form::select('language_id', $languages, Input::old('language_id') ?? $user->settings->language_id, array('class' => 'select2 fill-horizontal')) }}
        </section>
    </fieldset>
    <footer>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;{{ trans('forms.save') }}</button>
    </footer>
{{ Form::close() }}