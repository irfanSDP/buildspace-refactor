@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        @if(isset($contract))
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.contract.show', $contract->short_title, [$contract->id]) }}</li>
        <li>{{{ trans('forms.edit') }}} {{{ trans('general.developmentPlanning') }}}</li>
        @else
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.developmentPlanMasterlist')) }}</li>
        <li>{{{ trans('forms.add') }}} {{{ trans('general.developmentPlanning') }}}</li>
        @endif
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-table"></i> {{{ trans('general.developmentPlanning') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>@if(isset($contract)) {{{ trans('forms.edit') }}} {{{ $contract->short_title }}} @else {{{ trans('forms.add') }}} {{{ trans('general.developmentPlanning') }}} @endif</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => ['consultant.management.contracts.contract.store'], 'class' => 'smart-form', 'id' => 'consultant_management_contract-form']) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-8 col-lg-8">
                                <label class="label">{{{ trans('general.subsidiaryTownship') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('subsidiary_id') ? 'state-error' : null }}}">
                                    @if(isset($contract) && $contract->consultantManagementSubsidiaries->count())
                                    {{{ $contract->subsidiary->name }}}
                                    {{ Form::hidden('subsidiary_id', $contract->subsidiary_id) }}
                                    @else
                                    <select class="select2 fill-horizontal" name="subsidiary_id" id="contract_subsidiary_id">
                                        <option value="-1">{{{ trans('forms.none') }}}</option>
                                        @foreach($rootSubsidiaries as $rootSubsidiaryId => $rootSubsidiaryName)
                                        <option value="{{$rootSubsidiaryId}}" @if($rootSubsidiaryId == Input::old('subsidiary_id', isset($contract) ? $contract->subsidiary_id : null)) selected @endif>{{{ $rootSubsidiaryName }}}</option>
                                        @endforeach
                                    </select>
                                    @endif
                                </label>
                                {{ $errors->first('subsidiary_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-4 col-lg-4">
                                <label class="label">{{{ trans('companies.referenceNo') }}} <span class="required">*</span>:</label>
                                <label class="fill horizontal input {{{ $errors->has('reference_no') ? 'state-error' : 'state-success' }}}">
                                    {{ Form::text('reference_no', Input::old('reference_no', isset($contract) ? $contract->reference_no : null), ['required' => 'required', 'autofocus' => 'autofocus', 'id'=>'reference_number-txt']) }}
                                </label>
                                {{ $errors->first('reference_no', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('projects.title') }}} <span class="required">*</span>:</label>
                                <label class="textarea {{{ $errors->has('title') ? 'state-error' : null }}}">
                                    {{ Form::textarea('title', Input::old('title', isset($contract) ? $contract->title : null), ['id'=>'contract_title', 'required' => 'required', 'rows' => '1', 'autofocus' => 'autofocus']) }}
                                </label>
                                {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('general.description') }}} :</label>
                                <label class="textarea {{{ $errors->has('description') ? 'state-error' : null }}}">
                                    {{ Form::textarea('description', Input::old('description', isset($contract) ? $contract->description : null), ['rows' => 3]) }}
                                </label>
                                {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('companies.address') }}} <span class="required">*</span>:</label>
                                <label class="textarea {{{ $errors->has('address') ? 'state-error' : null }}}">
                                    {{ Form::textarea('address', Input::old('address', isset($contract) ? $contract->address : null), ['required' => 'required', 'rows' => 3]) }}
                                </label>
                                {{ $errors->first('address', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('projects.country') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('country_id') ? 'state-error' : null }}}">
                                    <select class="select2 fill-horizontal" name="country_id" id="country"></select>
                                </label>
                                {{ $errors->first('country_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('projects.state') }}} <span class="required">*</span>:</label>
                                <label class="fill-horizontal {{{ $errors->has('state_id') ? 'state-error' : null }}}">
                                    <select class="select2 fill-horizontal" name="state_id" id="state"></select>
                                </label>
                                {{ $errors->first('state_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ Form::hidden('id', (isset($contract)) ? $contract->id : -1) }}
                            @if(!isset($contract))
                            {{ link_to_route('consultant.management.contracts.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                            @else
                            {{ link_to_route('consultant.management.contracts.contract.show', trans('forms.back'), [$contract->id], ['class' => 'btn btn-default']) }}
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

@section('js')
<script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
<script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    @if(!isset($contract) or !$contract->consultantManagementSubsidiaries->count())
    $('#contract_subsidiary_id').on('select2:select', function (e) {
        var data = e.params.data;
        if(parseInt(data.id) > 0){
            $('#contract_title').val(data.text);
        }else{
            $('#contract_title').val('');
        }

        $.ajax({
            url: "{{{ route('consultant.management.contracts.generate.contract.number') }}}",
            method: 'POST',
            data: {
                _token: '{{{ csrf_token() }}}',
                sid: (parseInt(data.id) > 0) ? parseInt(data.id) : -1,
                cid: @if(isset($contract)) {{$contract->id}} @else -1 @endif
            },
            success: function(data) {
                $('#reference_number-txt').val(data.contract_number);
            },
            error  : function(jqXHR, textStatus, errorThrown) {
                // error
            }
        });
    });
    @endif
});
</script>
@endsection