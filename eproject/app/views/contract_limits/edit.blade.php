@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('technicalEvaluation.sets', trans('technicalEvaluation.technicalEvaluation')) }}</li>
        <li>{{ link_to_route('contractLimit.index', trans('contractLimit.contractLimit')) }}</li>
        <li>{{{ trans('forms.edit') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-list"></i> {{{ trans('contractLimit.contractLimit') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        {{{ trans('contractLimit.contractLimit') }}}
                    </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::model($contractLimit, array('route' => array('contractLimit.update', $contractLimit->id), 'method' => 'PUT', 'class' => 'smart-form')) }}
                            @include('contract_limits.form_fields')
                            <footer>
                                <button class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('general.save') }}</button>
                                <a href="{{ route('contractLimit.index') }}" class="btn btn-default">{{ trans('forms.back') }}</a>
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection