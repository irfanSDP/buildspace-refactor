@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('requestForInformation.index', trans('requestForInformation.requestForInformation'), array($project->id)) }}</li>
        <li>{{{ \PCK\Helpers\StringOperations::shorten($requestForInformation->subject, 50) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-comments"></i> {{{ $requestForInformation->reference }}} {{{ \PCK\Helpers\StringOperations::shorten($requestForInformation->subject, 50) }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('requestForInformation.requestsForInformation') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('route' => array('requestForInformation.message.create', $project->id, $requestForInformation->id), 'class' => 'smart-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('requestForInformation.reference') }}} :</label>
                                        {{{ $requestForInformation->reference }}}
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('requestForInformation.subject') }}} :</label>
                                        {{{ $requestForInformation->subject }}}
                                    </section>
                                </div>
                            </fieldset>
                            @foreach($requestForInformation->messages as $message)
                                @if(\PCK\Verifier\Verifier::isApproved($message) || (($message->composer->getAssignedCompany($project)->id == $user->getAssignedCompany($project)->id)))
                                <?php
                                    $class = $message->isRequest() ? null : 'bg-grey-e';
                                    if(\PCK\Verifier\Verifier::isBeingVerified($message)) $class = "bg-light-yellow";
                                    if(\PCK\Verifier\Verifier::isRejected($message)) $class = "bg-light-red";
                                ?>
                                <fieldset class="{{{ $class }}}">
                                    <button type="button" class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="message-{{{ $message->id }}}">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <div data-type="expandable" data-id="message-{{{ $message->id }}}">
                                        @if($message->isRequest())
                                            @include('request_for_information.partials.showRequest')
                                        @elseif($message->isResponse())
                                            @include('request_for_information.partials.showResponse')
                                        @endif
                                    </div>
                                </fieldset>
                                @endif
                            @endforeach
                            @if($requestForInformation->canRespond($user) || $requestForInformation->canRequest($user))
                                <fieldset>
                                    @if($requestForInformation->canRequest($user))
                                        @include('request_for_information.partials.request_form_fields')
                                    @elseif($requestForInformation->canRespond($user))
                                        @include('request_for_information.partials.response_form_fields')
                                    @endif
                                </fieldset>
                            @endif
                            <footer>
                                {{ link_to_route('requestForInformation.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                @if($requestForInformation->canRespond($user) || $requestForInformation->canRequest($user))
                                    {{ Form::submit(trans('forms.reply'), array('class' => 'btn btn-primary')) }}
                                @endif
                                @if(\PCK\Verifier\Verifier::isCurrentVerifier($user, $message))
                                    <div id="verifierActions">
                                        <button type="button" class="btn btn-primary" v-on="click:verifierApprove">
                                            {{ trans('forms.approve') }}
                                        </button>
                                        <button type="button" class="btn btn-danger" v-on="click:verifierReject">
                                            {{ trans('forms.reject') }}
                                        </button>
                                    </div>
                                @endif
                            </footer>
                        {{ Form::close() }}
                        @if(\PCK\Verifier\Verifier::isCurrentVerifier($user, $message))
                            {{ Form::open(array('route' => array('verify', $message->id), 'hidden' => true, 'id' => 'verifierForm')) }}
                                <input type="text" name="class" value="{{{ get_class($message) }}}"/>
                                {{ Form::submit(trans('forms.approve'), array('name' => 'approve')) }}
                                {{ Form::submit(trans('forms.reject'), array('name' => 'reject')) }}
                            {{ Form::close() }}
                        @endif
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
    @if(\PCK\Verifier\Verifier::isCurrentVerifier($user, $message))
    <script>
        $(document ).ready(function(){
            var vue = new Vue({
                el: '#verifierActions',
                methods: {
                    verifierApprove: function()
                    {
                        $('#verifierForm [name=approve]' ).click();
                    },
                    verifierReject: function()
                    {
                        $('#verifierForm [name=reject]' ).click();
                    }
                }
            });
        });
    </script>
    @endif
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
@endsection