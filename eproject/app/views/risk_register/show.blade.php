@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('riskRegister.index', trans('riskRegister.riskRegister'), array($project->id)) }}</li>
        <li>{{{ \PCK\Helpers\StringOperations::shorten($risk->subject, 50) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-exclamation-triangle"></i> {{{ $risk->reference }}} {{{ \PCK\Helpers\StringOperations::shorten($risk->subject, 50) }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{ trans('riskRegister.risk') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">

                        <table class="table">
                            <tbody>
                            <tr>
                                <td>
                                    {{ Form::open(array('class' => 'smart-form')) }}
                                    <fieldset>
                                        <div class="row">
                                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                                <label class="label">{{ trans('riskRegister.reference') }} :</label>
                                                {{{ $risk->reference }}}
                                            </section>
                                        </div>
                                        <div class="row">
                                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                                <label class="label">{{ trans('riskRegister.subject') }} :</label>
                                                {{{ $risk->subject }}}
                                            </section>
                                        </div>
                                    </fieldset>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                            @foreach($risk->getVisibleMessages() as $message)
                                @if(\PCK\Verifier\Verifier::isApproved($message))
                                    <tr>
                                        @if($message->isRisk())
                                            <td>
                                                {{ Form::open(array('class' => 'smart-form')) }}
                                                    @include('risk_register.partials.showRisk')
                                                {{ Form::close() }}
                                            </td>
                                        @endif
                                        @if($message->isComment())
                                            <td class="bg-grey-e">
                                                {{ Form::open(array('class' => 'smart-form')) }}
                                                    @include('risk_register.partials.showComment')
                                                {{ Form::close() }}
                                            </td>

                                        @endif
                                    </tr>
                                @endif
                                @if(\PCK\Verifier\Verifier::isBeingVerified($message))
                                    <tr>
                                        <td class="bg-light-yellow">
                                            {{ Form::open(array('class' => 'smart-form')) }}
                                            @if($message->isRisk())
                                                @include('risk_register.partials.showRisk')
                                            @endif
                                            @if($message->isComment())
                                                @include('risk_register.partials.showComment')
                                            @endif
                                            {{ Form::close() }}
                                            @if(\PCK\Verifier\Verifier::isCurrentVerifier($user, $message))
                                                <div class="text-right">
                                                    {{ Form::open(array('route' => array('verify', $message->id), 'id' => 'verifierForm', 'class' => '')) }}
                                                    <input type="hidden" name="class" value="{{{ get_class($message) }}}"/>
                                                    <div class="form-group">
                                                        {{ Form::submit(trans('forms.reject'), array('name' => 'reject', 'class' => 'btn btn-danger')) }}
                                                        {{ Form::submit(trans('forms.approve'), array('name' => 'approve', 'class' => 'btn btn-primary')) }}
                                                    </div>
                                                    {{ Form::close() }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            @foreach($risk->getVisibleMessages() as $message)
                                @if(\PCK\Verifier\Verifier::isRejected($message))
                                    @if($message->isRisk())
                                        <tr>
                                            <td class="bg-light-red">
                                                {{ Form::open(array('class' => 'smart-form')) }}
                                                @include('risk_register.partials.showRisk')
                                                {{ Form::close() }}
                                            </td>
                                        </tr>
                                        @if($user->id == $message->composer->id)
                                            <tr>
                                                <td>
                                                    {{ Form::open(array('route' => array('riskRegister.risk.rejected.update', $project->id, $message->id), 'class' => 'smart-form')) }}
                                                        @include('risk_register.partials.risk_form_fields')
                                                        <footer>
                                                            {{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary')) }}
                                                        </footer>
                                                    {{ Form::close() }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                    @if($message->isComment())
                                        <tr>
                                            <td class="bg-light-red">
                                                {{ Form::open(array('class' => 'smart-form')) }}
                                                    @include('risk_register.partials.showComment')
                                                {{ Form::close() }}
                                            </td>
                                        </tr>
                                        @if($user->id == $message->composer->id)
                                            <tr>
                                                <td class="bg-light-red">
                                                    {{ Form::open(array('route' => array('riskRegister.comment.update', $project->id, $message->id), 'class' => 'smart-form')) }}
                                                    @include('risk_register.partials.comment_form_fields')
                                                    <footer>
                                                        {{ Form::submit(trans('riskRegister.comment'), array('class' => 'btn btn-primary')) }}
                                                    </footer>
                                                    {{ Form::close() }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                            @if($risk->canPostComment($user))
                                <tr>
                                    <td class="bg-grey-e">
                                        <button class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="addCommentForm">
                                            <i class="fa fa-comment"></i>
                                            {{ trans('riskRegister.comment') }}
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-grey-e">
                                        <div data-type="expandable" data-id="addCommentForm" data-default="hide">
                                            {{ Form::open(array('route' => array('riskRegister.comment.create', $project->id, $risk->id), 'class' => 'smart-form')) }}
                                                @include('risk_register.partials.comment_form_fields')
                                                <footer>
                                                    {{ Form::submit(trans('riskRegister.comment'), array('class' => 'btn btn-primary')) }}
                                                </footer>
                                            {{ Form::close() }}
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            @if($risk->canUpdatePublishedRisk($user))
                                <tr>
                                    <td>
                                        <button class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="updateRiskForm">
                                            <i class="fa fa-edit"></i>
                                            {{ trans('riskRegister.update') }}
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div data-type="expandable" data-id="updateRiskForm" data-default="hide">
                                            {{ Form::model($risk->getLatestRisk(), array('route' => array('riskRegister.risk.update', $project->id, $risk->id), 'class' => 'smart-form')) }}
                                                @include('risk_register.partials.risk_form_fields')
                                                <footer>
                                                    {{ Form::submit(trans('riskRegister.updateRisk'), array('class' => 'btn btn-primary')) }}
                                                </footer>
                                            {{ Form::close() }}
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>

                        {{ Form::open(array('class' => 'smart-form')) }}
                        <footer>
                            {{ link_to_route('riskRegister.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                        </footer>
                        {{ Form::close() }}
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
        $(document ).ready(function(){
            var vue = new Vue({
                el: '#extraFormActions',
                methods: {
                    verifierApprove: function()
                    {
                        $('#verifierForm [name=approve]' ).click();
                    },
                    verifierReject: function()
                    {
                        $('#verifierForm [name=reject]' ).click();
                    },
                    updatePublishedRisk: function()
                    {
                        $("#riskMessageForm").attr("action", "{{ route('riskRegister.risk.update', array($project->id, $risk->id)) }}");

                        $('#riskMessageForm').submit();
                    }
                }
            });
        });
    </script>
    <script>
        $('.datetimepicker').datetimepicker({
            format: 'DD-MMM-YYYY',
            showTodayButton: true,
            allowInputToggle: true
        });
    </script>
@endsection