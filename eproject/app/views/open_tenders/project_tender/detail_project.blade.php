@extends('layout.metronic.main')

@section('css')
@endsection

@section('title')
    <h2 class="text-white font-weight-bold my-2 mr-5">{{ trans('projectOpenTenderBM.tender') }}</h2>
@endsection
<?php use Carbon\Carbon; ?>
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
        <a href="" class="text-white text-hover-white opacity-75 hover-opacity-100">{{ $info['open_tender_number'] }}</a>
        <!--end::Item-->
    </div>
@endsection

@section('content')
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Main-->
            <div class="card card-custom gutter-b mb-4">
                <div class="card-body p-0 d-flex">
                    <div class="d-flex align-items-start justify-content-start flex-grow-1 bg-light-warning p-8 card-rounded flex-grow-1 position-relative">
                        <div class="d-flex flex-column align-items-start flex-grow-1 h-100">
                            <div class="p-1 flex-grow-1">
                                <h5 class="text-warning font-weight-bold">{{ trans('projectOpenTenderBM.info') }} <a href="#" class="font-weight-bold">{{ trans('projectOpenTenderBM.daftarsyarikat') }}</a></h5>
                            </div>
                        </div>
                        <div class="position-absolute right-0 bottom-0 mr-5 overflow-hidden">
                            <img src="{{ asset('metronic/media/svg/humans/custom-13.svg') }}" class="max-h-200px max-h-xl-275px mb-n20" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <!--begin::Nav Panel -->
            <div class="card card-custom gutter-b mb-4">
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Nav Tabs-->
                    <ul class="dashboard-tabs nav nav-pills nav-danger row row-paddingless m-0 p-0 flex-column flex-sm-row" role="tablist">
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center active" data-toggle="pill" href="#kt_tab_pane_1">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"></rect>
                                                <path d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z" fill="#000000"></path>
                                                <rect fill="#000000" opacity="0.3" transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)" x="16.3255682" y="2.94551858" width="3" height="18" rx="1"></rect>
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bold text-center">{{ trans('projectOpenTenderBM.maklumattender') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_2">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <title>Stockholm-icons / Communication / Clipboard-list</title>
                                            <desc>Created with Sketch.</desc>
                                            <defs/>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" fill="#000000" opacity="0.3"/>
                                                <path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" fill="#000000"/>
                                                <rect fill="#000000" opacity="0.3" x="10" y="9" width="7" height="2" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="7" y="9" width="2" height="2" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="7" y="13" width="2" height="2" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="10" y="13" width="7" height="2" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="7" y="17" width="2" height="2" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="10" y="17" width="7" height="2" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center">{{ trans('projectOpenTenderBM.syaratsebutharga') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_3">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <title>Stockholm-icons / Communication / Chat5</title>
                                            <desc>Created with Sketch.</desc>
                                            <defs/>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M21.9999843,15.009808 L22.0249378,15 L22.0249378,19.5857864 C22.0249378,20.1380712 21.5772226,20.5857864 21.0249378,20.5857864 C20.7597213,20.5857864 20.5053674,20.4804296 20.317831,20.2928932 L18.0249378,18 L6,18 C4.34314575,18 3,16.6568542 3,15 L3,6 C3,4.34314575 4.34314575,3 6,3 L19,3 C20.6568542,3 22,4.34314575 22,6 L22,15 C22,15.0032706 21.9999948,15.0065399 21.9999843,15.009808 Z" fill="#000000" opacity="0.3"/>
                                                <path d="M7.5,12 C6.67157288,12 6,11.3284271 6,10.5 C6,9.67157288 6.67157288,9 7.5,9 C8.32842712,9 9,9.67157288 9,10.5 C9,11.3284271 8.32842712,12 7.5,12 Z M12.5,12 C11.6715729,12 11,11.3284271 11,10.5 C11,9.67157288 11.6715729,9 12.5,9 C13.3284271,9 14,9.67157288 14,10.5 C14,11.3284271 13.3284271,12 12.5,12 Z M17.5,12 C16.6715729,12 16,11.3284271 16,10.5 C16,9.67157288 16.6715729,9 17.5,9 C18.3284271,9 19,9.67157288 19,10.5 C19,11.3284271 18.3284271,12 17.5,12 Z" fill="#000000" opacity="0.3"/>
                                            </g>
                                        </svg>
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center">{{ trans('projectOpenTenderBM.taklimat') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_4">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <title>Stockholm-icons / Communication / Chat2</title>
                                            <desc>Created with Sketch.</desc>
                                            <defs/>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <polygon fill="#000000" opacity="0.3" points="5 15 3 21.5 9.5 19.5"/>
                                                <path d="M13.5,21 C8.25329488,21 4,16.7467051 4,11.5 C4,6.25329488 8.25329488,2 13.5,2 C18.7467051,2 23,6.25329488 23,11.5 C23,16.7467051 18.7467051,21 13.5,21 Z M9,8 C8.44771525,8 8,8.44771525 8,9 C8,9.55228475 8.44771525,10 9,10 L18,10 C18.5522847,10 19,9.55228475 19,9 C19,8.44771525 18.5522847,8 18,8 L9,8 Z M9,12 C8.44771525,12 8,12.4477153 8,13 C8,13.5522847 8.44771525,14 9,14 L14,14 C14.5522847,14 15,13.5522847 15,13 C15,12.4477153 14.5522847,12 14,12 L9,12 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center">{{ trans('projectOpenTenderBM.pengumuman') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_5">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Layout/Layout-4-blocks.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"></rect>
                                                <rect fill="#000000" x="4" y="4" width="7" height="7" rx="1.5"></rect>
                                                <path d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z" fill="#000000" opacity="0.3"></path>
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center">{{ trans('projectOpenTenderBM.kodkodbidang') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_6">
                                <span class="nav-icon py-2 w-auto">
                                    <span class="svg-icon svg-icon-2x">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                                <path d="M18,14 C16.3431458,14 15,12.6568542 15,11 C15,9.34314575 16.3431458,8 18,8 C19.6568542,8 21,9.34314575 21,11 C21,12.6568542 19.6568542,14 18,14 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"></path>
                                                <path d="M17.6011961,15.0006174 C21.0077043,15.0378534 23.7891749,16.7601418 23.9984937,20.4 C24.0069246,20.5466056 23.9984937,21 23.4559499,21 L19.6,21 C19.6,18.7490654 18.8562935,16.6718327 17.6011961,15.0006174 Z M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z" fill="#000000" fill-rule="nonzero"></path>
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center text-nowrap">{{ trans('projectOpenTenderBM.pegawaibertanggungjawab') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="nav-item d-flex col-sm flex-grow-1 flex-shrink-0 mr-0 mb-3 mb-lg-0">
                            <a class="nav-link border py-1 d-flex flex-grow-1 rounded flex-column align-items-center" data-toggle="pill" href="#kt_tab_pane_7">
                                    <span class="nav-icon py-2 w-auto">
                                        <span class="svg-icon svg-icon-2x">
                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <title>Stockholm-icons / Files / Uploaded-file</title>
                                                <desc>Created with Sketch.</desc>
                                                <defs/>
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <polygon points="0 0 24 0 24 24 0 24"/>
                                                    <path d="M5.85714286,2 L13.7364114,2 C14.0910962,2 14.4343066,2.12568431 14.7051108,2.35473959 L19.4686994,6.3839416 C19.8056532,6.66894833 20,7.08787823 20,7.52920201 L20,20.0833333 C20,21.8738751 19.9795521,22 18.1428571,22 L5.85714286,22 C4.02044787,22 4,21.8738751 4,20.0833333 L4,3.91666667 C4,2.12612489 4.02044787,2 5.85714286,2 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                    <path d="M8.95128003,13.8153448 L10.9077535,13.8153448 L10.9077535,15.8230161 C10.9077535,16.0991584 11.1316112,16.3230161 11.4077535,16.3230161 L12.4310522,16.3230161 C12.7071946,16.3230161 12.9310522,16.0991584 12.9310522,15.8230161 L12.9310522,13.8153448 L14.8875257,13.8153448 C15.1636681,13.8153448 15.3875257,13.5914871 15.3875257,13.3153448 C15.3875257,13.1970331 15.345572,13.0825545 15.2691225,12.9922598 L12.3009997,9.48659872 C12.1225648,9.27584861 11.8070681,9.24965194 11.596318,9.42808682 C11.5752308,9.44594059 11.5556598,9.46551156 11.5378061,9.48659872 L8.56968321,12.9922598 C8.39124833,13.2030099 8.417445,13.5185067 8.62819511,13.6969416 C8.71848979,13.773391 8.8329684,13.8153448 8.95128003,13.8153448 Z" fill="#000000"/>
                                                </g>
                                            </svg>
                                        </span>
                                    </span>
                                <span class="nav-text font-size-lg py-2 font-weight-bolder text-center">{{ trans('projectOpenTenderBM.dokumen') }}</span>
                            </a>
                        </li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Nav Tabs-->
                </div>
                <!--end::Body-->
            </div>
            <!--begin::Nav Panel -->
            <!--begin::Card-->
            <div class="card card-custom gutter-b">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bolder text-dark">{{ $info['title'] }}</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm">{{ trans('projectOpenTenderBM.sebuthargaoleh') }} {{ $info['open_tender_type'] }}</span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-2 pb-0 mt-n3">
                    <div class="tab-content mt-5" id="myTabTables11">
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade active show" id="kt_tab_pane_1" role="tabpanel" aria-labelledby="kt_tab_pane_1">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.petender') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $info['open_tender_type'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.noTender') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $info['open_tender_number'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tarikhIklan') }}</td>
                                        <?php
                                            $displayDate = Carbon::parse($info['open_tender_date_from'])->format(\Config::get('dates.full_format'));
                                            $toDate = $info['open_tender_date_to'] ? Carbon::parse($info['open_tender_date_from'])->format(\Config::get('dates.full_format')) : null;

                                            if($toDate)
                                            {
                                                $displayDate .= " - {$toDate}";
                                            }
                                        ?>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $displayDate }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tarikhJual') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ Carbon::parse($info['calling_date'])->format(\Config::get('dates.full_format')) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tarikhTutup') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ Carbon::parse($info['closing_date'])->format(\Config::get('dates.full_format')) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tempatHantar') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $info['deliver_address'] }}</td>
                                    </tr>
                                    @if($info["special_permission"])
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tarikhTaklimat') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ Carbon::parse($info['briefing_time'])->format(\Config::get('dates.full_format')) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.alamatTaklimat') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $info['briefing_address'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.kebenaranKhas') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">@if ($info['special_permission']) Ada @else Tiada @endif</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.syarikatBumiputerashj') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">@if ($info['local_company_only']) Ya @else Tidak @endif</td>
                                    </tr>
                                    <tr>
                                        <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.hargaDokumen') }}</td>
                                        <td class="text-dark-50 text-left align-middle pb-6">{{ $info['open_tender_price'] }}</td>
                                    </tr>
                                    @if ($tenderData['tender'])
                                        <tr>
                                            <td class="font-size-lg font-weight-bolder text-dark-75 align-middle pb-6">{{ trans('projectOpenTenderBM.tender') }}</td>
                                            <td class="text-dark-50 text-left align-middle pb-6">
                                                @if (! empty($tenderData['html']))
                                                    {{ $tenderData['html'] }}

                                                    @if ($tenderData['pg'])
                                                        @include('payments.gateway.partials.pg-form-container')
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel" aria-labelledby="kt_tab_pane_2">
                            <!--begin::Card-->
                            <div class="card card-custom card-stretch gutter-b bg-light border">
                                <div class="card-body p-3">
                                    {{ $info['description'] }}
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel" aria-labelledby="kt_tab_pane_3">
                            <!--begin::Card-->
                            <div class="card card-custom gutter-b bg-light border">
                                <div class="card-body">
                                    <!--begin::Title-->
                                    @if($info["special_permission"])
                                        <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                                            <!--begin::User-->
                                            <div class="mr-3">
                                                <!--begin::Briefing-->
                                                <a href="{{$info['googleMapsLink']}}" class="d-flex align-items-center text-dark text-hover-primary font-size-h5 font-weight-bold mr-3">{{ $info['briefing_address'] }}
                                                    <i class="flaticon2-correct text-primary icon-md ml-2"></i></a>
                                                <div class="d-flex flex-wrap my-2">
                                                    <a href="#" class="text-muted text-hover-primary font-weight-bold mr-lg-8 mr-5 mb-lg-0 mb-2">
                                                        <span class="svg-icon svg-icon-md svg-icon-primary mr-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                                <title>Stockholm-icons / Home / Alarm-clock</title>
                                                                <desc>Created with Sketch.</desc>
                                                                <defs/>
                                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                    <rect x="0" y="0" width="24" height="24"/>
                                                                    <path d="M7.14319965,19.3575259 C7.67122143,19.7615175 8.25104409,20.1012165 8.87097532,20.3649307 L7.89205065,22.0604779 C7.61590828,22.5387706 7.00431787,22.7026457 6.52602525,22.4265033 C6.04773263,22.150361 5.88385747,21.5387706 6.15999985,21.0604779 L7.14319965,19.3575259 Z M15.1367085,20.3616573 C15.756345,20.0972995 16.3358198,19.7569961 16.8634386,19.3524415 L17.8320512,21.0301278 C18.1081936,21.5084204 17.9443184,22.1200108 17.4660258,22.3961532 C16.9877332,22.6722956 16.3761428,22.5084204 16.1000004,22.0301278 L15.1367085,20.3616573 Z" fill="#000000"/>
                                                                    <path d="M12,21 C7.581722,21 4,17.418278 4,13 C4,8.581722 7.581722,5 12,5 C16.418278,5 20,8.581722 20,13 C20,17.418278 16.418278,21 12,21 Z M19.068812,3.25407593 L20.8181344,5.00339833 C21.4039208,5.58918477 21.4039208,6.53893224 20.8181344,7.12471868 C20.2323479,7.71050512 19.2826005,7.71050512 18.696814,7.12471868 L16.9474916,5.37539627 C16.3617052,4.78960984 16.3617052,3.83986237 16.9474916,3.25407593 C17.5332781,2.66828949 18.4830255,2.66828949 19.068812,3.25407593 Z M5.29862906,2.88207799 C5.8844155,2.29629155 6.83416297,2.29629155 7.41994941,2.88207799 C8.00573585,3.46786443 8.00573585,4.4176119 7.41994941,5.00339833 L5.29862906,7.12471868 C4.71284263,7.71050512 3.76309516,7.71050512 3.17730872,7.12471868 C2.59152228,6.53893224 2.59152228,5.58918477 3.17730872,5.00339833 L5.29862906,2.88207799 Z" fill="#000000" opacity="0.3"/>
                                                                    <path d="M11.9630156,7.5 L12.0475062,7.5 C12.3043819,7.5 12.5194647,7.69464724 12.5450248,7.95024814 L13,12.5 L16.2480695,14.3560397 C16.403857,14.4450611 16.5,14.6107328 16.5,14.7901613 L16.5,15 C16.5,15.2109164 16.3290185,15.3818979 16.1181021,15.3818979 C16.0841582,15.3818979 16.0503659,15.3773725 16.0176181,15.3684413 L11.3986612,14.1087258 C11.1672824,14.0456225 11.0132986,13.8271186 11.0316926,13.5879956 L11.4644883,7.96165175 C11.4845267,7.70115317 11.7017474,7.5 11.9630156,7.5 Z" fill="#000000"/>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                        {{ Carbon::parse($info['briefing_time'])->format(\Config::get('dates.full_format')) }}
                                                    </a>
                                                </div>
                                                <!--end::Briefing-->
                                            </div>
                                            <!--begin::User-->
                                            <!--begin::Actions-->
                                            <div class="my-lg-0 my-1">
                                                <a href="#" class="btn btn-sm btn-light-warning font-weight-bolder text-uppercase"><i class="fa-solid fa-circle-check mr-2"></i>Wajib Hadir</a>
                                            </div>
                                            <!--end::Actions-->
                                        </div>
                                    @endif
                                    <!--end::Title-->
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_4" role="tabpanel" aria-labelledby="kt_tab_pane_4">
                            <!--begin::Card-->
                            <div class="card card-custom card-stretch gutter-b bg-light border">
                                <div class="card-body p-3">
                                    @foreach ($announcements as $announcement)
                                        <div class="d-flex mb-4">
                                            {{ $announcement['description'] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel" aria-labelledby="kt_tab_pane_5">
                            <!--begin::Card-->
                            <div class="card card-custom gutter-b">
                                <div class="card-body">
                                    @foreach ($grades as $grade)
                                        @if($grade['grade'] != '')
                                            <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-2">
                                                <!--begin::Icon-->
                                                <span class="font-weight-bolder label label-xl label-warning label-inline py-5 mr-5">{{ $grade['grade'] }}</span>
                                                <!--end::Icon-->
                                                <!--begin::Title-->
                                                <div class="d-flex flex-column flex-grow-1 mr-2">
                                                    <!-- <a href="#" class="font-weight-bold text-dark-50 text-hover-primary font-size-lg mb-1">KEUPAYAAN TIDAK MELEBIHI RM200,000.00</a> -->
                                                    <span class="text-muted font-weight-bold">{{ trans('projectOpenTenderBM.gredcidb') }}</span>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                        @endif
                                    @endforeach
                                    @foreach ($codes as $code)
                                        @if($code['code'] != '')
                                            <div class="d-flex align-items-center bg-light-primary rounded p-5 mb-2">
                                                <!--begin::Icon-->
                                                <span class="font-weight-bolder label label-xl label-warning label-inline py-5 mr-5">{{ $code['code'] }}</span>
                                                <!--end::Icon-->
                                                <!--begin::Title-->
                                                <div class="d-flex flex-column flex-grow-1 mr-2">
                                                    <a href="#" class="font-weight-bold text-dark-50 text-hover-primary font-size-lg mb-1">{{ $code['desc'] }}</a>
                                                    <span class="text-muted font-weight-bold">{{ trans('projectOpenTenderBM.bidangkhusus') }}</span>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_6" role="tabpanel" aria-labelledby="kt_tab_pane_6">
                            <!--begin::Card-->
                            <div class="row">
                                @foreach ($pic as $user)
                                    <div class="col-lg-6">
                                        <div class="card card-custom card-custom-half card-stretch gutter-b border">
                                            <div class="card-body">
                                                <!--begin::User-->
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-60 symbol-xxl-100 mr-5 align-self-start align-self-xxl-center">
                                                        <div class="symbol-label" style="background-image: url('{{ asset('metronic/media/users/blank.png') }}')"></div>
                                                        <i class="symbol-badge bg-success"></i>
                                                    </div>
                                                    <div>
                                                        <a href="#" class="font-weight-bold font-size-h5 text-primary">{{ $user['name'] }}</a>
                                                        <div class="text-muted">{{ trans('projectOpenTenderBM.pegawaibertanggungjawab') }}</div>
                                                    </div>
                                                </div>
                                                <!--end::User-->
                                                <!--begin::Contact-->
                                                <div class="pt-8 pb-6">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="font-weight-bold mr-2">{{ trans('projectOpenTenderBM.emel') }}:</span>
                                                        <a href="#" class="text-primary">{{ $user['email'] }}</a>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="font-weight-bold mr-2">{{ trans('projectOpenTenderBM.nombofon') }}:</span>
                                                        <span class="text-warning">{{ $user['phone_number'] }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="font-weight-bold mr-2">{{ trans('projectOpenTenderBM.jabatan') }}:</span>
                                                        <span class="text-warning">{{ $user['department'] }}</span>
                                                    </div>
                                                </div>
                                                <!--end::Contact-->
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Tap pane-->
                        <!--begin::Tap pane-->
                        <div class="tab-pane fade" id="kt_tab_pane_7" role="tabpanel" aria-labelledby="kt_tab_pane_7">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table table-bordered ">
                                    <thead class="text-dark-50 text-center">
                                    <tr>
                                        <th>{{ trans('projectOpenTenderBM.dokumen') }}</th>
                                        <th>{{ trans('projectOpenTenderBM.muatTurun') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="table table-bordered bg-white text-dark-50 text-center">
                                    @foreach($openTenderDocuments as $index => $openTenderDocument)
                                        <tr>
                                            <td>
                                                {{ $openTenderDocument['description'] }}
                                            </td>
                                            <td>
                                                <button 
                                                    type="button" 
                                                    class="btn btn-light openModal" 
                                                    data-index="{{ $index }}"
                                                    data-description="{{ $openTenderDocument['description'] }}">
                                                    Attachments ({{ $openTenderDocument['count'] }}) 
                                                    <i class="flaticon-doc text-primary icon-2x"></i>
                                                </button>

                                                {{-- hidden documents for this row --}}
                                                <div id="documents-{{ $index }}" style="display: none;">
                                                    <ul style="text-align:left;">
                                                        @foreach($openTenderDocument['documents'] as $document)
                                                            <li>
                                                                <a href="{{asset($document['path'])}}" download>{{ $document['filename'] }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Tap pane-->
                    </div>
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".openModal").forEach(button => {
                button.addEventListener("click", function () {
                    const index = this.dataset.index;
                    const description = this.dataset.description;
                    const html = document.getElementById("documents-" + index).innerHTML;

                    Swal.fire({
                        title: description,
                        html: html,
                        confirmButtonText: 'Close'
                    });
                });
            });
        });
    </script>
    @if (($tenderData['pg'] || $tenderData['interest']) && ! empty($tenderData['html']))
        @if ($tenderData['pg'])
            @include('payments.gateway.partials.script-pg-btn')
            <script>
                $(document).ready(function() {
                    initPaymentGatewayBtn();
                });
            </script>
        @endif
        @if ($tenderData['interest'])
            <script>
                $(document).ready(function() {
                    let interestToTenderId = '#interestToTender';
                    let interestToTender = $(interestToTenderId);
                    let interestToTenderModalId = '#interestToTenderModal';
                    let interestToTenderModal = $(interestToTenderModalId);

                    interestToTender.on('click', '.interest', function(e) {
                        $(interestToTenderId+' [data-action=actionYes]').data('url', $(this).data('lnk'));
                        $(interestToTenderModalId).modal('show');
                    });
                    interestToTenderModal.on('click', '[data-action=actionYes]', function(e) {
                        e.preventDefault();
                        let interestToTenderBtn = $(interestToTenderId+' .btn.interest');

                        $.ajax({
                            url: atob($(this).data('url')),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                project_id: interestToTenderBtn.data('pid'),
                                tender_id: interestToTenderBtn.data('tid'),
                                company_id: interestToTenderBtn.data('co')
                            },
                            before: function () {
                                interestToTenderModal.modal('hide');
                            },
                            success: function (response) {
                                location.reload();
                                //notifyMsg(response.success ? 'success' : 'error', response.message, '', true);
                            },
                            error: function (request, status, error) {
                                notifyMsg('error', "{{ trans('errors.anErrorHasOccured', [], 'messages', 'ms') }}")
                            }
                        });
                    });
                });
            </script>
        @endif
    @endif
@endsection