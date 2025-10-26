@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ai->project->title, 50), array($ai->project->id)) }}</li>
		<li>{{ link_to_route('ai', trans('navigation/projectnav.architectInstruction') . ' (AI)', array($ai->project->id)) }}</li>
		<li>{{ link_to_route('ai.show', "View Current AI ({$ai->reference})", array($ai->project->id, $ai->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ai->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				@if ($user->hasCompanyProjectRole($ai->project, PCK\ContractGroups\Types\Role::CONTRACTOR))
					@include('architect_instruction_third_level_messages.partials.contractor_form')
				@else
					@include('architect_instruction_third_level_messages.partials.architect_form')
				@endif
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection