@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
        <li>{{ trans('technicalEvaluation.technicalEvaluation') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php $needValidation = ( $tender->technicalEvaluationIsBeingValidated() && in_array($user->id, $tender->technicalEvaluationVerifiers->lists('id')) ) ? true : false; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> Technical Evaluation Verification for {{{ $tender->current_tender_name }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                        <header>Technical Evaluation Verification for {{{ $tender->current_tender_name }}}</header>

                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fa-fw fa fa-info"></i>
                                        <strong>Info!</strong> Verification is required in order to open this Tender <strong>({{{ $tender->current_tender_name }}})</strong>.
                                    </div>
                                </section>
                            </div>
                        </fieldset>

                        <footer>
                            {{ link_to_route('technicalEvaluation.results.show', trans('forms.back'), array($project->id, $tender->id), array('class' => 'btn btn-default')) }}
                            @if ( $needValidation  )
                            {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'verification_reject', 'class' => 'btn btn-danger'] )  }}
                            {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'verification_confirm', 'class' => 'btn btn-success'] )  }}
                            @endif
                        </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop