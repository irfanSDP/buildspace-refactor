@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('costData.masterCostData') }}}</li>
        <li>{{{ trans('forms.add') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="far fa-map"></i> {{{ trans('costData.masterCostData') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('costData.masterCostData') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('route' => 'costData.master.store', 'method' => 'POST', 'class' => 'smart-form')) }}
                            @include('costData.master.formFields')
                            <footer>
                                <a href="{{ route('costData.master') }}" class="btn btn-default">{{ trans('forms.back') }}</a>
                                <button class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('general.save') }}</button>
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection