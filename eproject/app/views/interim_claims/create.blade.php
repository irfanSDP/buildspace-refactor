@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ link_to_route('ic', trans('navigation/projectnav.interimClaim') . ' (IC)', array($project->id)) }}</li>
		<li>Application for {{ trans('navigation/projectnav.interimClaim') }} (IC)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
	<h1>Application for {{ trans('navigation/projectnav.interimClaim') }} (IC)</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Application for {{ trans('navigation/projectnav.interimClaim') }} (IC)</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							@include('interim_claims.partials.ic_form')

							<footer>
								@if ( $isEditor )
									{{ Form::submit('Submit', array('class' => 'btn btn-primary', 'name' => 'issue_ic')) }}
								@endif

								{{ link_to_route('ic', 'Cancel', [$project->id], ['class' => 'btn btn-default']) }}
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