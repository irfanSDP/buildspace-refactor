@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($loe->project->title, 50), array($loe->project->id)) }}</li>
		<li>{{ link_to_route('loe', trans('navigation/projectnav.lossOrAndExpenses') . ' (L &amp; E)', array($loe->project->id)) }}</li>
		<li>{{ link_to_route('loe.show', "View Current L &amp; E ({$loe->subject})", array($loe->project->id, $loe->id)) }}</li>
		<li>Messaging Form</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $loe->project))
@endsection

@section('content')
	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-10">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				@if ($user->hasCompanyProjectRole($loe->project, PCK\ContractGroups\Types\Role::CONTRACTOR))
					@include('loss_and_or_expense_fourth_level_messages.partials.contractor_form')
				@else
					@include('loss_and_or_expense_fourth_level_messages.partials.architect_qs_form')
				@endif
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection