@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('costData', trans('costData.costData')) }}</li>
        <li>{{{ $costData->name }}}</li>
    </ol>
@endsection

@section('content')


    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">

        <div class="jarviswidget jarviswidget-sortable">
            <header role="heading">
                <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

                <h2>{{{ $costData->name }}}</h2>
            </header>

            <!-- widget div-->
            <div role="content">
                <!-- widget content -->
                <div class="widget-body no-padding">
                    <div class="smart-form">
                        <fieldset>
                            <h5>{{ trans('costData.details') }}</h5>
                            <hr class="simple"/>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <dl class="dl-horizontal no-margin">
                                        <dt>{{ trans('costData.masterCostData') }}:</dt>
                                        <dd>
                                            {{{ $costData->master->name }}}
                                        </dd>

                                        <dt>{{ trans('subsidiaries.subsidiary') }}:</dt>
                                        <dd>
                                            {{{ $costData->getSubsidiary()->name }}}
                                        </dd>

                                        <dt>{{ trans('costData.type') }}:</dt>
                                        <dd>
                                            {{{ $costData->type->name }}}
                                        </dd>

                                        <dt>{{ trans('projects.country') }}:</dt>
                                        <dd>
                                            {{{ $costData->region->country }}}
                                        </dd>

                                        <dt>{{ trans('projects.state') }}:</dt>
                                        <dd>
                                            {{{ $costData->subregion->name }}}
                                        </dd>

                                        <dt>{{ trans('projects.currency') }}:</dt>
                                        <dd>
                                            {{{ $costData->currency->currency_code }}}
                                        </dd>

                                        <dt>{{ trans('costData.tenderYear') }}:</dt>
                                        <dd>
                                            {{{ \Carbon\Carbon::parse($costData->tender_date)->format('Y') }}}
                                        </dd>

                                        <dt>{{ trans('costData.awardYear') }}:</dt>
                                        <dd>
                                            {{{ \Carbon\Carbon::parse($costData->award_date)->format('Y') }}}
                                        </dd>

                                        <dt>{{ trans('general.createdBy') }}:</dt>
                                        <dd>
                                            {{{ $costData->eprojectCreatedBy->name }}}
                                        </dd>

                                        <dt>{{ trans('general.createdAt') }}:</dt>
                                        <dd>
                                            {{{ \Carbon\Carbon::parse($costData->created_at)->format(\Config::get('dates.created_at')) }}}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            <br/>
                            <br/>

                            <h5>{{ trans('projects.projects') }}</h5>
                            <div class="padded">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-middle text-center squeeze" style="width:24px;">{{ trans('general.no') }}</th>
                                            <th class="text-middle text-left">{{ trans('projects.project') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @if($costData->e_project_projects->isEmpty())
                                        <tr>
                                            <td class="text-middle text-center" colspan="2">
                                                <div class="alert alert-warning fade in">{{ trans('general.noDataAvailable') }}</div>
                                            </td>
                                        </tr>
                                    @else
                                        <?php $count = 0; ?>
                                        @foreach($costData->e_project_projects as $project)
                                            <tr>
                                                <td class="text-middle text-center">{{{ ++$count }}}</td>
                                                <td class="text-middle text-left">{{{ $project->title }}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <br/>
                            <br/>

                            <h5>{{ trans('costData.notes') }}</h5>
                            <hr class="simple"/>
                            <div class="well padded">
                                {{ $costData->getEProjectCostData()->notes }}
                            </div>
                            <br/>
                            <br/>

                            <footer>
                                <a href="{{ route('costData') }}" class="btn btn-default pull-right">{{ trans('forms.back') }}</a>
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