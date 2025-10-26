@extends('layout.main')

@section('css')
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projectReport.chart.template.index', trans('projectReportChart.templates'), array()) }}</li>
		<li>{{ link_to_route('projectReport.chart.plot.template.index', $chart->title, array($chart->id)) }}</li>
		<li>{{ trans('projectReportChart.newPlot') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">{{ trans('projectReportChart.newTemplate') }}</h1>
		</div>
	</div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ trans('projectReportChart.newTemplate') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('url' => route('projectReport.chart.plot.template.store', array($chart->id)), 'method' => 'post', 'class' => 'smart-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.plotType') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ (! empty($selections['plot_types']))?'select2':'form-control' }} fill-horizontal" name="plotType" id="plotType" required>
                                                @if (empty($selections['plot_types']))<option value="">None</option>@endif
                                                @foreach ($selections['plot_types'] as $key => $plotType)
                                                    <option value="{{ $key }}">{{ $plotType }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('plotType', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.categoryColumn') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ (! empty($selections['category_columns']))?'select2':'form-control' }} fill-horizontal" name="categoryColumn" id="categoryColumn" required>
                                                @if (empty($selections['category_columns']))<option value="">None</option>@endif
                                                @foreach ($selections['category_columns'] as $key => $categoryColumn)
                                                    <option value="{{ $key }}">{{ $categoryColumn }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('categoryColumn', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div id="categoryColumnPartials"></div>
                                <div id="valueColumnPartials"></div>
                            </fieldset>
                            <footer>
                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), array('type' => 'submit', 'class' => 'btn btn-primary') )  }}
                                {{ link_to_route('projectReport.chart.plot.template.index', trans('forms.back'), array($chart->id), array('class' => 'btn btn-default')) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('project_report.template.chart.plot.scripts')
@endsection