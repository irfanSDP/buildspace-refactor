@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $project->latestTender->current_tender_name, array($project->id, $project->latestTender->id)) }}</li>
        <li>Assign Verifier(s) for Open Tender Purposes</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

    <?php use PCK\Filters\TenderFilters; ?>

    <?php $readOnly = ( ( $tender->openTenderIsBeingValidated() OR $tender->openTenderIsSubmitted() ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false; ?>

    <div class="row">
        <div class="col-xs-12">
            <h1 class="page-title txt-color-blueDark">
                Assign Verifier(s) for Open Tender Purposes
            </h1>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="pull-right">
                <div class="btn-group header-btn">
                    <a href="#" data-toggle="modal" data-target="#verifierLogsModal" class="btn btn-success btn-sm">
                    <i class="fa fa-search"></i> View Open Tender Verifier Logs
                    </a>
                </div>
                <div class="btn-group header-btn">
                    {{ Form::open(array('route' => array('projects.openTender.reassignOTVerifiers', $project->id, $tender->id))) }}
                        {{ Form::button('<i class="fa fa-users"></i> '.trans('tenders.reassignVerifiers'), ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <br />

    {{ Form::open(array('method' => 'PUT')) }}
    @foreach ( $selectedCompanies as $company )
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ $company->name }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align:center;width:60px;">{{trans('verifiers.verifiers')}}</th>
                                        <th>{{trans('general.name')}}</th>
                                        <th style="text-align:center;width:240px;">{{trans('users.email')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if ( array_key_exists($company->id, $selectedUsers) )
                                    @foreach ($selectedUsers[$company->id] as $user)
                                        <tr>
                                            <td style="text-align: center;">
                                                @if ( $readOnly )
                                                    @if ( in_array($user->id, $selectedVerifiers) )
                                                        {{ link_to_route('projects.openTender.resendOTVerifierEmail', 'Resend Email', array($project->id, $project->latestTender->id, $user->id)) }}
                                                    @else
                                                        -
                                                    @endif
                                                @else
                                                    {{ Form::checkbox('selected_users[]', $user->id, in_array($user->id, $selectedVerifiers)) }}
                                                @endif
                                            </td>
                                            <td>{{{ $user->name }}}</td>
                                            <td style="text-align: center;">{{{ $user->email }}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" style="text-align: center;" class="alert-warning">
                                            No user(s) available
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

        @include('open_tenders.partials.verifier_logs', array(
            'title' => 'View Open Tender Verifier Logs',
            'logs' => $tender->openTenderVerifierLogs
        ))

        @if ( ! $readOnly )
            <!--  Input -->
            <div class="form-group pull-right">
                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  }}

                {{ Form::button('<i class="fa fa-file-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-success btn-sm', 'name' => 'send_to_verify'] )  }}

                {{ link_to_route('projects.openTender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
            </div>
        @endif
    {{ Form::close() }}

@endsection