@extends('layout.main')

@section('css')
	<style>
		#ebidding-info .label,
		#ebidding-info span {
			font-size: 13px;
		}
		#ebidding-info .label {
			color: #666;
		}
		#ebidding-info span {
			color: #333;
		}
	</style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
		<li>{{ trans('eBidding.create_ebidding') }}</li>
    </ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
			<h1 class="page-title txt-color-blueDark">
				{{ trans('eBidding.create_ebidding') }}
			</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget">
				<header>
					<h2>{{ trans('eBidding.ebidding_detail') }}</h2>
				</header>
				<div>
					<div class="widget-body no-padding">
						@include('open_tenders.e_biddings.partials.eBiddingInfo')

						{{ Form::open(['route' => ['projects.e_bidding.store', $project->id], 'class' => 'smart-form', 'id' => 'add-form', 'files' => true]) }}
						@include('open_tenders.e_biddings.partials.eBiddingForm')
						<footer>
							{{ Form::submit(trans('forms.next'), array('class' => 'btn btn-primary')) }}
							{{ link_to_route('projects.openTender.show', trans('forms.back'), array($project->id, $tenderId), array('class' => 'btn btn-default')) }}
						</footer>
						{{ Form::close() }}
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('js')
	<script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
	@include('open_tenders.e_biddings.partials.eBiddingFormScript')
@endsection

@section('inline-js')
	$(document).ready(function() {
		$('#add-form').validate({
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        });
	});
@endsection