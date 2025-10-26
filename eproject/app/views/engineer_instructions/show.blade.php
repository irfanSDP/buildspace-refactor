@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($ei->project->title, 50), array($ei->project->id)) }}</li>
		<li>{{ link_to_route('ei', trans('navigation/projectnav.engineerInstruction') . ' (EI)', array($ei->project->id)) }}</li>
		<li>View Current EI ({{{ $ei->subject }}})</li>
	</ol>

    @include('projects.partials.project_status', array('project' => $ei->project))
@endsection

@section('content')
	<h1>View {{ trans('navigation/projectnav.engineerInstruction') }} (EI)</h1>

	<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-6">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>View Current EI ({{{ $ei->subject }}})</h2>
				</header>

				<!-- widget div-->
				<div>
					@if ( $ei->status == PCK\LossOrAndExpenses\LossOrAndExpense::DRAFT_TEXT )
						@if (($ei->created_by == $user->id && $user->stillInSameAssignedCompany($ei->project, $ei->created_at)) or $isEditor and $user->hasCompanyProjectRole($ei->project, array(PCK\ContractGroups\Types\Role::CONSULTANT_1, PCK\ContractGroups\Types\Role::CONSULTANT_2)))
							@include('engineer_instructions.partials.ei_update_form', array('project' => $ei->project))
						@else
							@include('engineer_instructions.partials.ei_view_only')
						@endif
					@else
						@include('engineer_instructions.partials.ei_view_only')
					@endif
				</div>
				<!-- end widget div -->
			</div>
			<!-- end widget -->
		</article>
		<!-- END COL -->
	</div>
@endsection