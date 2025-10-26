@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ link_to_route('wr', trans('navigation/projectnav.weatherRecord') . ' (WR)', array($project->id)) }}</li>
		<li>New Weather Detail Record</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<h1>Issue New Weather Detail Record</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Weather Detail</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">Time<span class="required">*</span>:</label>
									{{ Form::select('from_time', PCK\Base\Helpers::generateTimeArray()) }} to {{ Form::select('to_time', PCK\Base\Helpers::generateTimeArray()) }}
								</section>

								<section>
									<label class="label">Weather<span class="required">*</span>:</label>
									<label class="input {{{ $errors->has('weather_status') ? 'state-error' : null }}}">
										{{ Form::select('weather_status', PCK\WeatherRecordReports\WeatherStatusTypeTrait::generateWeatherStatusDropDownData()) }}
									</label>
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Add Record', array('class' => 'btn btn-primary')) }}

								@if ( $mode == 'new' )
									{{ link_to_route('wr.create', 'Cancel', [$project->id, $wrId], ['class' => 'btn btn-default']) }}
								@else
									{{ link_to_route('wr.show', 'Cancel', [$project->id, $wrId], ['class' => 'btn btn-default']) }}
								@endif
							</footer>
						{{ Form::close() }}
					</div>
					<!-- end widget content -->
				</div>
				<!-- end widget div -->
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection