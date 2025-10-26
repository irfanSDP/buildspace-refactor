@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('labours.index', 'labours', array()) }}</li>
        <li>Edit Labour record</li>
    </ol>

@endsection

@section('content')

 <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    
        {{ Form::model($labour, array('route' => array('labours.update', $labour->id), 'method' => 'PUT')) }}

            <div class="form-group">
                {{ Form::label('name', "Name") }}
                {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'maxlength' => '100')) }}
                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
            </div>

        {{ Form::submit("Edit", array('class' => 'btn btn-primary')) }}

        {{ Form::close() }}
        
    </div>
</div>

@endsection