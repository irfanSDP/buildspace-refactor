@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ trans('general.consultantManagementRolesMaintenance') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('inspection.roles') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <h2> {{{ trans('inspection.roles') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    {{ Form::open(['route'=>'consultant.management.maintenance.roles.store', 'class' => 'smart-form']) }}
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width: 200px;" class="text-right">{{{ trans('inspection.roles') }}}</th>
                            <th style="width: auto;" class="text-center">{{{ trans('contractGroupCategories.userGroup') }}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($roles as $roleId => $roleName)
                            <tr>
                                <td class="text-middle text-right">
                                    <div class="{{{ $errors->has('roles.'.$roleId) ? 'has-error' : null }}}">
                                        {{{$roleName}}}
                                        {{ Form::hidden('roles['.$roleId.']', $roleId) }}
                                    </div>
                                    {{ $errors->first('roles.'.$roleId, '<em class="invalid pull-right">:message</em>') }}
                                </td>
                                <td class="text-middle" style="padding: 25px;">
                                    <label class="input fill-horizontal {{{ $errors->has('group_categories.'.$roleId) ? 'state-error' : null }}}">
                                        {{ Form::select('group_categories['.$roleId.'][]', $groupCategories, Input::old('group_categories['.$roleId.']', array_key_exists($roleId, $selectedGroupCategories) ? $selectedGroupCategories[$roleId] : null), array('class' => 'select2 fill-horizontal', 'multiple' => 'multiple')) }}
                                    </label>
                                    {{ $errors->first('group_categories.'.$roleId, '<em class="invalid">:message</em>') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <footer>
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection