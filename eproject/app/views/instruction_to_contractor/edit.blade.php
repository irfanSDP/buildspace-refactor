
@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('instructiontocontractor.edit-form') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">

    <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <h2>{{{ trans('instructiontocontractor.instruction-to-contractor') }}}</h2>
        </header>
                    
        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                <div class="smart-form">
                {{ Form::model($record, array('route' => array('instruction-to-contractor.update', $project->id, $record->id), 'method' => 'PUT')) }}
                <fieldset>
                 
                    <div class="form-group">
                     {{ Form::label('instruction', trans('instructiontocontractor.instructions')) }}
                     {{ Form::textarea('instruction', Input::old('instruction'), array('class' => 'form-control padded-less-left', 'rows' => 5)) }}
                     {{ $errors->first('instruction', '<em class="invalid">:message</em>') }}
                    </div>

                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('siteManagement.attachments') }}}:</label>
                            @include('file_uploads.partials.upload_file_modal')
                        </section>

                        <section class="col col-xs-4 col-md-4 col-lg-4">
                            @include('verifiers.select_verifiers', array('modalId' => 'instructionsToContractorVerifierModal'))
                        </section>

                </fieldset>

                    <footer>
                    <!-- {{ Form::button('<i class="fa fa-save"></i> '.trans('instructiontocontractor.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }} -->
                    
					<!-- {{ Form::submit('Update', array('class' => 'btn btn-primary', 'name' => 'Submit')) }} -->
                    <button id="btnSubmitForApproval" type="submit" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message ="{{trans('general.submitWithoutVerifier')}}" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.submitForApproval') }}</button>

                    {{ link_to_route('instruction-to-contractor.index', trans('forms.cancel'), [$project->id] ,['class' => 'btn btn-default']) }}
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

@include('uploads.downloadModal')
@endsection 

@section('js')
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
   
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






