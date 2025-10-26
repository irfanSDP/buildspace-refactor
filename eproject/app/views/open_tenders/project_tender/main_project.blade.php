@extends('layout.metronic.main')

@section('css')
	<style>
		.h-250 {
			max-height: 200px;
		}

		.limited-text {
			display: -webkit-box;
			-webkit-line-clamp: 2; /* Number of lines to show */
			-webkit-box-orient: vertical;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: normal;
		}
	</style>
@endsection

@section('title')
	<h2 class="text-white font-weight-bold my-2 mr-5">{{ trans('projectOpenTenderBM.utama') }}</h2>
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
		<a href="" class="text-white text-hover-white opacity-75 hover-opacity-100">{{ trans('projectOpenTenderBM.kemaskiniterkini') }}</a>
		<!--end::Item-->
	</div>
@endsection

@section('content')
	<div class="d-flex flex-column-fluid">
		<!--begin::Container-->
		<div class="container">
			<!--begin::Dashboard-->
			<!--begin::Row-->
			<div class="row">
				<div class="col-lg-7 col-xxl-8">
					<div class="card card-custom gutter-b card-stretch">
						<div class="card-body py-5">
							<div id="kt_carousel_1" class="carousel carousel-custom slide" data-ride="carousel" data-interval="8000">
								<!--begin::Heading-->
								<!--begin::Carousel Indicators-->
								<ol class="carousel-indicators carousel-indicators-bullet carousel-indicators-active-primary">
									@foreach ($openTenderBanners as $index => $banner)
										<li data-target="#kt_carousel_1" data-slide-to="{{ $index }}" class="ms-1 {{ $index === 0 ? 'active' : '' }}"></li>
									@endforeach
								</ol>
								<!--end::Carousel Indicators-->
								<!--end::Heading-->

								<!--begin::Carousel-->
								<div class="carousel-inner rounded-lg">
									@foreach ($openTenderBanners as $index => $banner)
										<?php
											if (!empty($banner->image))
											{
												if (file_exists(public_path('/upload/banner/'.$banner->id.'/'.$banner->image))) {
													$imageAttributes['path'] = asset('/upload/banner/'.$banner->id.'/'.$banner->image);
												}
											}
										?>
										@if(isset($imageAttributes))
											<!--begin::Item-->
											<div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
												<img src="{{ $imageAttributes['path'] }}" class="d-block w-100 h-250"  alt="banner">
											</div>
											<!--end::Item-->
										@endif
									@endforeach
								</div>
								<!--end::Carousel-->
							</div>
						</div>
						<!--begin::Card Register-->
						<div class="card-body bg-light-warning">
							<h4 class="text-dark-50 font-weight-bolder">Dapatkan maklumat tender dan sebut harga terkini!</h4>
							<p class="text-dark-50 font-weight-bold mt-3">Pernah mendaftar dengan Sistem Tender Online?</p>
							<div class="btn-group" role="group" aria-label="Basic example">
								<a href="{{ route('register') }}" class="btn btn-primary font-weight-bolder">Daftar Syarikat</a>
							</div>
						</div>
						<!--end::Card Register-->
					</div>
				</div>
				<div class="col-lg-5 col-xxl-4">
					<!--begin::Tiles Widget 1-->
					<div class="card card-custom gutter-b card-stretch">
						<!--begin::Header-->
						<div class="card-header ribbon ribbon-right">
							<div class="ribbon-target bg-primary" style="top: 10px; right: -2px;">
								<a href="{{ route('open_tenders.list_news') }}" class="text-white">{{ trans('projectOpenTenderBM.lihat_semua') }}</a>
							</div>
							<div class="d-flex align-items-center">
								<!--begin::Symbol-->
								<div class="symbol symbol-45 symbol-light mr-5">
									<span class="symbol-label">
										<span class="svg-icon svg-icon-lg svg-icon-primary">
											<!--begin::Svg Icon | path:assets/media/svg/icons/General/Clipboard.svg-->
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
												<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
													<rect x="0" y="0" width="24" height="24"></rect>
													<path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" fill="#000000" opacity="0.3"></path>
													<path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" fill="#000000"></path>
													<rect fill="#000000" opacity="0.3" x="7" y="10" width="5" height="2" rx="1"></rect>
													<rect fill="#000000" opacity="0.3" x="7" y="14" width="9" height="2" rx="1"></rect>
												</g>
											</svg>
											<!--end::Svg Icon-->
										</span>
									</span>
								</div>
								<!--end::Symbol-->
								<!--begin::Info-->
								<h3 class="card-title">{{ trans('projectOpenTenderBM.berita_terkini') }}</h3>
								<!--end::Info-->
							</div>
						</div>
						<!--end::Header-->
						<!--begin::Body-->
						<div class="card-body d-flex flex-column px-0">
							<!--begin::Items-->
							<div class="flex-grow-1 card-spacer-x">
								<div class="scroll scroll-pull" data-scroll="true" data-wheel-propagation="true" style="height: 350px">
									@foreach ($openTenderNews as $news)
											<?php
											$created_at =  \Carbon\Carbon::parse($news->created_at);
											$created_at = $created_at->format('d M Y');
											?>
												<!--begin::Item-->
										<a href="{{ route('open_tenders.detail_news', $news->id) }}" class="btn btn-light border rounded text-justify mb-5 w-100">
											<span class="font-size-sm text-muted font-weight-bold mt-1">{{$news->subsidiary->name}}</span>
											</br>
											<span class="font-size-h6 text-dark-75 text-hover-primary font-weight-bolder limited-text">{{$news->description}}</span>
											</br>
											<span class="label font-weight-bold label-lg label-light-primary label-inline">{{$created_at}}</span>
										</a>
										<!--end::Item-->
									@endforeach
								</div>
							</div>
							<!--end::Items-->
						</div>
						<!--end::Body-->
					</div>
					<!--end::Tiles Widget 1-->
				</div>
			</div>
			<!--end::Row-->

			<!--begin::Card-->
			<div class="card card-custom">
				<div class="card-header flex-wrap border-0 pt-6 pb-0">
					<div class="card-title">
						<h3 class="card-label">{{ trans('projectOpenTenderBM.tenderDanSebutHarga')}}
							<!-- <span class="d-block text-muted pt-2 font-size-sm">Sorting &amp; pagination remote datasource</span> -->
						</h3>
					</div>
					<div class="card-toolbar">
					</div>
				</div>
				<div class="card-body">
					<!--begin::Search Form-->
					<div class="mb-7">
						<div class="row align-items-center">
							<div class="col-lg-10 col-xl-11">
								<div class="row align-items-center">
									<div class="col-md-4 my-2 my-md-0">
										<div class="input-icon">
											<input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query" />
											<span>
												<i class="flaticon2-search-1 text-muted"></i>
											</span>
										</div>
									</div>
								</div>
							</div>
							<!-- <div class="col-lg-2 col-xl-1 mt-5 mt-lg-0">
								<div class="dropdown dropdown-inline ml-2" data-toggle="tooltip" title="" data-placement="top" data-original-title="Quick actions">
									<a href="#" class="btn btn-primary px-6 font-weight-bold" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Pilih</a>
									<div class="dropdown-menu p-0 m-0 dropdown-menu-md dropdown-menu-right">
										<ul class="navi navi-hover py-5">
											<li class="navi-item">
												<a href="#" class="navi-link">
													<span class="navi-icon">
														<i class="fa-solid fa-folder-open"></i>
													</span>
													<span class="navi-text">{{ trans('projectOpenTenderBM.semua') }}</span>
												</a>
											</li>
											<li class="navi-item">
												<a href="#" class="navi-link">
													<span class="navi-icon">
														<i class="fa-solid fa-file-lines"></i>
													</span>
													<span class="navi-text">{{ trans('projectOpenTenderBM.tnder') }}</span>
												</a>
											</li>
											<li class="navi-item">
												<a href="#" class="navi-link">
													<span class="navi-icon">
														<i class="fa-solid fa-hand-holding-dollar"></i>
													</span>
													<span class="navi-text">{{ trans('projectOpenTenderBM.sebutharga') }}</span>
												</a>
											</li>
										</ul>
									</div>
								</div>
							</div> -->
						</div>
					</div>
					<!--end::Search Form-->
					<!--begin: Datatable-->
					<div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
					<!--end: Datatable-->
				</div>
			</div>
			<!--end::Card-->
			<!--end::Dashboard-->
		</div>
		<!--end::Container-->
	</div>
@endsection

@section('js')
	<script src="{{ asset('metronic/js/pages/crud/ktdatatable/base/data-ajax-main.js') }}"></script>
@endsection