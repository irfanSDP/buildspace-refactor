@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{ link_to_route('consultant.management.loa.running.number.index', 'LOA Running Numbers') }}</li>
    @if(isset($subsidiaryRunningNumber))
    <li>{{ link_to_route('consultant.management.loa.running.number.show', $subsidiaryRunningNumber->subsidiary->short_name, [$subsidiaryRunningNumber->subsidiary_id]) }}</li>
    <li>Edit LOA Running Number</li>
    @else
    <li>Create LOA Running Number</li>
    @endif
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-sort-numeric-down"></i> LOA Running Number
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>
                    @if(isset($subsidiaryRunningNumber)) Edit LOA Running Numbers @else Create LOA Running Numbers @endif
                </h2>
            </header>
            <div>
                <div class="widget-body">
                    @if(isset($subsidiaryRunningNumber))
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('subsidiaries.subsidiary') }}:</dt>
                                <dd>{{ $subsidiaryRunningNumber->subsidiary->name }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('subsidiaries.subsidiaryCode') }}:</dt>
                                <dd>{{{ $subsidiaryRunningNumber->subsidiary->identifier }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <hr class="simple">
                    @endif
                    {{ Form::open(['route' => ['consultant.management.loa.running.number.store'], 'class' => 'smart-form']) }}
                    @if(!isset($subsidiaryRunningNumber))
                    <div class="row">
                        <section class="col col-xs-12 col-md-8 col-lg-8">
                            <label class="label">{{{ trans('general.subsidiaryTownship') }}} <span class="required">*</span>:</label>
                            <label class="fill-horizontal {{{ $errors->has('subsidiary_id') ? 'state-error' : null }}}">
                                <select class="select2 fill-horizontal" name="subsidiary_id" id="subsidiary_id">
                                    <option value="-1">{{{ trans('forms.none') }}}</option>
                                    @foreach($subsidiaries as $subsidiaryId => $subsidiaryName)
                                    <option value="{{$subsidiaryId}}" @if($subsidiaryId == Input::old('subsidiary_id', null)) selected @endif>{{{ $subsidiaryName }}}</option>
                                    @endforeach
                                </select>
                            </label>
                            {{ $errors->first('subsidiary_id', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    @endif
                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-2 col-lg-2">
                            <label class="label">Next Running Number <span class="required">*</span>:</label>
                            <label class="input {{{ $errors->has('next_running_number') ? 'state-error' : null }}}">
                                {{ Form::text('next_running_number', Input::old('next_running_number', ($subsidiaryRunningNumber) ? $subsidiaryRunningNumber->next_running_number : 1), ['required'=>'required', 'autofocus' => 'autofocus']) }}
                            </label>
                            {{ $errors->first('next_running_number', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', (isset($subsidiaryRunningNumber)) ? $subsidiaryRunningNumber->subsidiary_id : -1) }}
                        @if(isset($subsidiaryRunningNumber))
                        {{ link_to_route('consultant.management.loa.running.number.show', trans('forms.back'), [$subsidiaryRunningNumber->subsidiary_id], ['class' => 'btn btn-default']) }}
                        @else
                        {{ link_to_route('consultant.management.loa.running.number.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                        @endif
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection