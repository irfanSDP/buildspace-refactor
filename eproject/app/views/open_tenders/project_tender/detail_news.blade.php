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
						<span class="text-muted mt-3 font-weight-bold font-size-sm"></span>
					</h3>
				</div>
				<!--end::Header-->
				<!--begin::Body-->
				<div class="card-body pt-2 pb-0 mt-n3">
					<!--begin::Table-->
					<?php
					use Carbon\Carbon;
					$created_at =  Carbon::parse($openTenderNews->created_at);
					$created_at = $created_at->format('d M Y');
					?>
					<div class="table-responsive">
						<table class="table table-bordered">
							<tbody>
							<tr>
								<td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.jabatan') }}</td>
								<td class="text-dark-50 text-left align-middle pb-6">{{ $openTenderNews->subsidiary->name }}</td>
							</tr>
							<tr>
								<td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.berita') }}</td>
								<td class="text-dark-50 text-left align-middle pb-6">{{ $openTenderNews->description }}</td>
							</tr>
							<tr>
								<td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.status') }}</td>
								<td class="text-dark-50 text-left align-middle pb-6">
									@if ($openTenderNews->status == 1)
										{{ trans('projectOpenTenderBM.Aktif') }}
									@else
										{{ trans('projectOpenTenderBM.TidakAktif') }}
									@endif
								</td>
							</tr>
							<tr>
								<td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tarikh') }}</td>
								<td class="text-dark-50 text-left align-middle pb-6">{{ $created_at }}</td>
							</tr>
							</tbody>
						</table>
					</div>
					<!--end::Table-->
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
@endsection