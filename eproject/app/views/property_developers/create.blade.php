@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('propertyDevelopers.propertyDevelopers') }}}</li>
        <li>{{{ trans('forms.add') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('propertyDevelopers.propertyDevelopers') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('forms.add') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::open(array('route' => array('propertyDevelopers.store'), 'class' => 'smart-form')) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="label">{{{ trans('propertyDevelopers.name') }}} <span class="required">*</span>:</label>
                                    <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                        {{ Form::text('name', Input::old('name'), array('required' => 'required', 'autofocus' => 'autofocus')) }}
                                    </label>
                                    {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <footer>
                                {{ link_to_route('propertyDevelopers.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection