@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('riskRegister.riskRegister') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-exclamation-triangle"></i> {{ trans('riskRegister.riskRegister') }}
            </h1>
        </div>

        @if(PCK\RiskRegister\RiskRegister::canPostRisk($currentUser, $project))
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="{{route('riskRegister.create', array($project->id))}}" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{ trans('riskRegister.registerNew') }}
                </a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{ trans('riskRegister.riskRegister') }} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped table-hover" id="risk_register_table">
                                <thead>
                                <tr>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.reference') }}</th>
                                    <th class="text-center" style="width: auto;">{{ trans('riskRegister.subject') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.probability') }} %</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.impact') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.status') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.dateToReview') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.daysLeft') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.submittedBy') }}</th>
                                    <th class="text-center occupy-min">{{ trans('riskRegister.submittedDate') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($risks as $risk)
                                    <?php $riskPost = $risk->getLatestRisk(); ?>
                                    <tr>
                                        <td class="text-center text-middle" style="font-family: monospace">{{{ \PCK\Helpers\StringOperations::pad($risk->reference_number, 4, '0') }}}</td>
                                        <td class="text-left">
                                            <a href="{{ route('riskRegister.show', array($project->id, $risk->id)) }}" class="plain">
                                                {{{ \PCK\Helpers\StringOperations::shorten($risk->subject, 30) }}}
                                            </a>
                                        </td>
                                        <td class="text-center text-middle">{{{ $riskPost->probability }}}</td>
                                        <td class="text-center text-middle">{{{ \PCK\RiskRegister\RiskRegisterMessage::getRatingText($riskPost->impact) }}}</td>
                                        <td class="text-center text-middle">{{{ \PCK\RiskRegister\RiskRegisterMessage::getStatusText($riskPost->status) }}}</td>
                                        <td class="text-center text-middle occupy-min">{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($riskPost->reply_deadline))->format(\Config::get('dates.standard')) }}}</td>
                                        <td class="text-center text-middle occupy-min">
                                            @if($riskPost->getDaysLeft() < 0)
                                                -
                                            @else
                                                <span class="{{{ (($daysLeft = $riskPost->getDaysLeft()) > 0) ? null : "text-danger" }}}">
                                                    {{{ $daysLeft }}}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center text-middle occupy-min">{{{ $risk->issuer->name }}}</td>
                                        <td class="text-center text-middle occupy-min">{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($risk->updated_at))->format(\Config::get('dates.standard')) }}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        var table = $('#risk_register_table' ).DataTable({
            // Disable initial sorting.
            "aaSorting": []
        });
    </script>
@endsection