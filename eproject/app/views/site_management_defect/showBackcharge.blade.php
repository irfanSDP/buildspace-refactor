@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.submitted-defect') }}}</li>
    </ol>

@endsection

@section('content')

<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

            <h2>{{{ trans('siteManagementDefect.backcharge-details') }}}</h2>
        </header>

        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                <div class="smart-form">
                    <fieldset>
                        <section>
                            <label class="label">{{{ trans('siteManagementDefect.machinery') }}}&#58;</label>
                            <label class="input">
                                {{{$project->modified_currency_code}}}&nbsp;{{{$defectBackchargeDetail->machinery}}}
                            </label>
                        </section>

                        <section>
                            <label class="label">{{{ trans('siteManagementDefect.material') }}}&#58;</label>
                            <label class="input">
                                {{{$project->modified_currency_code}}}&nbsp;{{{$defectBackchargeDetail->material}}}
                            </label>
                        </section>

                        <section>
                            <label class="label">{{{ trans('siteManagementDefect.labour') }}}&#58;</label>
                            <label class="input">
                                {{{$project->modified_currency_code}}}&nbsp;{{{$defectBackchargeDetail->labour}}}
                            </label>
                        </section>

                         <section>
                            <label class="label">{{{ trans('siteManagementDefect.total') }}}&#58;</label>
                            <label class="input">
                                {{{$project->modified_currency_code}}}&nbsp;{{{$defectBackchargeDetail->total}}}
                            </label>
                        </section>

                        <section>
                            <label class="label">{{{ trans('siteManagementDefect.date-submitted') }}}&#58;</label>
                            <label class="input">
                                {{{$project->getProjectTimeZoneTime($defectBackchargeDetail->created_at)}}}
                            </label>
                        </section>

                        <section>
                            <label class="label">{{{ trans('siteManagementDefect.submitted-by') }}}&#58;</label>
                            <label class="input">
                                {{{$defectBackchargeDetail->user->name}}}
                            </label>
                        </section>
                    </fieldset>

                    @if(\PCK\Verifier\Verifier::isCurrentVerifier($user, $defectBackchargeDetail))
                        <footer>
                            @include('verifiers.verifier_status_overview')
                            @include('verifiers.approvalForm', array('object' => $defectBackchargeDetail))
                        </footer>
                    @endif
                </div>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
</article>

@endsection

