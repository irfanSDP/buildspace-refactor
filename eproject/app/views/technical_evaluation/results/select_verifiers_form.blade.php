@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('technicalEvaluation.results.index', trans('technicalEvaluation.technicalEvaluationResults'), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
        <li>{{ trans('technicalEvaluation.assignVerifier') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <?php use PCK\Filters\TenderFilters; ?>

    <?php $readOnly = ( ( $tender->technicalEvaluationIsBeingValidated() OR $tender->technicalEvaluationIsSubmitted() ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{ trans('technicalEvaluation.assignVerifier') }}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="btn-group pull-right header-btn">
                {{ Form::open(array('route' => array('technicalEvaluation.results.verifiers.reassign', $project->id, $tender->id))) }}
                    {{ Form::button('<i class="fa fa-redo"></i> '.trans('tenders.reassignVerifiers'), ['type' => 'submit', 'class' => 'btn btn-primary btn-md'] )  }}
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="row">
        <article class="col-sm-12">
            <div class="jarviswidget" data-widget-togglebutton="false" data-widget-editbutton="false" data-widget-fullscreenbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                <header>
                    <ul id="technical_evaluation_verifiers-tab" class="nav nav-tabs pull-right in">
                    @foreach ( $selectedCompanies as $idx => $company )
                        <li @if($idx == 0) class="active" @endif>
                            <a href="#{{ $company->id }}" data-toggle="tab" style="font-size:11px;"><i class="fa fa-fw fa-university"></i> <span class="hidden-mobile hidden-tablet">{{{ $company->name }}}</span></a>
                        </li>
                    @endforeach
                    </ul>
                </header>

                <div class="no-padding">
                    <div class="widget-body">
                        {{ Form::open(array('route' => array('technicalEvaluation.results.verifiers.assign', $project->id, $tender->id), 'class'=>'smart-form')) }}
                        <div id="technical_evaluation_verifiers-tab_content" class="tab-content" style="height: 100%;">
                            @foreach ( $selectedCompanies as $idx => $company )
                            <div class="tab-pane fade @if($idx == 0) active @endif in padding-10 no-padding-bottom" id="{{ $company->id }}">
                                <div class="well" style="margin-bottom:12px;">
                                    <div class="row">
                                        <div class="col col-lg-12">
                                            <dl class="dl-horizontal no-margin">
                                                <dt>{{{ trans('companies.name') }}}:</dt>
                                                <dd>{{ nl2br($company->name) }}</dd>
                                                <dt>{{{ trans('companies.referenceNumber') }}}:</dt>
                                                <dd>{{{ $company->reference_no }}}</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-bordered table-condensed table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-middle text-center squeeze" style="width: 120px;">Selected Verifier(s)</th>
                                            <th class="text-middle text-left">Name</th>
                                            <th class="text-middle text-center squeeze" style="width:240px;">E-Mail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @if ( array_key_exists($company->id, $selectedUsers) )
                                        @foreach ($selectedUsers[$company->id] as $user)
                                            <tr>
                                                <td class="text-middle text-center">
                                                    @if ( $readOnly )
                                                        @if ( in_array($user->id, $selectedVerifiers) )
                                                            {{ link_to_route('technicalEvaluation.resendTechnicalEvaluationVerifierEmail', 'Resend Email', array($project->id, $project->latestTender->id, $user->id)) }}
                                                        @else
                                                            -
                                                        @endif
                                                    @else
                                                        {{ Form::checkbox('selected_users[]', $user->id, in_array($user->id, $selectedVerifiers)) }}
                                                    @endif
                                                </td>
                                                <td class="text-middle text-left">{{{ $user->name }}}</td>
                                                <td class="text-middle text-center">{{{ $user->email }}}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-middle text-center">No user(s) available</td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            @endforeach
                        </div>
                        <footer>
                            {{ link_to_route('technicalEvaluation.results.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                            <a href="#" data-toggle="modal" data-target="#verifierLogsModal" class="btn btn-info btn-md">
                                <i class="fa fa-search"></i> {{ trans('technicalEvaluation.viewLog') }}
                            </a>
                            @if ( ! $readOnly )
                            {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-success', 'name' => 'send_to_verify'] )  }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            @endif
                        </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </article>
    </div>
    @include('open_tenders.partials.verifier_logs', array(
        'title' => trans('technicalEvaluation.verifierLog'),
        'logs' => $tender->technicalEvaluationVerifierLogs
    ))
@endsection