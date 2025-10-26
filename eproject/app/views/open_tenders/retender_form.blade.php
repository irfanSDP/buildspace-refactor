@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $project->latestTender->current_tender_name, array($project->id, $project->latestTender->id)) }}</li>
        <li>{{ trans("tenders.tenderRevision") }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php use PCK\Filters\TenderFilters; ?>

    <?php $readOnly = ( ( $tender->isBeingValidated() OR $tender->isSubmitted() ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project)) ) ) ? true : false; ?>

    <?php $needValidation = ( $tender->isBeingValidated() && in_array($user->id, $tender->latestReTenderVerifiers->lists('id')) ) ? true : false; ?>

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-upload"></i> Apply {{ trans("tenders.tenderRevision") }} for {{{ $project->latestTender->current_tender_name }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                        <header>Apply {{ trans("tenders.tenderRevision") }} for {{{ $project->latestTender->current_tender_name }}}</header>

                        <fieldset>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    @if($readOnly)
                                        @include('verifiers.verifier_list', array(
                                            'verifiers' => $tender->reTenderVerifiers
                                        ))
                                    @else
                                        @include('verifiers.select_verifiers', array(
                                            'selectedVerifiers' => $tender->reTenderVerifiers
                                        ))
                                    @endif
                                </section>
                                <section class="col col-xs-12 col-md-12 col-lg-12">
									<label class="label">{{{ trans('general.remarks') }}}</label>
                                    @if($readOnly)
                                    <?php $retenderRemarks = (!is_null($tender->request_retender_remarks) && (trim($tender->request_retender_remarks) != '')) ? nl2br(trim($tender->request_retender_remarks)) : '-'; ?>
                                    <label class="label">{{ $retenderRemarks }}</label>
                                    @else
									<label class="textarea">
										<textarea rows="4" name="request_retender_remarks">{{ $tender->request_retender_remarks }}</textarea>
									</label>
                                    @endif
								</section>
                            </div>

                            @if ( ! $tender->reTenderVerifierLogs->isEmpty() )
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">Verification Log(s) :</label>
                                        <ol style="margin: 0 0 0 18px;">
                                            @foreach ( $tender->reTenderVerifierLogs as $log )
                                                <li>{{ $log->present()->log_text_format() }}</li>
                                            @endforeach
                                        </ol>
                                    </section>
                                </div>
                            @endif
                            {{ $errors->first('tender', '<em class="invalid">:message</em>') }}
                        </fieldset>

                        @if ( ! $readOnly  )
                            <footer>
                                {{ link_to_route('projects.openTender.show', trans('forms.back'), array($project->id, $project->latestTender->id), array('class' => 'btn btn-default')) }}

                                {{ Form::submit(trans('forms.submit'), array('class' => 'btn btn-success', 'name' => 'send_to_verify', 'data-intercept' => 'confirmation', 'data-intercept-condition' => 'noVerifier')) }}
                            </footer>
                        @endif

                        @if ( $needValidation  )
                            <footer>
                                {{ link_to_route('projects.openTender.show', trans('forms.back'), array($project->id, $project->latestTender->id), array('class' => 'btn btn-default')) }}

                                {{ Form::submit(trans('forms.confirm'), array('class' => 'btn btn-primary', 'name' => 'verification_confirm')) }}

                                {{ Form::submit(trans('forms.reject'), array('class' => 'btn btn-danger', 'name' => 'verification_reject')) }}
                            </footer>
                        @endif

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();
            return !input.some(function(element)
            {
                return (element.value > 0);
            });
        }
    </script>
@endsection