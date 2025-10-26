@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('inspection.request', trans('requestForInspection.requestForInspection'), array($project->id)) }}</li>
        <li>{{{ \PCK\Helpers\StringOperations::shorten($requestForInspection->subject, 50) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-search"></i> {{{ $requestForInspection->reference }}} {{{ \PCK\Helpers\StringOperations::shorten($requestForInspection->subject, 50) }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('requestForInspection.requestForInspection') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        @include('request_for_inspection.partials.request')
                        @if(\PCK\Verifier\Verifier::isApproved($requestForInspection))
                            @foreach($requestForInspection->inspections as $inspection)
                                @if($inspection->isVisible($currentUser) && (!\PCK\Verifier\Verifier::isRejected($inspection)))
                                    @include('request_for_inspection.partials.inspection')
                                @endif
                                <?php $inspection->load('reply'); ?>
                                @if(($inspection->reply ?? false))
                                    @if(\PCK\Verifier\Verifier::isApproved($inspection->reply))
                                        @include('request_for_inspection.partials.reply')
                                    @elseif(\PCK\Verifier\Verifier::isBeingVerified($inspection->reply) && $inspection->reply->isVisible($currentUser))
                                        @include('request_for_inspection.partials.reply')
                                    @elseif(\PCK\Verifier\Verifier::isRejected($inspection->reply) && $inspection->reply->isVisible($currentUser) && ($currentUser->id != $inspection->reply->created_by))
                                        @include('request_for_inspection.partials.reply')
                                    @endif
                                @endif
                            @endforeach
                            <!-- Forms -->
                            @if(!$requestForInspection->isCompleted())

                                <?php $lastInspection = !is_null($requestForInspection->inspections->last()) ? $requestForInspection->inspections->last() : null; ?>
                                <?php $lastReply = $lastInspection->reply ?? null; ?>

                                @if($requestForInspection->inspections->isEmpty() || \PCK\Verifier\Verifier::isRejected($lastInspection))
                                    <!-- Inspection After Request -->
                                    @if(\PCK\Verifier\Verifier::isApproved($requestForInspection))
                                        @if((!$lastInspection) || \PCK\Verifier\Verifier::isRejected($lastInspection))
                                            @if(\PCK\DirectedTo\DirectedTo::isDirectedTo($currentUser->getAssignedCompany($project)->getContractGroup($project), $requestForInspection))
                                                @include('request_for_inspection.partials.inspectionForm')
                                            @endif
                                        @endif
                                    @endif
                                @elseif(!$requestForInspection->inspections->isEmpty())
                                    <!-- Inspection After Reply -->
                                    @if($lastReply)
                                        @if(\PCK\Verifier\Verifier::isApproved($lastReply))
                                            @if(\PCK\DirectedTo\DirectedTo::isDirectedTo($currentUser->getAssignedCompany($project)->getContractGroup($project), $lastReply))
                                                @include('request_for_inspection.partials.inspectionForm')
                                            @endif
                                        @endif
                                    @endif
                                @endif
                                <!-- Reply -->
                                @if($lastInspection && \PCK\Verifier\Verifier::isApproved($lastInspection))
                                    @if((!$lastReply) || \PCK\Verifier\Verifier::isRejected($lastReply))
                                        @if(($currentUser->id == $requestForInspection->created_by && $currentUser->stillInSameAssignedCompany($project, $requestForInspection->created_at)) && (!$lastReply || $lastReply->isVisible($currentUser)))
                                            @include('request_for_inspection.partials.replyForm')
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif
                        <form class="smart-form">
                            <footer class="bg-transparent">
                                {{ link_to_route('inspection.request', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                            </footer>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
@endsection