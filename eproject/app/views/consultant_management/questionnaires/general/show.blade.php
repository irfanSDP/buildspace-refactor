@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        @if(isset($vendorCategoryRfp))
        <li>{{ link_to_route('consultant.management.calling.rfp.index', $vendorCategoryRfp->vendorCategory->name, [$vendorCategoryRfp->id]) }}</li>
        <li>{{ link_to_route('consultant.management.consultant.questionnaire.show', trans('general.questionnaires'), [$vendorCategoryRfp->id, $company->id]) }}</li>
        @else
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $consultantManagementContract->short_title, [$consultantManagementContract->id]) }}</li>
        <li>{{ link_to_route('consultant.management.questionnaire.settings.index', trans('general.questionnaireSettings'), [$consultantManagementContract->id]) }}</li>
        @endif
        <li>{{{ trans('general.generalQuestionnaires') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{{ trans('general.generalQuestionnaires') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-fw fa-tasks"></i> {{{ trans('general.questionnaire') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.question') }}:</dt>
                                <dd>{{ $questionnaire->question }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>Mandatory:</dt>
                                <dd>{{{ ($questionnaire->required) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{{ trans('general.type') }}}:</dt>
                                <dd>{{{ $questionnaire->getTypeText() }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>With Attachment:</dt>
                                <dd>{{{ ($questionnaire->with_attachment) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>

                    @if($questionnaire->type == PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT or $questionnaire->type == PCK\ConsultantManagement\ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT)
                    <hr class="simple">

                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <h5>{{{trans('documentManagementFolders.options')}}}</h5>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <table class="table table-bordered table-condensed table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:38px;text-align:center;">No.</th>
                                        <th style="width:auto;">{{{trans('documentManagementFolders.options')}}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($questionnaire->options as $idx => $option)
                                    <tr>
                                        <td class="text-middle text-center squeeze">{{$idx+1}}</td>
                                        <td>{{{ $option->text }}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                    @endif

                    @if($user->isSuperAdmin() or ($user->isGroupAdmin() && ($user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT or $user->isConsultantManagementEditorByRole($consultantManagementContract, PCK\ConsultantManagement\ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT)))))
                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            @if($questionnaire && !isset($vendorCategoryRfp) && $questionnaire->deletable())
                            {{ HTML::decode(link_to_route('consultant.management.questionnaire.settings.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$consultantManagementContract->id, $questionnaire->id], ['class' => 'btn btn-primary'])) }}
                            {{ HTML::decode(link_to_route('consultant.management.questionnaire.settings.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$consultantManagementContract->id, $questionnaire->id], ['data-id'=>$questionnaire->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                            @endif
                            @if(isset($vendorCategoryRfp))
                            {{ link_to_route('consultant.management.consultant.questionnaire.show', trans('forms.back'), [$vendorCategoryRfp->id, $company->id], ['class' => 'btn btn-default']) }}
                            @else
                            {{ link_to_route('consultant.management.questionnaire.settings.index', trans('forms.back'), [$consultantManagementContract->id], ['class' => 'btn btn-default']) }}
                            @endif
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