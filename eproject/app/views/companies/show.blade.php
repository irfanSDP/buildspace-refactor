@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('companies.company') }}</li>
    </ol>
@endsection

@section('content')
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
        @include('layout.partials.flash_message')

        <div class="jarviswidget jarviswidget-sortable">
            <header role="heading">
                <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

                <h2>{{{ $company->name }}} {{ trans('companies.details') }}</h2>
            </header>

            <!-- widget div-->
            <div role="content">
                <!-- widget content -->
                <div class="widget-body no-padding">
                    <div class="smart-form">
                        <fieldset>
                            <h5>{{ trans('companies.generalInformation') }}</h5>
                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('companies.companyName') }}:</dt>
                                        <dd>
                                            {{{ $company->name }}}
                                        </dd>

                                        <dt>{{ trans('companies.address') }}:</dt>
                                        <dd>
                                            @if(!empty($company->address))
                                                {{ nl2br($company->address) }}
                                            @endif
                                        </dd>

                                        <dt>{{ trans('companies.mainContact') }}:</dt>
                                        <dd>
                                            {{{ $company->main_contact }}}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <br/>

                            <h5>{{ trans('companies.contactInformation') }}</h5>
                            <hr class="simple"/>
                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('companies.email') }}:</dt>
                                        <dd>{{{ $company->email }}}</dd>

                                        <dt>{{ trans('companies.telephone') }}:</dt>
                                        <dd>{{{ $company->telephone_number }}}</dd>

                                        <dt>{{ trans('companies.fax') }}:</dt>
                                        <dd>{{{ $company->fax_number }}}</dd>
                                    </dl>
                                </div>
                            </div>
                            <br/>
                            <br/>

                            @if(!$company->contractor)
                                *{{ trans('companies.companyHasNoContractorInfo', array('companyName' => $company->name)) }}
                            @endif

                            <footer>
                                <a href="{{ URL::previous() }}" class="btn btn-default pull-right">{{ trans('forms.back') }}</a>
                            </footer>
                        </fieldset>
                    </div>
                </div>
                <!-- end widget content -->
            </div>
            <!-- end widget div -->
        </div>
    </article>
@endsection