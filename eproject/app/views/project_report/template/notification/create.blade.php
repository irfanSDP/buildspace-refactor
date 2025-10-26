@extends('layout.main')

@section('css')
    <style>
        .select2-container .select2-selection--single {
            height: 34px;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ link_to_route('projectReport.notification.reportTypes', trans('projectReport.reportTypes'), [$project->id, 'permission_type' => 'reminder']) }}</li>
        <li>{{ $mappingTitle }}</li>
        <li>{{ link_to_route('projectReport.notification.index', trans('projectReportNotification.title'), [$project->id, $mappingId]) }}</li>
        <li>{{ trans('projectReportNotification.newTemplate') }}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">{{ trans('projectReportNotification.newTemplate') }}</h1>
		</div>
	</div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2><i class="fa fa-list"></i> {{ trans('projectReportNotification.newTemplate') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('url' => route('projectReport.notification.store', [$project->id, $mappingId]), 'method' => 'post', 'class' => 'smart-form', 'id' => 'reminderForm')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">{{ trans('projectReportNotification.templateName') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <input type="text" name="templateName" value="" class="form-control" maxlength="250" required>
                                        </label>
                                        {{ $errors->first('templateName', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">{{ trans('projectReportNotification.categoryColumn') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ (! empty($categoryColumns))?'select2':'form-control' }} fill-horizontal" name="categoryColumn" id="categoryColumn" required>
                                                @if (empty($categoryColumns))<option value="">None</option>@endif
                                                @foreach ($categoryColumns as $columnId => $columnTitle)
                                                    <option value="{{ $columnId }}">{{ $columnTitle }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('categoryColumn', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <div id="categoryColumnPartials"></div>
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">{{ trans('projectReportNotification.period') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ (! empty($periodSelections))?'select2':'form-control' }} fill-horizontal" name="periodValue[]" id="periodValue" multiple>
                                                @if (empty($periodSelections))<option value="">None</option>
                                                @else
                                                    @for($i = 1; $i <= 100; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                @endif
                                            </select>
                                        </label>
                                        {{ $errors->first('periodValue', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">&nbsp;</label>
                                        <label class="fill-horizontal">
                                            <select class="{{ (! empty($periodSelections))?'select2':'form-control' }} fill-horizontal" name="periodType" id="periodType" required>
                                                @if (empty($periodSelections))<option value="">None</option>@endif
                                                @foreach ($periodSelections as $key => $period)
                                                    <option value="{{ $key }}">{{ $period }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        {{ $errors->first('periodType', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{ trans('projectReportNotification.subject') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <input type="text" name="subject" value="" class="form-control" maxlength="250" required>
                                        </label>
                                        {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{ trans('projectReportNotification.body') }} <span class="required">*</span>:</label>
                                        <label class="fill-horizontal">
                                            <textarea name="body" class="form-control" rows="5" required></textarea>
                                        </label>
                                        {{ $errors->first('body', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), array('type' => 'submit', 'class' => 'btn btn-primary') )  }}
                                {{ link_to_route('projectReport.notification.index', trans('general.back'), [$project->id, $mappingId], array('class' => 'btn btn-default')) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @include('project_report.template.notification.scripts')
@endsection