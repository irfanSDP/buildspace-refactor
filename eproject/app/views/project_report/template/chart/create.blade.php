@extends('layout.main')

@section('css')
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projectReport.chart.template.index', trans('projectReportChart.templates'), array()) }}</li>
		<li>{{ trans('projectReportChart.newTemplate') }}</li>
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
                        {{ Form::open(array('url' => route('projectReport.chart.template.store'), 'method' => 'post', 'class' => 'smart-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.reportType') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ count($reportTypeMappings) > 0 ? 'select2':'form-control' }} fill-horizontal" name="reportTypeMapping" id="reportTypeMapping" required>
                                                @if (count($reportTypeMappings) === 0)<option value="">None</option>@endif
                                                @foreach ($reportTypeMappings as $mapping)
                                                    <option value="{{ $mapping->id }}">{{ $mapping->projectReportType->title }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('reportTypeMapping', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-11 col-md-5 col-lg-5">
                                        <label class="label">{{ trans('projectReportChart.chartType') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ count($chartTypes) > 0 ? 'select2':'form-control' }} fill-horizontal" name="chartType" id="chartType" required>
                                                @if (count($chartTypes) === 0)<option value="">None</option>@endif
                                                @foreach ($chartTypes as $key => $chartType)
                                                    <option value="{{ $key }}">{{ $chartType }}</option>
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
                                            <input type="text" name="title" value="" class="form-control" maxlength="250" required>
                                        </label>
                                        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), array('type' => 'submit', 'class' => 'btn btn-primary') )  }}
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
        /*function getColumnTypeSelectValue() {
            return $('#column_type').val();
        }

        $('.select').select2();

        $(document).on('change', '#column_type', function() {
            const columnType = getColumnTypeSelectValue();

                $('#template-name-input').closest('.form-group').show(200);
            } else {
                $('#template-name-input').closest('.form-group').hide(200);
            }
        });*/
    });
</script>
@endsection