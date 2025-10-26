@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('rejected-materials.index', 'rejected_material', array()) }}</li>
        <li>{{{trans('rejected_materials.edit_rejected_material')}}}</li>
    </ol>

@endsection

@section('content')

 <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    
        {{ Form::model($rejectedMaterial, array('route' => array('rejected-materials.update', $rejectedMaterial->id), 'method' => 'PUT')) }}

            <div class="form-group">
                {{ Form::label('name', trans('rejected_materials.name')) }}
                {{ Form::text('name', Input::old('name'), array('class' => 'form-control','maxlength' => '100')) }}
                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
            </div>

        {{ Form::submit(trans('rejected_materials.edit'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}
        
    </div>
</div>

@endsection