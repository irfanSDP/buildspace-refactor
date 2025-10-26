@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('cidb_codes.index', trans('cidbCodes.cidb_codes'), []) }}</li>
        @if ($FirstLevel !== NULL)
        <li>{{ link_to_route('cidb_codes_children.index', $FirstLevel->code, [$FirstLevel->id]) }}</li>
        @endif
        <li>{{$name->code}}</li>
		<li>{{ trans('cidbCodes.add_cidb_codes') }}</li>
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
                 {{ Form::open(array('class'=>'smart-form','id'=>'cidb-codes-form','files' => true)) }}
                    <fieldset>
                        <legend><h2>{{{ trans('cidbCodes.add_cidb_codes') }}}</h2></legend>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            <label for="code" style="padding-left:5px;"><strong>{{{ trans('cidbCodes.code') }}}</strong>&nbsp;<span class="required">*</span>
                            </label>
                            {{ Form::text('code', Input::old('code'), array('class' => 'form-control padded-less-left')) }}
                            {{ $errors->first('code', '<em class="invalid">:message</em>') }}
                            <br>
                            <label for="description" style="padding-left:5px;"><strong>{{{ trans('cidbCodes.description') }}}</strong>&nbsp;<span class="required">*</span>
                            </label>
                            <textarea class="form-control padded-less-left" rows="5" name="description" id="description">{{{Input::old('description')}}}</textarea>
                            {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                        </section>
                    </fieldset>
                    <footer>
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('cidbCodes.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
                        <a href="{{route('cidb_codes_children.index', array($parentId))}}">
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