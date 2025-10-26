@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenders.contractors') }}</li>
    </ol>
@endsection

@section('content')
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
        @include('layout.partials.flash_message')

        <div class="jarviswidget jarviswidget-sortable">
            <header role="heading">
                <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

                <h2>{{{ $company->name }}} ({{ trans('companies.contractorDetails') }})</h2>
            </header>

            <!-- widget div-->
            <div role="content">
                <!-- widget content -->
                <div class="widget-body">
                    @if($contractor)
                        <div class="smart-form">
                        <fieldset>
                            <h5>{{ trans('companies.generalInformation') }}</h5>
                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('companies.companyName') }}:</dt>
                                        <dd>
                                            {{{ $contractor->company->name }}}
                                        </dd>

                                        <dt>{{ trans('companies.address') }}:</dt>
                                        <dd>
                                            {{{ $contractor->company->address }}}
                                        </dd>

                                        <dt>{{ trans('companies.mainContact') }}:</dt>
                                        <dd>
                                            {{{ $contractor->company->main_contact }}}
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
                                        <dd>{{{ $contractor->company->email }}}</dd>

                                        <dt>{{ trans('companies.telephone') }}:</dt>
                                        <dd>{{{ $contractor->company->telephone_number }}}</dd>

                                        <dt>{{ trans('companies.fax') }}:</dt>
                                        <dd>{{{ $contractor->company->fax_number }}}</dd>
                                    </dl>
                                </div>
                            </div>
                            <br/>

                            <h5>{{ trans('projects.mainInformation') }}</h5>
                            <hr class="simple"/>
                            <div class="row">
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('tenders.typeOfWork') }}:</dt>
                                        <dd>
                                            <?php $categoryCounter = 1; ?>
                                            @foreach($contractor->workCategories as $category)
                                                <?php echo $categoryCounter ++ ?>
                                                {{{ '. '.$category->name }}}
                                                <br/>
                                            @endforeach
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col col-lg-6">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('tenders.subCategory') }}:</dt>
                                        <dd>
                                            <?php $subcategoryCounter = 1; ?>
                                            @foreach($contractor->workSubcategories as $subcategory)
                                                <?php echo $subcategoryCounter ++ ?>
                                                {{{ '. '.$subcategory->name }}}
                                                <br/>
                                            @endforeach
                                        </dd>
                                    </dl>
                                </div>

                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">

                                        <dt>{{ trans('tenders.subCategory') }}:</dt>
                                        <dd>
                                            {{{ $contractor->currentCPEGrade->grade }}}
                                        </dd>

                                        <dt>{{ trans('tenders.previousCPE') }}:</dt>
                                        <dd>
                                            {{{ $contractor->previousCPEGrade->grade }}}
                                        </dd>

                                        <dt>{{ trans('tenders.registrationStatus') }}:</dt>
                                        <dd>
                                            {{{ $contractor->registrationStatus->name }}}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <br/>

                            <h5>{{ trans('general.additionalInformation') }}</h5>
                            <hr class="simple"/>
                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('tenders.jobLimit') }}:</dt>
                                        <dd>
                                            {{{ \PCK\Contractors\Contractor::getJobLimitSymbolSymbolById($contractor->job_limit_sign).' '.(isset($contractor->job_limit_number)? number_format($contractor->job_limit_number, 2, '.', ',') : 0) }}}
                                        </dd>

                                        <dt>CIDB Category:</dt>
                                        <dd>
                                            @if(trim($contractor->cidb_category) != '')
                                                {{{ $contractor->cidb_category }}}
                                            @else
                                                -
                                            @endif
                                        </dd>

                                        <dt>{{ trans('general.registeredDate') }}:</dt>
                                        <dd>
                                            {{{ isset($contractor->registered_date) ? \Carbon\Carbon::parse($contractor->registered_date)->format(\Config::get('dates.submission_date_formatting')) : '-' }}}
                                        </dd>

                                        <dt>{{ trans('general.remarks') }}:</dt>
                                        <dd>
                                            @if(trim($contractor->remarks) != '')
                                                {{{ $contractor->remarks }}}
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            <footer>
                                <a href="{{ URL::previous() }}" class="btn btn-default pull-right">{{ trans('forms.back') }}</a>
                            </footer>
                        </fieldset>
                    </div>
                    @else
                        <div class="well">
                            *{{ trans('companies.companyHasNoContractorInfo', array('companyName' => $company->name)) }}
                        </div>
                    @endif
                </div>
                <!-- end widget content -->
            </div>
            <!-- end widget div -->
        </div>
    </article>
@endsection