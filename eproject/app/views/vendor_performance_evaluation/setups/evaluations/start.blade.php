@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ trans('vendorManagement.setup') }}</li>
        <li>{{{ $evaluation->project->short_title }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.start') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $evaluation->project->title }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.setups.evaluations.start', $evaluation->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('projects.title') }}}:</label>
                                <label class="input">
                                    {{{ $evaluation->project->title }}}
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('projects.reference') }}}:</label>
                                <label class="input">
                                    {{{ $evaluation->project->reference }}}
                                </label>
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('projects.businessUnit') }}}:</label>
                                <label class="input">
                                    {{{ $evaluation->project->businessUnit->name }}}
                                </label>
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('projects.stage') }}}:</label>
                                <label class="input">
                                    {{{ \PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation($evaluation->project_status_id) }}}
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.startDate') }}}:</label>
                                <label class="input">
                                    {{ \Carbon\Carbon::parse($evaluation->start_date)->format(\Config::get('dates.submitted_at')) }}
                                </label>
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('vendorManagement.endDate') }}}:</label>
                                <label class="input">
                                    {{ \Carbon\Carbon::parse($evaluation->end_date)->format(\Config::get('dates.submitted_at')) }}
                                </label>
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.setups.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('vendorManagement.start'), ['type' => 'submit', 'class' => 'btn btn-warning'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection