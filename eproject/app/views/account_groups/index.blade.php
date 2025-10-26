@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-cogs"></i>&nbsp;Account Code Settings
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body">
                    <div class="row mb-4">
                        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <button type="button" class="btn btn-info btn-xs" id="new_account_group-btn">
                                <i class="fa fa-plus"></i>&nbsp;New Account Group
                            </button>
                        </div>
                    </div>

                    @if(count($accountGroups))
                    <div class="row">
                        <section class="col col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <ul id="account_group-tabs" class="nav flex-column nav-pills pb-4">
                            @foreach($accountGroups as $idx => $accountGroup)
                                <li class="nav-item @if($idx == 0) active @endif" data-account_group_id="{{ $accountGroup->id }}">
                                    <a class="nav-link" href="#account_group-{{ $accountGroup->id }}" data-toggle="tab">
                                        <button type="button" class="btn btn-primary btn-xs edit_account_group-btn" data-id="{{ $accountGroup->id }}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        {{{ $accountGroup->name }}}
                                    </a>
                                </li>
                            @endforeach
                            </ul>
                        </section>
                        <section class="col col-xs-12 col-sm-10 col-md-10 col-lg-10">
                            <div class="tab-content">
                            @foreach($accountGroups as $idx => $accountGroup)
                                <div class="tab-pane fade in @if($idx == 0) active @endif" id="account_group-{{ $accountGroup->id }}">
                                    <div class="row">
                                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <button type="button" class="btn btn-info btn-xs new_account_code-btn" data-group_id="{{ $accountGroup->id }}">
                                                <i class="fa fa-plus"></i>&nbsp;New Account Code
                                            </button>
                                        </section>
                                    </div>
                                    <div class="row">
                                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="pt-6" id="account_codes-{{ $accountGroup->id }}-table"></div>
                                        </section>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </section>
                    </div>
                    @else
                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="alert alert-warning text-center">
                                <i class="fa-fw fa fa-info"></i>
                                <strong>Info!</strong> There is no Account Group and Account Code.
                            </div>
                        </section>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('account_groups.partials.account_group_form')

@include('account_groups.partials.account_code_form')

@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function () {

    $('#account_group_form-modal').on('show.bs.modal', function () {
        resetForm("account_group_form");
    })

    $('#new_account_group-btn').on('click', function(e){
        $('#account_group-form_title').html('New Account Group')
        $('#account_group_form-modal').modal('show');
        $('#account_group_id-hidden').val('-1');
    });

    $('.edit_account_group-btn').on('click', function(e){
        var url = "{{ route('account.group.info', [':id']) }}";
        url = url.replace(':id', parseInt($(this).data('id')));
        $.get(url, function(data){
            var form = $("#account_group_form");
            $.each(data, function (k, v) {
                $('[name="' + k + '"]', form).val(v);
            });
        });
        $('#account_group-form_title').html('Edit Account Group')
        $('#account_group_form-modal').modal('show');
    });

    $('#account_group_form').on('submit', function(e){
        e.preventDefault();
        submitForm($(this)[0], function(){
            location.reload();
        });
    });

    @foreach($accountGroups as $accountGroup)
    new Tabulator('#account_codes-{{ $accountGroup->id }}-table', {
        height:420,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('account.group.account.codes.ajax.list', $accountGroup->id) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('finance.accountCodes') }}", field:"code", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter:"textarea"},
            {title:"{{ trans('accountCodes.taxCode') }}", field:"tax_code", width:180, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.type') }}", field:"type_txt", width:100, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                innerHtml: [{
                    innerHtml: function(rowData){
                        return '<button title="{{ trans('general.edit') }}" class="btn btn-xs btn-primary" data-id="'+rowData.id+'"" onClick="accounCodeEdit('+rowData.id+', {{ $accountGroup->id}})"><i class="fa fa-edit"></i></button>';
                    }
                },{
                    innerHtml: function(){
                        return '&nbsp;';
                    }
                },{
                    innerHtml: function(rowData){
                        return '<a href="'+rowData['route:delete']+'" title="{{ trans('general.delete') }}" class="btn btn-xs btn-danger" data-id="'+rowData.id+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                    }
                }]
            }}
        ]
    });
    @endforeach

    $('#account_code_form-modal').on('show.bs.modal', function () {
        $('#account_code_type').val('').trigger('change');
        resetForm("account_code_form");
    })

    $('.new_account_code-btn').on('click', function(e){
        var form = $('#account_code_form');
        var url = form.attr('action').replace(/-?[0-9]*\.?[0-9]+/, parseInt($(this).data('group_id')));
        form.attr('action', url);
        $('#account_code_id-hidden').val('-1');
        $('#account_code-form_title').html('New Account Code');
        $('#account_code_form-modal').modal('show');
    });

    $('#account_code_form').on('submit', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        submitForm($(this)[0], function(){
            var accGrpEl = $("ul#account_group-tabs li.active");
            var tbl = Tabulator.prototype.findTable("#account_codes-"+accGrpEl.data('account_group_id')+"-table")[0];
            if(tbl){
                tbl.setData();//reload
            }
        });
    });
});

function accounCodeEdit(id, accountGroupId){
    var url = "{{ route('account.group.account.codes.info', [':grpId', ':id']) }}";
    url = url.replace(':grpId', parseInt(accountGroupId));
    url = url.replace(':id', parseInt(id));
    $.get(url, function(data){
        var form = $("#account_code_form");
        $.each(data, function (k, v) {
            if(k=='type'){
                $('#account_code_type').val(v).trigger('change');
            }else{
                $('[name="' + k + '"]', form).val(v);
            }
        });
    });

    var form = $('#account_code_form');
    var url = form.attr('action').replace(/-?[0-9]*\.?[0-9]+/, parseInt(accountGroupId));
    form.attr('action', url);
    
    $('#account_code-form_title').html('Edit Account Code')
    $('#account_code_form-modal').modal('show');
}

const submitForm = (form, successCallback) => {
    const formData  = new FormData();
    const formId    = form.getAttribute('id');
    const submitBtn = $("#"+formId+"_submit-btn");

    submitBtn.prop("disabled", true);

    for ( var i = 0; i < form.elements.length; i++ ) {
        var el = form.elements[i];
        if(el.name.length && (el.getAttribute("type") != "checkbox" || (el.getAttribute("type") == "checkbox" && el.checked)) && (el.getAttribute("type") != "radio" || (el.getAttribute("type") == "radio" && el.checked))){
            var selected = [];
            if(el.options) {
                selected = [...el.selectedOptions].map(option => option.value);
            }

            if (selected.length > 1){
                formData.append(el.name, selected);
            } else {
                formData.append(el.name, el.value); 
            }
        }
    }

    fetch(form.getAttribute('action'), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Csrf-Token': form.querySelector('[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    }).then((response) => {
        if (!response.ok) {
            return;
        }
        return response.json(); 
    })
    .then((data) =>{
        
        submitBtn.prop("disabled", false);

        if (data.status == "success"){
            $('#'+formId+'-modal').modal('hide');
            if (typeof successCallback == "function"){
                successCallback(data);
            }
        }else{
            for(var i in data.errors){
                var el = form.querySelector('em[data-field="form_error-'+i+'"]');
                if(el){
                    form.querySelector('label[data-field="form_error_label-'+i+'"]').classList.add("state-error");
                    el.textContent = data.errors[i];
                }
            }
        }
    }).catch((error) => {
        console.log(error);
        submitBtn.prop("disabled", false);
    });
}

const resetForm = (formId) => {
    const form = $('#'+formId);
    const errElems = form.find("em[data-field^='form_error-']");
    form.trigger("reset");
    errElems.each(function(i, elem) {
        $(this).text("");
        let n = $(this).data('field');
        let nArr = n.split("-");
        if(nArr.length > 1){
            $('label[data-field="form_error_label-'+nArr[1]+'"]').removeClass('state-error');
        }
    });
}
</script>
@endsection