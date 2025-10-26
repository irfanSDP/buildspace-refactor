@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
        <li>{{ link_to_route('consultant.management.rfp.attachment.settings.index', trans('general.attachmentSettings'), [$vendorCategoryRfp->id]) }}</li>
        <li>{{{ PCK\Helpers\StringOperations::shorten($rfpAttachmentSetting->title, 30) }}}</li>
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
                <h2><i class="fa fa-fw fa-cogs"></i> {{{ $rfpAttachmentSetting->title }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.title') }}:</dt>
                                <dd>{{{ $rfpAttachmentSetting->title }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Mandatory:</dt>
                                <dd>{{{ ($rfpAttachmentSetting->mandatory) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    @if($user->isSuperAdmin() or $vendorCategoryRfp->consultantManagementContract->editableByUser($user))
                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            {{ HTML::decode(link_to_route('consultant.management.rfp.attachment.settings.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$vendorCategoryRfp->id, $rfpAttachmentSetting->id], ['class' => 'btn btn-primary'])) }}
                            @if($rfpAttachmentSetting->deletable())
                            {{ HTML::decode(link_to_route('consultant.management.rfp.attachment.settings.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$vendorCategoryRfp->id, $rfpAttachmentSetting->id], ['data-id'=>$rfpAttachmentSetting->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                            @endif
                            {{ link_to_route('consultant.management.rfp.attachment.settings.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
@endsection