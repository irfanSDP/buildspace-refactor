@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('eBidding.create_ebidding') }}</li>
    </ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('eBidding.create_ebidding') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
                    {{ Form::open(['route' => ['projects.e_bidding.assignVerifier', $project->id], 'class' => 'smart-form', 'id' => 'add-form', 'files' => true]) }}
						<header>
							{{ trans('eBidding.selectverifier') }}
						</header>
						<fieldset>
							<!-- verifier -->
							<div class="row">
								<section class="col col-xs-12 col-md-6 col-lg-3">
									@include('verifiers.select_verifiers', array('modalId' => 'eBiddingVerifierModal'))
								</section>
							</div>
						</fieldset>
						<footer>
							<button type="submit" class="btn btn-primary" id="btnSubmitForApproval" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message="{{trans('general.submitWithoutVerifier')}}"><i class="fa fa-save"></i> {{ trans('forms.assignVerifier') }}</button>
							{{ link_to_route('projects.e_bidding.assignCommittees', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
						</footer>
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('inline-js')
function noVerifier(e){
	var form = $(e.target).closest('form');
	var input = form.find(':input[name="verifiers[]"]').serializeArray();

	return !input.some(function(element){
		return (element.value > 0);
	});
}
function noVerifier(e){
	var form = $(e.target).closest('form');
	var input = form.find(':input[name="verifiers[]"]').serializeArray();

	return !input.some(function(element){
		return (element.value > 0);
	});
}
@endsection