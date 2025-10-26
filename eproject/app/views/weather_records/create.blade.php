@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ link_to_route('wr', trans('navigation/projectnav.weatherRecord') . ' (WR)', array($project->id)) }}</li>
		<li>Create</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

	<h1>Issue New {{ trans('navigation/projectnav.weatherRecord') }} (WR)</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Issue New WR</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							@include('weather_records.partials.wr_form')

							<footer>
								@if ( $isEditor )
									@if ( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER) )
										{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'verify')) }}
									@else
										{{ Form::submit('Submit for Verify', array('class' => 'btn btn-primary', 'name' => 'issue_wr')) }}
									@endif
								@endif

								{{ Form::submit('Save as Draft', array('class' => 'btn btn-default', 'name' => 'draft')) }}

								{{ link_to_route('wr', 'Cancel', [$project->id], ['class' => 'btn btn-default']) }}
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