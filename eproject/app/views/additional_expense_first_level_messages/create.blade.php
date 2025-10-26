@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ae->project->title, 50), array($ae->project->id)) }}</li>
		<li>{{ link_to_route('ae', trans('navigation/projectnav.additionalExpenses') . ' (AE)', array($ae->project->id)) }}</li>
		<li>{{ link_to_route('ae.show', "View Current AE ({$ae->subject})", array($ae->project->id, $ae->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

	@include('projects.partials.project_status', array('project' => $ae->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				@if ($user->hasCompanyProjectRole($ae->project, PCK\ContractGroups\Types\Role::CONTRACTOR))
					@include('additional_expense_first_level_messages.partials.contractor_form')
				@else
					@include('additional_expense_first_level_messages.partials.architect_form')
				@endif
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection