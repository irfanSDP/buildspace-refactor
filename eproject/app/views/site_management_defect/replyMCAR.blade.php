@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.mcar-form') }}}</li>
    </ol>

@endsection

@section('content')

<style>
    .horizontal_line {
        border-top:1px solid #000;
    }

    .horizontal_dashed {
        border-top:1px dashed #000;
    }
</style>

@if(isset($response->verified_at))
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <a target="_blank" href="{{ route('site-management-defect.printMCAR', array($project->id,$record->siteManagementDefect->id))}}">
		    <button class="btn btn-primary btn-md">
		        <span class="glyphicon glyphicon-print"></span>&nbsp;&nbsp;{{{ trans('siteManagementDefect.print') }}}
		    </button>
		</a>
    </div>
</div>
@endif

<div class="row">
	<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
	    <div class="jarviswidget jarviswidget-sortable">
	        <header role="heading">
	            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

	            <h2>{{{ trans('siteManagementDefect.mcar-title') }}}</h2>
	        </header>

	        <!-- widget div-->
	        <div role="content">
	            <!-- widget content -->
	            <div class="widget-body no-padding">
	                <div class="smart-form">
	                    <fieldset>
	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.project') }}}&#58;</label>
	                            <label class="input">
	                                 {{{$record->project->title}}}
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.mcar-number') }}}&#58;</label>
	                            <label class="input">
		                            @if(isset($record->mcar_number))
		                                {{{$record->mcar_number}}}
		                            @endif
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.sub-con') }}}&#58;</label>
	                            <label class="input">
	                                @if($record->contractor_id !=NULL)
	                                {{{$record->company->name}}}
	                                @else
	                                {{{ trans('siteManagementDefect.con-not-selected') }}}
	                                @endif  
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.work-description') }}}&#58;</label>
	                            <label class="input">
	                                {{{$record->work_description}}}
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.remark') }}}&#58;</label>
	                            <label class="input">
	                                {{{$record->remark}}}
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.submitted-by') }}}&#58;</label>
	                            <label class="input">
	                                {{{$record->user->name}}}
	                            </label>
	                        </section>

	                        <section>
	                            <label class="label">{{{ trans('siteManagementDefect.date-submitted') }}}&#58;</label>
	                            <label class="input">
	                                {{{$project->getProjectTimeZoneTime($record->created_at)}}}
	                            </label>
	                        </section>
	                    </fieldset>
	                </div>
	            </div>
	            <!-- end widget content -->
	        </div>
	        <!-- end widget div -->
	    </div>
	</article>

</div>

<hr class="horizontal_line">

<!-- Response Details start -->

@if(PCK\SiteManagement\SiteManagementMCARFormResponse::checkRecordExists($form_id))

<div class="row">

	<article class="col-sm-12 col-md-12 col-lg-12">

		    <h2 data-toggle="collapse" data-target="#responses">{{{ trans('siteManagementDefect.con-response') }}}<i class="glyphicon glyphicon-chevron-down pull-right" data-toggle="collapse" data-target="#responses"></i>
		    </h2>
		    <?php
		     $satisfactory = PCK\SiteManagement\SiteManagementMCARFormResponse::getStatusText($response->satisfactory);
		     $applicable = PCK\SiteManagement\SiteManagementMCARFormResponse::getStatusText($response->applicable);
		    ?>
		    <div class="collapse" id="responses">
			    <fieldset>
			    	<h3>{{{ trans('siteManagementDefect.con-response') }}}</h3>
			    	<div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="cause">{{{ trans('siteManagementDefect.cause') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				           &#58;&nbsp;{{{$response->cause}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="action">{{{ trans('siteManagementDefect.action') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$response->action}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="applicable">{{{ trans('siteManagementDefect.applicable') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$applicable}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="corrective">{{{ trans('siteManagementDefect.corrective') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            @if(empty($response->corrective))
				            &#58;&nbsp;{{{ trans('siteManagementDefect.none') }}}
				            @else
				            &#58;&nbsp;{{{$response->corrective}}}
				            @endif
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="commitment_date">{{{ trans('siteManagementDefect.commit-date') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$project->getProjectTimeZoneTime($response->commitment_date)}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="submitted_by">{{{ trans('siteManagementDefect.submitted-by') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$response->user->name}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="date_submitted">{{{ trans('siteManagementDefect.date-submitted') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$project->getProjectTimeZoneTime($response->created_at)}}}
				        </div>
			        </div>
			        <br>
				</fieldset>
				<fieldset>
					<h3>{{{ trans('siteManagementDefect.pic-verify') }}}</h3>
					<div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="comment">{{{ trans('siteManagementDefect.comment-site') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$response->comment}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="satisfactory">{{{ trans('siteManagementDefect.satisfactory-site') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            &#58;&nbsp;{{{$satisfactory}}}
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="reinspection_date">{{{ trans('siteManagementDefect.reinspection-date') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            @if(empty($response->reinspection_date))
				            &#58;&nbsp;{{{ trans('siteManagementDefect.none') }}}
				            @else
				            &#58;&nbsp;{{{$MCARRecord->project->getProjectTimeZoneTime($response->reinspection_date)}}}
				            @endif
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="reinspection_date">{{{ trans('siteManagementDefect.submitted-by') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				        	@if(empty($response->verifier_id))
				            &#58;&nbsp;{{{ trans('siteManagementDefect.none') }}}
				            @else
				            &#58;&nbsp;{{{$response->verifier->name}}}
				            @endif
				        </div>
			        </div>
			        <br>
			        <div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="reinspection_date">{{{ trans('siteManagementDefect.verified-at') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				        	@if(empty($response->verified_at))
				            &#58;&nbsp;{{{ trans('siteManagementDefect.none') }}}
				            @else
				            &#58;&nbsp;{{{$project->getProjectTimeZoneTime($response->verified_at)}}}
				            @endif
				        </div>
			        </div>
			        <br>
				</fieldset>
		    </div>
		    <hr class="horizontal_dashed">
	</article>

</div>

    @if(PCK\SiteManagement\SiteManagementUserPermission::isSiteUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) && $response->comment == "none")

	    <h4>{{{ trans('siteManagementDefect.effectiveness-verify') }}}</h4>
	    <br><br>

	    {{ Form::open(array('route' => array('site-management-defect.verifyMCAR', $project->id, $form_id))) }}
	    <div class="row">
		    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			    <fieldset id="form">
			    	<div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label>{{{ trans('siteManagementDefect.verify') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				        	<label><input type="radio" name="satisfactory" value="2">&nbsp;&nbsp;{{{ trans('siteManagementDefect.satisfactory') }}}</label>
				        	&nbsp;&nbsp;&nbsp;&nbsp;
				            <label><input type="radio" name="satisfactory" value="3">&nbsp;&nbsp;{{{ trans('siteManagementDefect.not-satisfactory') }}}</label>
				            {{ $errors->first('satisfactory', '<em class="invalid">:message</em>') }}
				        </div>
			        </div>
			        <br><br>
			    	<div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="comment">{{{ trans('siteManagementDefect.comment') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            <textarea class="form-control" rows="5" name="comment">{{{Input::old('comment')}}}</textarea>
				            {{ $errors->first('comment', '<em class="invalid">:message</em>') }}
				        </div>
			        </div>
			        <br><br>
			       	<div class="row">
				        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
				            <label for="reinspection_date">{{{ trans('siteManagementDefect.reinspection-date') }}}</label>
				        </div>
				        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
				            {{ Form::text('reinspection_date', Input::old('reinspection_date') ? Input::old('reinspection_date') : \Carbon\Carbon::now($project->timezone)->addDays(7)->format('d-M-Y'), array('class' => 'datetimepicker')) }}
				        </div>
			        </div>
			        <div>
			            <button class="btn btn-success btn-md header-btn" type="submit">
			                {{{ trans('siteManagementDefect.submit') }}}
			            </button>
			            <a href="{{ route('site-management-defect.index',$project->id )}}">
			                <div class="btn btn-info btn-md header-btn" >{{{ trans('siteManagementDefect.back') }}}</div>
			            </a>
			        </div>
			    </fieldset>
			</div>
		</div>
	    {{ Form::close()}}

    @endif

<!-- Response Details End -->

@else

	@if(PCK\SiteManagement\SiteManagementDefect::isDefectAssignedContractor($user,$form_id))

		<div class="row">
		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-12">
		    <!-- Widget ID (each widget will need unique ID)-->
		    <div class="jarviswidget">
		        <header>
		            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
		             <h2>{{{ trans('siteManagementDefect.corrective-action') }}}</h2> 
		        </header>

		        <!-- widget div-->
		        <div>
		            <!-- widget content -->
		            <div class="widget-body no-padding">
		                 {{ Form::open(array('class'=>'smart-form','route' => array('site-management-defect.postReplyMCAR', $project->id, $form_id))) }}
		                     <fieldset id="form">
		                        <h2><strong>{{{ trans('siteManagementDefect.corrective-action') }}}</strong></h2>  
		                        <section>
		                            <label for="cause">{{{ trans('siteManagementDefect.cause') }}}</label>
		                            <textarea class="form-control" rows="5" name="cause">{{{Input::old('cause')}}}</textarea>
		                            {{ $errors->first('cause', '<em class="invalid">:message</em>') }}
		                        </section>
		                        <section>
		                            <label for="action">{{{ trans('siteManagementDefect.action') }}}</label>
		                            <textarea class="form-control" rows="5" name="action">{{{Input::old('action')}}}</textarea>
		                            {{ $errors->first('action', '<em class="invalid">:message</em>') }}
		                        </section>
								<section>
									<label for="corrective">{{{ trans('siteManagementDefect.corrective') }}}</label>&nbsp;&nbsp;
									<label><input type="radio" name="applicable" value="2" data-action="applicable_yes">&nbsp;&nbsp;{{{ trans('siteManagementDefect.yes') }}}</label>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<label><input type="radio" name="applicable" value="3" data-action="applicable_no">&nbsp;&nbsp;{{{ trans('siteManagementDefect.no') }}}</label>
									{{ $errors->first('applicable', '<em class="invalid">:message</em>') }}
								</section>
		                        <section style="display:none;" id="corrective">
		                           <textarea class="form-control" rows="5" name="corrective">{{{Input::old('corrective')}}}</textarea>
		                        </section>
		                        <section>
		                           <label for="commitment_date">{{{ trans('siteManagementDefect.commit-date') }}}</label>
		                           {{ Form::text('commitment_date', Input::old('commitment_date') ? Input::old('commitment_date') : \Carbon\Carbon::now($project->timezone)->addDays(7)->format('d-M-Y'), array('class' => 'datetimepicker')) }}
		                           {{ $errors->first('commitment_date', '<em class="invalid">:message</em>') }}
		                        </section>
		                    </fieldset>
		                    <footer>
		                        {{ Form::submit(trans('siteManagementDefect.submit'), array('class' => 'btn btn-default', 'name' => 'Submit')) }}
		                        {{ link_to_route('site-management-defect.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
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

	@endif

@endif

@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });
        $(document).ready(function () {

            $('button[type=submit]').on('click', function(){
		        app_progressBar.toggle();
		        app_progressBar.maxOut();

		    });

		    $('[data-action=applicable_yes]').on('click', function(){
		       
		    	$('#corrective').show();
		    });

		    $('[data-action=applicable_no]').on('click', function(){
		       
		    	$('#corrective').hide();
		    });
        });
    </script>
@endsection