@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('subsidiaries.index', trans('subsidiaries.subsidiaries')) }}</li>
        <li>@if(isset($subsidiary)) {{{ trans('forms.edit') }}} {{{ $subsidiary->name }}} @else {{{ trans('forms.add') }}} {{{ trans('subsidiaries.subsidiary') }}} @endif</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cubes"></i> {{{ trans('subsidiaries.subsidiaries') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($subsidiary)) {{{ trans('forms.edit') }}} {{{ $subsidiary->name }}} @else {{{ trans('forms.add') }}} {{{ trans('subsidiaries.subsidiary') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['subsidiaries.store'], 'class' => 'smart-form']) }}
                        <div class="row">
                            <section class="col col-xs-6 col-md-9 col-lg-9">
                                <label class="label">{{{ trans('subsidiaries.name') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
                                    {{ Form::text('name', Input::old('name', (isset($subsidiary)) ? $subsidiary->name : ""), ['required' => 'required']) }}
                                </label>
                                {{ $errors->first('name', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-6 col-md-3 col-lg-3">
                                <label class="label">{{{ trans('subsidiaries.identifier') }}} <span class="required">*</span>:</label>
                                <label class="input {{{ $errors->has('identifier') ? 'state-error' : null }}}">
                                    {{ Form::text('identifier', Input::old('identifier', (isset($subsidiary)) ? $subsidiary->identifier : ""), ['required' => 'required']) }}
                                </label>
                                {{ $errors->first('identifier', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('subsidiaries.parent') }}} :</label>
                                <label class="input fill-horizontal {{{ $errors->has('parent_id') ? 'state-error' : null }}}">
                                    {{ Form::select('parent_id', $parents, Input::old('parent_id', (isset($subsidiary) && $subsidiary->parent_id) ? $subsidiary->parent_id : -1), ['class' => 'select2 fill-horizontal']) }}
                                </label>
                                {{ $errors->first('parent_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($subsidiary)) ? $subsidiary->id : -1) }}
                            {{ link_to_route('subsidiaries.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection