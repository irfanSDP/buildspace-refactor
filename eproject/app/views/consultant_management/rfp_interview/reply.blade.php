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
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-comments"></i> {{{ trans('general.consultantInterview') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <div>
                    <div class="widget-body">
                        <div class="row">
                            <div class="col col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>RFP Title:</dt>
                                    <dd>{{{ $vendorCategoryRfp->vendorCategory->name }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>Interview Title:</dt>
                                    <dd>{{{ $rfpInterview->title }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                        </div>
                        @if(!empty($rfpInterview->details))
                        <div class="row">
                            <div class="col col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('general.details') }}:</dt>
                                    <dd><div class="well">{{ nl2br($rfpInterview->details) }}</div></dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                        </div>
                        @endif
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
                        @if(!empty($rfpInterviewConsultant->remarks))
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('general.remarks') }}:</dt>
                                    <dd><div class="well">{{ nl2br($rfpInterviewConsultant->remarks) }}</div></dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </section>
                        </div>
                        @endif

                        <div id="rfp_interview-reply">
                            <hr class="simple">
                            {{ Form::open(['route' => ['consultant.management.consultant.rfp.interview.reply.store'], 'class' => 'smart-form']) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <strong><i class="fa fa-comment-dots"></i> {{{ trans('forms.reply') }}}</strong>
                                        </div>
                                        <div class="card-body">
                                            <?php
                                            $selectedStatus = Input::old('status');
                                            ?>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                {{ Form::radio('status', PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant::STATUS_ACCEPTED, ($selectedStatus == PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant::STATUS_ACCEPTED), ['id'=>'status_accepted', 'class'=>'custom-control-input']) }}
                                                <label class="custom-control-label" for="status_accepted">{{{ trans('general.accepted') }}}</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                {{ Form::radio('status', PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant::STATUS_DECLINED, ($selectedStatus == PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant::STATUS_DECLINED), ['id'=>'status_declined', 'class'=>'custom-control-input']) }}
                                                <label class="custom-control-label" for="status_declined">{{{ trans('general.declined') }}}</label>
                                            </div>
                                            @if($errors->has('status'))
                                            <label class="input state-error"></label>
                                            {{ $errors->first('status', '<em class="invalid">:message</em>') }}
                                            @endif
                                            <hr class="simple"/>
                    
                                            <div class="well">
                                                <label class="label">{{{ trans('general.remarks') }}} :</label>
                                                <label class="textarea {{{ $errors->has('consultant_remarks') ? 'state-error' : null }}}">
                                                    {{ Form::textarea('consultant_remarks', Input::old('consultant_remarks'), ['autofocus' => 'autofocus', 'rows' => 3]) }}
                                                </label>
                                                {{ $errors->first('consultant_remarks', '<em class="invalid">:message</em>') }}
                                            </div>
                    
                                        </div>
                                    </div>
                    
                                </section>
                            </div>
                            <footer>
                                {{ Form::hidden('id', $rfpInterviewConsultant->id) }}
                                {{ Form::hidden('cid', $user->company_id) }}
                                {{ Form::hidden('token', $interviewToken->token) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.submit'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection