@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($wr->project->title, 50), array($wr->project->id)) }}</li>
		<li>{{ link_to_route('wr', trans('navigation/projectnav.weatherRecord') . ' (WR)', array($wr->project->id)) }}</li>
		<li>View Current Weather Record (WR)</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $wr->project))
@endsection

@section('content')
	<h1>View Current {{ trans('navigation/projectnav.weatherRecord') }} (WR)</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>View WR ({{{ $wr->project->getProjectTimeZoneTime($wr->date) }}})</h2>
				</header>

				<!-- widget div-->
				<div>
					@if ( $wr->status == PCK\WeatherRecords\WeatherRecord::DRAFT_TEXT )
						@if (($wr->created_by == $user->id && $user->stillInSameAssignedCompany($wr->project, $wr->created_at)) or $isEditor)
							@include('weather_records.partials.wr_update_form', array('project' => $wr->project))
						@else
							@include('weather_records.partials.wr_view_only')
						@endif
					@else
						@include('weather_records.partials.wr_view_only')
					@endif
				</div>
				<!-- end widget div -->
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection