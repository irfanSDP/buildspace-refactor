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
		<article class="col-sm-12 col-md-12 col-lg-8">
			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget">
				<header>
					<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
					<h2>{{{ PCK\Forms\LossAndOrExpenseInterimClaimForm::formTitle }}}</h2>
				</header>

				<!-- widget div-->
				<div>
					<!-- widget content -->
					<div class="widget-body no-padding">
						{{ Form::open(array('class' => 'smart-form')) }}
							<fieldset>
								<section>
									<label class="label">The Loss and/or Expense has been paid in the following Interim Certificate<span class="required">*</span>:</label>
									<label class="{{{ $errors->has('interim_claim_id') ? 'state-error' : null }}}">
										{{ Form::select('interim_claim_id', $ics) }}
									</label>
									{{ $errors->first('interim_claim_id', '<em class="invalid">:message</em>') }}
								</section>

								<section>
									<label class="label">Attachment(s):</label>

									@include('file_uploads.partials.upload_file_modal', ['project' => $loe->project])
								</section>
							</fieldset>

							<footer>
								{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}

								{{ link_to_route('loe.show', 'Cancel', [$loe->project->id, $loe->id], ['class' => 'btn btn-default']) }}
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