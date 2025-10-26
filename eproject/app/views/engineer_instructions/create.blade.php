@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ link_to_route('ei', trans('navigation/projectnav.engineerInstruction') . ' (EI)', array($project->id)) }}</li>
		<li>Issue New {{ trans('navigation/projectnav.engineerInstruction') }} (EI)</li>
	</ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
	<h1>Issue New {{ trans('navigation/projectnav.engineerInstruction') }} (EI)</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>Issue New EI</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							@include('engineer_instructions.partials.ei_form')

							<footer>
								@if ( $isEditor )
									{{ Form::submit('Issue EI', array('class' => 'btn btn-primary', 'name' => 'issue_ei')) }}
								@endif

								{{ Form::submit('Save as Draft', array('class' => 'btn btn-default', 'name' => 'draft')) }}

								{{ link_to_route('ei', 'Cancel', [$project->id], ['class' => 'btn btn-default']) }}
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