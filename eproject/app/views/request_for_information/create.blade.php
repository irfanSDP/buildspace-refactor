@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('requestForInformation.index', trans('requestForInformation.requestForInformation'), array($project->id)) }}</li>
        <li>{{ trans('requestForInformation.issueNew') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-comments"></i> {{{ trans('requestForInformation.issueNew') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('requestForInformation.issueNew') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('route' => array('requestForInformation.store', $project->id), 'class' => 'smart-form', 'id' => 'add-form')) }}
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-lg-2 col-md-2">
                                        <label class="label">{{{ trans('requestForInformation.reference') }}} :</label>
                                        <label class="input {{{ $errors->has('reference_number') ? 'state-error' : null }}}">
                                            {{ Form::number('reference_number', Input::old('reference_number') ? Input::old('reference_number') : $defaultReferenceNumber, array('min' => 1)) }}
                                        </label>
                                        {{ $errors->first('reference_number', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-lg-10 col-md-10">
                                        <label class="label">{{{ trans('requestForInformation.subject') }}} <span class="required">*</span>:</label>
                                        <label class="input {{{ $errors->has('subject') ? 'state-error' : null }}}">
                                            {{ Form::text('subject', Input::old('subject'), array('required' => 'required', 'autofocus' => true)) }}
                                        </label>
                                        {{ $errors->first('subject', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <fieldset>
                                @include('request_for_information.partials.request_form_fields')
                            </fieldset>
                            <footer>
                                {{ link_to_route('requestForInformation.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                {{ Form::submit(trans('forms.send'), array('class' => 'btn btn-primary')) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection