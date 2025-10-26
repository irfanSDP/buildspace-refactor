@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('companies.myCompany') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <span class="widget-icon"> <i class="fa fa-fw fa-university"></i> </span>
                <h2>{{ trans('companies.myCompanyDetails') }}</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    
                    <div class="smart-form">
                        <fieldset>
                            <section>
                                <label class="label">{{{ trans('companies.companyName') }}}:</label>
                                <label class="input">{{{ $company->name }}}</label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('companies.address') }}}:</label>
                                <label class="textarea">
                                    @if(!empty($company->address))
                                    {{ nl2br($company->address) }}
                                    @endif
                                </label>
                            </section>

                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.country') }}}:</label>
                                    <label class="input">{{{ $company->country->country }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.state') }}}:</label>
                                    <label class="input">{{{ $company->state->name }}}</label>
                                </section>
                                <section class="col col-3"></section>
                                <section class="col col-3"></section>
                            </div>
                        </fieldset>

                        <fieldset>
                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{ trans('companies.referenceNo') }}:</label>
                                    <label class="select">{{{ $company->reference_no or 'Not Available' }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{ trans('companies.taxRegistrationNumber') }}:</label>
                                    <label class="select">{{{ $company->tax_registration_no or 'Not Available' }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{ trans('companies.contractGroupCategory') }}:</label>
                                    <label class="select">{{{ $company->contractGroupCategory->name }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{ trans('companies.mainContact') }}:</label>
                                    <label class="select">{{{ $company->main_contact }}}</label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.email') }}}:</label>
                                    <label class="input">{{{ $company->email }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.telephone') }}}:</label>
                                    <label class="input">{{{ $company->telephone_number }}}</label>
                                </section>
                                <section class="col col-3">
                                    <label class="label">{{{ trans('companies.fax') }}} :</label>
                                    <label class="input">{{{ $company->fax_number }}}</label>
                                </section>
                                <section class="col col-3"></section>
                            </div>

                        </fieldset>

                    </div>

                    @if ($user->isGroupAdmin())
                        <footer class="pull-right" style="padding:6px;">
                            {{ html_entity_decode(link_to_route('companies.users', '<i class="fa fa-fw fa-users"></i> '.trans('companies.viewOrAddUsers'), array($company->id), array('class' => 'btn btn-success'))) }}
                            @if ($canEditCompanyDetails)
                            {{ html_entity_decode(link_to_route('companies.edit', '<i class="fa fa-fw fa-edit"></i> '.trans('companies.editCompanyDetails'), array($company->id), array('class' => 'btn btn-primary'))) }}
                            @endif
                        </footer>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection