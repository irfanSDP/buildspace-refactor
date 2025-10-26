@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        <li>{{ link_to_route('consultant.management.attachment.settings.index', trans('general.attachmentSettings'), [$consultantManagementContract->id]) }}</li>
        <li>{{{ PCK\Helpers\StringOperations::shorten($attachmentSetting->title, 30) }}}</li>
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
                <h2><i class="fa fa-fw fa-cogs"></i> {{{ $attachmentSetting->title }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.title') }}:</dt>
                                <dd>{{{ $attachmentSetting->title }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Mandatory:</dt>
                                <dd>{{{ ($attachmentSetting->mandatory) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    @if($user->isSuperAdmin() or ($user->isGroupAdmin() && ($user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT or $user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)))))
                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            @if($attachmentSetting && $attachmentSetting->deletable())
                            {{ HTML::decode(link_to_route('consultant.management.attachment.settings.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$consultantManagementContract->id, $attachmentSetting->id], ['class' => 'btn btn-primary'])) }}
                            {{ HTML::decode(link_to_route('consultant.management.attachment.settings.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$consultantManagementContract->id, $attachmentSetting->id], ['data-id'=>$attachmentSetting->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                            @endif
                            {{ link_to_route('consultant.management.attachment.settings.index', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
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