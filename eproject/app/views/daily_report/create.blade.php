
@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('dailyreport.submit-form') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">

    <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <h2>{{{ trans('dailyreport.daily-report') }}}</h2>
        </header>
                    
        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                <div class="smart-form">
                {{ Form::open(array('route' => array('daily-report.store', $project->id),'files' => true)) }}
                   
                <fieldset>
                 

                    <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label for="instruction" style="padding-left:5px;"><strong>{{{ trans('dailyreport.description') }}}</strong>&nbsp;<span class="required">*</span></label>
                        <textarea class="form-control padded-less-left" rows="5" name="instruction" id="instruction">{{{Input::old('instruction')}}}</textarea>
                        {{ $errors->first('instruction', '<em class="invalid">:message</em>') }}
                    </section>

                        <section class="col col-xs-12 col-md-12 col-lg-12">
                        <label class="label">{{{ trans('siteManagement.attachments') }}}:</label>
                        @include('file_uploads.partials.upload_file_modal')
                        </section>
                        
                    
                        <section class="col col-xs-4 col-md-4 col-lg-4">
                        @include('verifiers.select_verifiers', array('modalId' => 'dailyReportVerifierModal'))
                        </section>
                        
                    </fieldset>

                    <footer>
                    <!-- {{ Form::button('<i class="fa fa-save"></i> '.trans('instructiontocontractor.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }} -->
                    <button id="btnSubmitForApproval" type="submit" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message ="{{trans('general.submitWithoutVerifier')}}" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.submitForApproval') }}</button>
					<!-- {{ Form::submit('Submit for Verify', array('class' => 'btn btn-primary', 'name' => 'Submit')) }} -->
					<!-- {{ Form::submit('Save as Draft', array('class' => 'btn btn-default', 'name' => 'draft')) }} -->
                    {{ link_to_route('daily-report.index', trans('forms.cancel'), [$project->id] ,['class' => 'btn btn-default']) }}
                    </footer>

                    {{ Form::close() }}
                </div>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    </article>

</div>

@endsection 

@section('js')
    <script>
        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();

            return !input.some(function(element){
                return (element.value > 0);
            });
        }
    </script>
@endsection 





