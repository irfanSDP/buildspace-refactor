@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('open_tender_banners.index', trans('openTenderBanners.banners')) }}</li>
        <li>{{{trans('openTenderBanners.edit_banner')}}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{ trans('openTenderBanners.edit_news') }}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
					{{ Form::model($id, ['route' => ['open_tender_banners.update', $id], 'class' => 'smart-form', 'method' => 'PUT', 'id' => 'edit-form', 'files' => true]) }}
						@include('open_tender_banners.partials.openTenderBannersForm')
						<footer>
							{{ link_to_route('open_tender_banners.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
							{{ Form::submit(trans('forms.edit'), array('class' => 'btn btn-primary')) }}
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