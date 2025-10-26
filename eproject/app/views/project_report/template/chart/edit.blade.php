@extends('layout.main')

@section('css')
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projectReport.chart.template.index', trans('projectReportChart.templates'), array()) }}</li>
		<li>{{ trans('projectReportChart.editTemplate').': '.$record->title }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">{{ trans('projectReportChart.editTemplate') }}</h1>
		</div>
	</div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ $record->title }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('url' => route('projectReport.chart.template.update', array($record->id)), 'method' => 'post', 'class' => 'smart-form')) }}
                            <fieldset>
                                <div class="row" style="{{-- $record->is_locked ? 'display: none;' : '' --}}">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.reportType') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ count($reportTypeMappings) > 0 ? 'select2':'form-control' }} fill-horizontal" name="reportTypeMapping" id="reportTypeMapping" required>
                                                @if (count($reportTypeMappings) === 0)<option value="">None</option>@endif
                                                @foreach ($reportTypeMappings as $mapping)
                                                    <option value="{{ $mapping->id }}" {{ $mapping->id === $record->project_report_type_mapping_id ? 'selected' : '' }}>
                                                        {{ $mapping->projectReportType->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('reportTypeMapping', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row text-" style="{{-- $record->is_locked ? 'display: none;' : '' --}}">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.chartType') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ count($chartTypes) > 0 ? 'select2':'form-control' }} fill-horizontal" name="chartType" id="chartType" required>
                                                @if (count($chartTypes) === 0)<option value="">None</option>@endif
                                                @foreach ($chartTypes as $key => $chartType)
                                                    <option value="{{ $key }}" {{ $key == $record->chart_type ? 'selected' : '' }}>{{ $chartType }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('chartType', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.title') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <input type="text" name="title" value="{{ $record->title }}" class="form-control" maxlength="250" required>
                                        </label>
                                        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.update'), array('type' => 'submit', 'class' => 'btn btn-primary') )  }}
                                {{ link_to_route('projectReport.chart.template.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        //
    });
</script>
@endsection