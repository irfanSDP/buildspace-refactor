@extends('unauthenticated_forms.base')

@section('content')
<header id="header" class="logo-header">
    <div id="logo-group">
        <a href="{{ route('home.index') }}" id="logo" class="d-flex">
            @if(file_exists(public_path('img/company-logo.png')))
                <img src="{{ asset('img/company-logo.png') }}" alt="{{{ \PCK\MyCompanyProfiles\MyCompanyProfile::all()->first()->name }}}">
            @else
                <img src="{{ asset('img/buildspace-login-logo.png') }}" alt="BuildSpace eProject">
            @endif
        </a>
    </div>
</header>
<div id="content">
    @include('layout.partials.flash_message')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-comments"></i> {{{ trans('tenders.tenderInterview') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <div>
                    <div class="widget-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.company') }}:</dt>
                                    <dd>{{{ $rfpInterviewConsultant->company->name }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('companies.referenceNo') }}:</dt>
                                    <dd>{{{ $rfpInterviewConsultant->company->reference_no }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-4 col-md-4 col-lg-4">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('general.time') }}:</dt>
                                    <dd>{{{ Carbon\Carbon::parse($consultantManagementContract->getContractTimeZoneTime($rfpInterviewConsultant->interview_timestamp))->format('d-M-Y H:i:s') }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('general.status') }}:</dt>
                                    <dd>{{{ $rfpInterviewConsultant->getStatusText() }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection