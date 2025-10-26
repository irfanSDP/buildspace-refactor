@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('machinery.index', 'machinery', array()) }}</li>
        <li>Add Machinery</li>
    </ol>

@endsection

@section('content')

 <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    
        {{ Form::open(array('route' => array('machinery.store'))) }}

        <div class="form-group">
            {{ Form::label('name', "Name") }}
            {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'maxlength' => '100')) }}
            {{ $errors->first('name', '<em class="invalid">:message</em>') }}
        </div>

        {{ Form::submit("Create", array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}
        
    </div>
</div>

@endsection