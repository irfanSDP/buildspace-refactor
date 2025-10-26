@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('defect-categories', 'Defect Category', array()) }}</li>
        <li>{{{ trans('defects.edit-category') }}}</li>
    </ol>

@endsection

@section('content')

 <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

        {{ Form::model($category, array('route' => array('defect-categories.update', $category->id), 'method' => 'PUT')) }}

            <div class="form-group">
                {{ Form::label('name', trans('defects.name')) }}
                {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
            </div>

        {{ Form::submit(trans('defects.edit'), array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}
        
    </div>
</div>

@endsection