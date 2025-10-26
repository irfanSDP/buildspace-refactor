@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, array($project->id, $tender->id)) }}</li>
        <li>Open Tender Verifier Log</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> Open Tender Verifier Log for {{{ $tender->current_tender_name }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                            <header>Open Tender Verifier Log for {{{ $tender->current_tender_name }}}</header>

                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        @if ( $tender->openTenderVerifierLogs->isEmpty() )
                                            <p class="required">There are no log entries.</p>
                                        @else
                                            <ol style="padding: 0 0 0 20px;">
                                                @foreach ( $tender->openTenderVerifierLogs as $log )
                                                    <li>{{ $log->present()->log_text_format() }}</li>
                                                @endforeach
                                            </ol>
                                        @endif
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