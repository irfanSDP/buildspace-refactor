@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($eot->project->title, 50), array($eot->project->id)) }}</li>
		<li>{{ link_to_route('eot', trans('navigation/projectnav.extensionOfTime') . ' (EOT)', array($eot->project->id)) }}</li>
		<li>{{ link_to_route('eot.show', "View Current EOT ({$eot->subject})", array($eot->project->id, $eot->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $eot->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-10">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				@if ($user->hasCompanyProjectRole($eot->project, PCK\ContractGroups\Types\Role::CONTRACTOR))
					@include('extension_of_time_third_level_messages.partials.contractor_form')
				@else
					@include('extension_of_time_third_level_messages.partials.architect_form')
				@endif
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection