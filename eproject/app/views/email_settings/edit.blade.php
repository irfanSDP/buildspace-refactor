@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ trans('email.emailSettings') }}</li>
    </ol>
    @endsection
    <?php use PCK\EmailSettings\EmailSetting; ?>
    <?php use PCK\EmailSettings\EmailReminderSetting; ?>
    @section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="far fa-envelope"></i> {{ trans('email.emailSettings') }}
            </h1>
        </div>
    </div>
    <div class="jarviswidget">
        <header>
            <span class="widget-icon"> <i class="far fa-list-alt"></i> </span>
            <h2>{{ trans('email.emailTemplateSettings') }}</h2>
        </header>
        <div>
            <div class="widget-body">
                {{ Form::open(['route' => ['email.settings.update'], 'class' => 'smart-form', 'method' => 'POST', 'enctype' => "multipart/form-data"]) }}
                <div class="row">
                    <section class="col col-xs-12 col-md-4 col-lg-4" style="height:260px;">
                        @if(strlen($emailSettings->footer_logo_image) > 0)
                        <img style="border: 1px solid #ccc;" src="{{{ asset(EmailSetting::LOGO_FILE_DIRECTORY.DIRECTORY_SEPARATOR.$emailSettings->footer_logo_image) }}}" class="logo">
                        @else
                        <div style="width:100%; height: 150px;">
                            <div class="alert text-middle text-center alert-warning">
                                <i class="fa-fw fa fa-ban"></i> No Footer Logo Image
                            </div>
                        </div>
                        @endif
                    </section>
                    <section class="col col-xs-12 col-md-8 col-lg-8">
                        <label class="label">Footer Logo</label>
                        <label class="input {{{ $errors->has('footer_logo_image') ? 'state-error' : null }}}">
                            {{ Form::file('file',['id' => 'fileToUpload', 'name'=>'footer_logo_image', 'accept'=>'.jpeg, .png, .jpg, .gif, .svg', 'required'=>'required'])}}
                        </label>
                        {{ $errors->first('footer_logo_image', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-2 col-lg-2">
                        <label class="label">Footer Logo Position <span class="required">*</span></label>
                        <label class="select">
                            <select name="company_logo_alignment_identifier" class="select2">
                                @foreach(EmailSetting::getCompanyLogoAlignmentDropdownSelection() as $identifier => $description)
                                    <?php $selected = ($emailSettings->company_logo_alignment_identifier == $identifier) ? 'selected' : null; ?>
                                    <option value="{{ $identifier }}" {{ $selected }}>{{ $description }}</option>
                                @endforeach
                            </select>
                        </label>
                    </section>
                    <section class="col col-xs-12 col-md-2 col-lg-2">
                        <?php $resizeImage = ($emailSettings->resize_footer_image); ?>
                        <label class="label">Resize Footer Logo:</label>
                        <label class="input {{{ $errors->has('resize_footer_image') ? 'state-error' : null }}}">
                            <select class="select2 fill-horizontal" name="resize_footer_image" id="resize_footer_image-select">
                                <option value="0" @if(Input::old('resize_footer_image', $resizeImage)) selected @endif>{{{ trans('general.no') }}}</option>
                                <option value="1" @if(Input::old('resize_footer_image', $resizeImage)) selected @endif>{{{ trans('general.yes') }}}</option>
                            </select>
                        </label>
                        {{ $errors->first('resize_footer_image', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-8 col-lg-8"></section>
                    <section class="footer_logo_size-container col col-xs-12 col-md-2 col-lg-2" style="@if(!Input::old('resize_footer_image', $resizeImage)) display:none; @endif">
                        <label class="label">Footer Logo Width (px) <span class="required">*</span>:</label>
                        <label class="input {{{ $errors->has('footer_logo_width') ? 'state-error' : null }}}">
                            {{ Form::number('footer_logo_width', Input::old('footer_logo_width', $emailSettings->footer_logo_width), ['autofocus' => 'autofocus']) }}
                        </label>
                        {{ $errors->first('footer_logo_width', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="footer_logo_size-container col col-xs-12 col-md-2 col-lg-2" style="@if(!Input::old('resize_footer_image', $resizeImage)) display:none; @endif">
                        <label class="label">Footer Logo Height (px) <span class="required">*</span>:</label>
                        <label class="input {{{ $errors->has('footer_logo_height') ? 'state-error' : null }}}">
                            {{ Form::number('footer_logo_height', Input::old('footer_logo_height', $emailSettings->footer_logo_height), ['autofocus' => 'autofocus']) }}
                        </label>
                        {{ $errors->first('footer_logo_height', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
                <footer>
                    {{ HTML::decode(link_to_route('email.settings.delete.footer.logo', '<i class="fa fa-trash"></i> Remove Footer Logo', [], ['data-id'=>$emailSettings->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                </footer>
            {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="jarviswidget">
        <header>
            <span class="widget-icon"> <i class="far fa-bell"></i> </span>
            <h2>{{ trans('email.emailReminderSettings') }}</h2>
        </header>
        <div>
            <div class="widget-body">
                {{ Form::open(['route' => ['email.reminder.settings.update'], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-4">
                            <label class="label">{{ trans('email.tenderReminderEmailBeforeClosingDate', ['value' => EmailReminderSetting::getValue('tender_reminder_before_closing_date_value'), 'unit' => EmailReminderSetting::getUnitDescription(EmailReminderSetting::getValue('tender_reminder_before_closing_date_unit'))]) }} <span class="required">*</span></label>
                            <label class="select">
                                <label class="input {{ $errors->has('valid_period_of_temp_login_acc_to_unreg_vendor_value') ? 'state-error' : null }}">
                                    <input type="tex" name="tender_reminder_before_closing_date_value" value="{{ Input::old('tender_reminder_before_closing_date_value') ?? EmailReminderSetting::getValue('tender_reminder_before_closing_date_value') }}">
                                </label>
                                {{ $errors->first('tender_reminder_before_closing_date_value', '<em class="invalid">:message</em>') }}
                            </label>
                        </section>
                        <section class="col col-2">
                            <label class="label">{{ trans('general.unit') }} <span class="required">*</span></label>
                            <label class="select">
                                <select name="tender_reminder_before_closing_date_unit" class="select2">
                                    <?php $selectedValue = Input::old('tender_reminder_before_closing_date_unit') ?? EmailReminderSetting::getValue('tender_reminder_before_closing_date_unit'); ?>
                                    @foreach(EmailReminderSetting::getUnitDescription() as $value => $description)
                                    <option value="{{ $value }}" @if($value == $selectedValue) selected @endif>{{ $description }}</option>
                                    @endforeach
                                </select>
                            </label>
                            {{ $errors->first('tender_reminder_before_closing_date_unit', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#resize_footer_image-select').on('select2:select', function(e){
        e.preventDefault();
        if(parseInt($('#resize_footer_image-select').val()) != 0){
            $('.footer_logo_size-container').show();
        }else{
            $('.footer_logo_size-container').hide();
        }
    });
});
</script>
@endsection