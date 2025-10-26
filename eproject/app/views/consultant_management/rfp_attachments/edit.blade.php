@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
        @if(!isset($rfpAttachmentSetting))
        <li>{{ link_to_route('consultant.management.rfp.attachment.settings.index', trans('general.attachmentSettings'), [$vendorCategoryRfp->id]) }}</li>
        <li>{{{ trans('forms.add') }}} {{{ trans('general.attachmentSettings') }}}</li>
        @else
        <li>{{ link_to_route('consultant.management.rfp.attachment.settings.show', $rfpAttachmentSetting->title, [$vendorCategoryRfp->id, $rfpAttachmentSetting->id]) }}</li>
        <li>{{{ trans('forms.edit') }}} {{{ trans('general.attachmentSettings') }}}</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cogs"></i> {{{ trans('general.attachmentSettings') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($rfpAttachmentSetting)) {{{ trans('forms.edit') }}} {{{ $rfpAttachmentSetting->title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.attachmentSettings') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.rfp.attachment.settings.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-8 col-lg-8">
                            <label class="label">{{{ trans('general.title') }}} <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                {{ Form::text('title', Input::old('title', isset($rfpAttachmentSetting) ? $rfpAttachmentSetting->title : null), ['required' => 'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-4 col-lg-4">
                            <?php $defaultMandatory = (isset($rfpAttachmentSetting) && !$rfpAttachmentSetting->mandatory) ? 0 : 1; ?>
                            <label class="label">Mandatory <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('mandatory') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="mandatory" id="mandatory-select">
                                    <option value="1" @if(1 == Input::old('mandatory', $defaultMandatory)) selected @endif>{{{ trans('general.yes') }}}</option>
                                    <option value="0" @if(0 == Input::old('mandatory', $defaultMandatory)) selected @endif>{{{ trans('general.no') }}}</option>

                                </select>
                            </label>
                            {{ $errors->first('mandatory', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', (isset($rfpAttachmentSetting)) ? $rfpAttachmentSetting->id : -1) }}
                        {{ Form::hidden('vendor_category_rfp_id', $vendorCategoryRfp->id) }}
                        @if(!isset($rfpAttachmentSetting))
                        {{ link_to_route('consultant.management.rfp.attachment.settings.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
                        @else
                        {{ link_to_route('consultant.management.rfp.attachment.settings.show', trans('forms.back'), [$vendorCategoryRfp->id, $rfpAttachmentSetting->id], ['class' => 'btn btn-default']) }}
                        @endif
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection