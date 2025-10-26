@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('requestForInformation.requestForInformation') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-comments"></i> {{{ trans('requestForInformation.requestForInformation') }}}
            </h1>
        </div>

        @if(PCK\RequestForInformation\RequestForInformation::canCreateRfiMessage($currentUser, $project))
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <a href="{{route('requestForInformation.create', array($project->id))}}" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('requestForInformation.issueNew') }}}
                </a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('requestForInformation.requestsForInformation') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped table-hover" id="rfi_table">
                                <thead>
                                <tr>
                                    <th class="text-center occupy-min">{{{ trans('requestForInformation.reference') }}}</th>
                                    <th class="text-center occupy-min">{{{ trans('requestForInformation.dateIssued') }}}</th>
                                    <th class="text-center">{{{ trans('requestForInformation.subject') }}}</th>
                                    <th class="text-center">{{{ trans('requestForInformation.issuer') }}}</th>
                                    <th class="text-center" style="width: auto;">{{{ trans('requestForInformation.question') }}}</th>
                                    <th class="text-center">{{{ trans('requestForInformation.deadline') }}}</th>
                                    <th class="text-center occupy-min">{{{ trans('requestForInformation.daysLeft') }}}</th>
                                    <th class="text-center">{{{ trans('requestForInformation.status') }}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($requestsForInformation as $rfi)
                                    <?php $lastQuestion = $rfi->getLastVisibleRequest(); ?>
                                    <tr>
                                        <td class="text-center text-middle" style="font-family: monospace">{{{ \PCK\Helpers\StringOperations::pad($rfi->reference_number, 4, '0') }}}</td>
                                        <td class="text-center text-middle occupy-min">{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($lastQuestion->created_at))->format(\Config::get('dates.standard')) }}}</td>
                                        <td class="text-left">
                                            <a href="{{ route('requestForInformation.show', array($project->id, $rfi->id)) }}" class="plain">
                                                {{{ \PCK\Helpers\StringOperations::shorten($rfi->subject, 30) }}}
                                            </a>
                                        </td>
                                        <td class="text-center text-middle occupy-min">{{{ $rfi->issuer->name }}}</td>
                                        <td class="text-left">
                                            <a href="{{ route('requestForInformation.show', array($project->id, $rfi->id)) }}" class="plain">
                                                {{{ \PCK\Helpers\StringOperations::shorten($lastQuestion->content, 60) }}}
                                            </a>
                                        </td>
                                        <td class="text-center text-middle occupy-min">{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($lastQuestion->reply_deadline))->format(\Config::get('dates.standard')) }}}</td>
                                        <td class="text-center text-middle occupy-min">
                                            @if($lastQuestion->getResponse())
                                                -
                                            @else
                                                <span class="{{{ (($daysLeft = $lastQuestion->getDaysLeft()) > 0) ? null : "text-danger" }}}">
                                                    {{{ $daysLeft }}}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center text-middle occupy-min">
                                            <?php
                                                $class = null;
                                                if(PCK\Verifier\Verifier::isCurrentVerifier($user, $lastQuestion)) $class="text-warning";
                                            ?>
                                            <span class="{{{ $class }}}">
                                                {{{ $lastQuestion->getResponse() ? trans('requestForInformation.answered') : trans('requestForInformation.requesting') }}}
                                            </span>
                                        </td>
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
        var table = $('#rfi_table' ).DataTable({
            // Disable initial sorting.
            "aaSorting": []
        });
    </script>
@endsection