@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('cidbGrades.add_cidb_grades') }}</li>
	</ol>
@endsection

@section('content')

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body no-padding">
                 {{ Form::open(array('class'=>'smart-form','id'=>'cidb-grades-form','files' => true)) }}
                    <fieldset>
                        <legend><h2>{{{ trans('cidbGrades.add_cidb_grades') }}}</h2></legend>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            <label for="grade" style="padding-left:5px;"><strong>{{{ trans('cidbGrades.grade') }}}</strong>&nbsp;<span class="required">*</span>
                            </label>
                            {{ Form::text('grade', Input::old('grade'), array('class' => 'form-control padded-less-left')) }}
                            {{ $errors->first('grade', '<em class="invalid">:message</em>') }}
                        </section>
                    </fieldset>
                    <footer>
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('cidbGrades.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
                        <a href="{{route('cidb_grades.index')}}">
                            {{ Form::button(trans('forms.cancel'), ['type' => 'button', 'class' => 'btn btn-default', 'name' => 'Cancel'] )  }}
                        </a>
                    </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>
    
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            
            $("form").on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });
        });

    </script>
@endsection