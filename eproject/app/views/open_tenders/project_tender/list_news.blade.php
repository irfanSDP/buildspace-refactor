@extends('layout.metronic.main')

@section('css')
@endsection

@section('title')
	<h2 class="text-white font-weight-bold my-2 mr-5">{{ trans('projectOpenTenderBM.tender') }}</h2>
@endsection

@section('breadcrumb')
	<div class="d-flex align-items-center font-weight-bold my-2">
		<!--begin::Item-->
		<a href="#" class="opacity-75 hover-opacity-100">
			<i class="flaticon2-shelter text-white icon-1x"></i>
		</a>
		<!--end::Item-->
		<!--begin::Item-->
		<span class="label label-dot label-sm bg-white opacity-75 mx-3"></span>
		<a href="" class="text-white text-hover-white opacity-75 hover-opacity-100">{{ trans('projectOpenTenderBM.utama') }}</a>
		<!--end::Item-->
		<!--begin::Item-->
		<span class="label label-dot label-sm bg-white opacity-75 mx-3"></span>
		<a href="" class="text-white text-hover-white opacity-75 hover-opacity-100">{{ trans('projectOpenTenderBM.berita') }}</a>
		<!--end::Item-->
	</div>
@endsection

@section('content')
	<div class="d-flex flex-column-fluid">
		<!--begin::Container-->
		<div class="container">
			<!--begin::Card-->
			<div class="card card-custom gutter-b">
				<!--begin::Header-->
				<div class="card-header border-0 pt-5">
					<h3 class="card-title align-items-start flex-column">
						<span class="card-label font-weight-bolder text-dark">{{ trans('projectOpenTenderBM.berita_terkini') }}</span>
					</h3>
				</div>
				<!--end::Header-->
				<!--begin::Body-->
				<div class="card-body p-5">
					<!--begin: Datatable-->
					<div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable_news"></div>
					<!--end: Datatable-->
				</div>
				<!--end::Body-->
			</div>
			<!--end::Card-->
			<!--end::Main-->
		</div>
		<!--end::Container-->
	</div>
@endsection

@section('js')
	<script src="{{ asset('metronic/js/pages/crud/ktdatatable/base/data-ajax-news.js') }}"></script>
@endsection