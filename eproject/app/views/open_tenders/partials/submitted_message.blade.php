@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('navigation/projectnav.openTender') }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
        <li>Open Tender</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> Open Tender Verification for {{{ $tender->current_tender_name }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                        <header>Open Tender Verification for {{{ $tender->current_tender_name }}}</header>

                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    Sorry we cannot process your because {{{ $tender->current_tender_name }}} has already been submitted.
                                </section>
                            </div>
                        </fieldset>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop