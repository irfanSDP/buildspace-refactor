@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.loa.running.number.index', 'LOA Running Numbers') }}</li>
        <li>{{{ $subsidiaryRunningNumber->subsidiary->short_name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-sort-numeric-down"></i> LOA Running Number
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-fw fa-sort-numeric-down"></i> {{{ $subsidiaryRunningNumber->subsidiary->short_name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('subsidiaries.subsidiary') }}:</dt>
                                <dd>{{ $subsidiaryRunningNumber->subsidiary->name }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('subsidiaries.subsidiaryCode') }}:</dt>
                                <dd>{{{ $subsidiaryRunningNumber->subsidiary->identifier }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>Next Running Number:</dt>
                                <dd>{{ $subsidiaryRunningNumber->next_running_number }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            {{ HTML::decode(link_to_route('consultant.management.loa.running.number.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$subsidiaryRunningNumber->subsidiary_id], ['class' => 'btn btn-primary'])) }}
                            {{ link_to_route('consultant.management.loa.running.number.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection