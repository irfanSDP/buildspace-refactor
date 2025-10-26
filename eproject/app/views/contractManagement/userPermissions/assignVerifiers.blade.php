@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('contractManagement.contractManagement') }}</li>
        <li>{{{ trans('contractManagement.userManagement') }}}</li>
        <li>{{{ trans('contractManagement.assignVerifiers') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-eye" data-type="tooltip" data-toggle="tooltip" data-placement="right" title="{{{ trans('contractManagement.userManagementHelp') }}}"></i> {{{ trans('contractManagement.assignVerifiers') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ $moduleName }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                    @if($verifierRecords->isEmpty())
                        {{ Form::open(array('route' => array('contractManagement.permissions.verifiers.assign', $project->id, $moduleId), 'class' => 'smart-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-4 col-md-4 col-lg-4">
                                        @include('verifiers.select_verifiers')
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                <a href="{{ route('contractManagement.permissions.index', array($project->id)) }}#{{{ $moduleId }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                                {{ Form::button('<i class="fa fa-eye"></i> '.trans('forms.assign'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    @else
                        {{ Form::open(array('route' => array('contractManagement.permissions.verifiers.reset', $project->id, $moduleId), 'class' => 'smart-form')) }}
                            @include('verifiers.verifier_status_overview', array('showStatus' => false))
                            <footer>
                                <a href="{{ route('contractManagement.permissions.index', array($project->id)) }}#{{{ $moduleId }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                                {{ Form::button('<i class="fa fa-sync"></i> '.trans('forms.reset'), ['type' => 'submit', 'class' => 'btn btn-warning'] )  }}
                            </footer>
                        {{ Form::close() }}
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection