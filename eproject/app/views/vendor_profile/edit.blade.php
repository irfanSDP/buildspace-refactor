<?php use PCK\ObjectField\ObjectField; ?>

@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorProfile', trans('vendorProfile.vendorProfiles'), []) }}</li>
        <li>{{ link_to_route('vendorProfile.show', $company->name, [$company->id]) }}</li>
        <li>{{{ trans('forms.edit') }}}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorProfile.vendorProfile') }}}
        </h1>
    </div>
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3" style="text-align:right;">
        <a href="{{ route('vendorProfile.show', [$company->id]) }}" class="btn btn-default btn-md header-btn">
            {{{ trans('forms.back') }}}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <?php
                switch($company->getStatus())
                {
                    case \PCK\Companies\Company::STATUS_DEACTIVATED:
                        $badgeColor = 'bg-color-red';
                        break;
                    case \PCK\Companies\Company::STATUS_EXPIRED:
                        $badgeColor = 'bg-color-yellow';
                        break;
                    default:
                        $badgeColor = 'bg-color-green';
                }
                ?>
                <h2>{{ $company->name }} <span class="label {{$badgeColor}}">{{{ $company->getStatusText() }}}</span></h2> 
            </header>
            <div>
                <div class="widget-body">
                    <ul id="myTab1" class="nav nav-tabs bordered">
                        <li @if((!$errors->hasBag('vendor')) or $errors->hasBag('company')) class="active" @endif >
                            <a href="#company-details" data-toggle="tab">{{ trans('vendorProfile.companyDetails') }}</a>
                        </li>
                        <li @if($errors->hasBag('vendor')) class="active" @endif>
                            <a href="#vendor-work-category" data-toggle="tab">{{ trans('vendorManagement.vendorWorkCategories') }}</a>
                        </li>
                        @if(isset($company) && $company->vendorProfile)
                        <li>
                            <a href="#remarks" data-toggle="tab">{{ trans('vendorProfile.remarks') }}</a>
                        </li>
                        @endif
                    </ul>

                    <div id="myTabContent1" class="tab-content padding-10">
                        <div class="tab-pane fade @if((!$errors->hasBag('vendor')) or $errors->hasBag('company')) in active @endif" id="company-details">
                            <div>
                                <div>
                                    <div>
                                        <i class="fa fa-tags"></i> <strong>{{ trans('tags.tags') }}</strong>
                                    </div>
                                    <div class="row">
                                        <div class="col col-lg-11">
                                            @include('templates.tag_selector', ['id' => 'tags-input'])
                                        </div>
                                        <div class="col col-lg-1">
                                            <div class="pull-right">
                                                <button class="btn btn-info" data-action="sync-tags">
                                                    <i class="fa fa-save"></i> {{ trans('forms.save') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                                <fieldset>
                                    @include('vendor_profile.partials.edit.company_details')
                                </fieldset>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade @if($errors->hasBag('vendor')) in active @endif" id="vendor-work-category">
                            <p>@include('vendor_profile.partials.edit.vendor_work_categories')</p>
                        </div>

                        @if(isset($company) && $company->vendorProfile)
                        <div class="tab-pane fade" id="remarks">
                            <div class="smart-form">
                                <div class="row">
                                    <section class="col col-xs-11">
                                        <label class="label">{{ trans('general.remarks') }}</label>
                                        <label class="textarea">
                                            <textarea rows="5" name="clientRemarks" id="clientRemarks"></textarea>
                                        </label>
                                    </section>
                                    <section class="col col-xs-1">
                                        <div class="pull-right ">
                                            <label class="label">&nbsp;</label>
                                            <button class="btn btn-info" id="btnSaveClientRemarks"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                                        </div>
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12">
                                        <div id="vendor-profile-remarks-table"></div>
                                    </section>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
    'title'      => trans('vendorProfile.editRemarks'),
    'modalId'    => 'editVendorProfileRemarksModal',
    'textareaId' => 'vendorProfileRemarksTextarea'
])

@include('templates/yesNoModal', [
    'modalId' => 'deleteVendorProfileRemarkYesNoModal',
    'titleId' => 'deleteVendorProfileRemarkYesNoModalTitle',
    'message' => trans('vendorProfile.areYouSureDeleteRemarks'),
])

@include('templates/yesNoModal', [
    'modalId' => 'changeVendorGroupYesNoModal',
    'title'   => trans('vendorProfile.changeVendorGroupTitle'),
    'message' => trans('vendorProfile.changeVendorGroupWarning'),
    'size'    => 'lg',
])
@endsection

@section('js')
<script src="{{ asset('js/app/app.dependentSelection.js') }}"></script>
<script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
<script>
    $(document).ready(function () {

        $("select#tags-input").select2({
            tags: true
        });

        <?php
        $vendorCategoryIds = Input::old('vendor_category_id', (isset($company)) ? $company->vendorCategories()->lists('vendor_category_id') : []);
        ?>
        var dependentSelection = $.extend({}, DependentSelection);
        dependentSelection.setUrls({first: webClaim.urlContractGroupCategories, second: webClaim.urlVendorCategories});
        dependentSelection.setForms({first: $('form [name=contract_group_category_id]'), second: $('form [name="vendor_category_id[]"]')});
        dependentSelection.setSelectedIds({first: {{Input::old('contract_group_category_id', isset($company) ? $company->contract_group_category_id : null)}}, second: {{json_encode($vendorCategoryIds)}}});
        dependentSelection.setPreSelectOnLoad({first: true, second: false});
        dependentSelection.init();

        @if(isset($company) && $company->vendorProfile)

        var vendorProfileRemarksTable = new Tabulator('#vendor-profile-remarks-table', {
            height:380,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('vendorProfile.remarks.ajax.list', [$company->vendorProfile->id]) }}",
            ajaxConfig: "GET",
            layout:"fitColumns",
            columns:[
                {title:"{{ trans('general.remarks') }}", field:"content", minWidth: 300, hozAlign:"left", headerSort:false,formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        return '<div class="well">'
                        +'<p style="white-space: pre-wrap;">'+rowData['content']+'</p>'
                        +'<br />'
                        +'<p style="color:#4d8af0">'+rowData['created_by']+' &nbsp;&nbsp;&nbsp;&nbsp; '+rowData['created_at']+'</p>'
                        +'</div>';
                    }
                }},
                {title:"{{ trans('general.actions') }}", field:"content", width: 80, hozAlign:"center", cssClass: 'text-center', headerSort:false, formatter: function(cell, formatterParams, onRendered) {
                        var data = cell.getRow().getData();

                        var updateVendorProfileRemarksButton = document.createElement('button');
                        updateVendorProfileRemarksButton.innerHTML = '<i class="fas fa-edit"></i></button>';
                        updateVendorProfileRemarksButton.className = 'btn btn-xs btn-warning';
                        updateVendorProfileRemarksButton.style['margin-right'] = '5px';

                        var deleteVendorProfileRemarksButton = document.createElement('button');
                        deleteVendorProfileRemarksButton.innerHTML = '<i class="fas fa-trash"></i></button>';
                        deleteVendorProfileRemarksButton.className = 'btn btn-xs btn-danger';

                        var container = document.createElement('div');
                        container.appendChild(updateVendorProfileRemarksButton);
                        container.appendChild(deleteVendorProfileRemarksButton);

                        updateVendorProfileRemarksButton.addEventListener('click', function(e) {
                            e.preventDefault();

                            $('#editVendorProfileRemarksModal [data-action="saveContent"]').data('url', data['route:update']);

                            $('#vendorProfileRemarksTextarea').val(data.content);

                            $('#editVendorProfileRemarksModal').modal('show');
                        });

                        deleteVendorProfileRemarksButton.addEventListener('click', function(e) {
                            e.preventDefault();

                            $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').data('url', data['route:delete']);
                            $('#deleteVendorProfileRemarkYesNoModal').modal('show');
                        });

                        return container;
                    }},
            ],
        });

        var vendorWorkCategoriesTable = new Tabulator('#vendor_work_categories-table', {
            height:480,
            placeholder: "{{ trans('general.noRecordsFound') }}",
            ajaxURL: "{{ route('vendorProfile.vendor.list', [$company->id]) }}",
            ajaxConfig: "GET",
            paginationSize: 100,
            pagination: "remote",
            ajaxFiltering:true,
            layout:"fitColumns",
            columns:[
                { title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"title", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        var c = '<div class="well">';
                        $.each(rowData.vendor_categories, function( key, value ) {
                            c+='<p>'+value+'</p>';
                        });
                        c+='</div>';
                        return c;
                    }
                }},
                {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", minWidth: 300, hozAlign:"left", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        var c = '<div class="well">';
                        c+='<p>'+rowData.vendor_work_category_name+'</p>';
                        c+='</div>';
                        return c;
                    }
                }},
                {title:"{{ trans('vendorManagement.qualified') }}", field:"qualified", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.status') }}", field:"status", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.actions') }}", field:"id", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    innerHtml: function(rowData){
                        var c = '<button type="button" class="btn btn-xs btn-primary" title="{{ trans("general.edit") }}" onclick=vendorEdit('+rowData.id+')><i class="fa fa-edit"></i></button>';
                        if(rowData.can_be_deleted){
                            c+='&nbsp;';
                            c += '<button type="button" data-toggle="modal" data-vendor-id="'+parseInt(rowData.id)+'" data-target="#vendorDeleteModal" class="btn btn-xs btn-danger" title="{{ trans("forms.delete") }}"><i class="fa fa-times"></i></button>';
                        }
                        return c;
                    }
                }}
            ],
        });

        $(document).on('click', '#btnSaveClientRemarks',function(e) {
            e.preventDefault();
            var remarks = DOMPurify.sanitize($('#clientRemarks').val()).trim();
            if(remarks.length){
                app_progressBar.toggle();

                $.post("{{ route('vendorProfile.remarks.save', [$company->vendorProfile->id]) }}", {
                    _token: _csrf_token,
                    remarks: remarks,
                })
                .done(function(data) {
                    if(data.success) {
                        app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                    } else {
                        SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                    }

                    if(vendorProfileRemarksTable){
                        vendorProfileRemarksTable.setData();//reload
                    }

                    $('#clientRemarks').val("");
                    app_progressBar.toggle();
                })
                .fail(function(data) {
                    app_progressBar.toggle();
                    SmallErrorBox.refreshAndRetry();
                });
            }
        });
        @endif

        $("#vendor_work_categories-form").on('submit', function(e){
            app_progressBar.toggle();
            var dataStr = $(this).serialize();
            $.ajax({
                type: "POST",
                url: "{{route('vendorProfile.vendor.store')}}",
                data: dataStr,
                success: function (resp) {
                    app_progressBar.maxOut();
                    $("[id^=input_label-]").removeClass('state-error');
                    $("[id^=input_error-]").html('');
                    if(!resp.success){
                        $.each( resp.errors, function( key, data ) {
                            data.key = data.key.replace(/\./g,'-');
                            $("#input_label-"+data.key).addClass('state-error');
                            $('<em id="input_error-'+data.key+'" class="invalid">'+data.msg+'</em>').insertAfter($("#input_label-"+data.key));
                        });
                    }else{
                        $.smallBox({
                            title : "{{ trans('general.success') }}",
                            content : "<i class='fa fa-check'></i> <i>{{ trans('forms.saved') }}</i>",
                            color : "#179c8e",
                            sound: true,
                            iconSmall : "fa fa-save",
                            timeout : 1000
                        });
                        resetForm();
                        if(vendorWorkCategoriesTable){
                            vendorWorkCategoriesTable.setData();//reload
                        }
                    }
                    app_progressBar.toggle();
                }
            });

            e.preventDefault();
        });

        @if(Input::old('vendor.vendor_category_id'))
        var url = '{{ route("registration.vendorWorkCategories", Input::old('vendor.vendor_category_id')) }}';
        createVendorWorkCategorySelect2(url);
        @endif

        $('#vendor_work_category-vendor_category_id-select').on('select2:select', function (e) {
            var data = e.params.data;
            var vcId = (data.id) ? parseInt(data.id) : -1;
            var url = '{{ route("registration.vendorWorkCategories", ":id") }}';
            url = url.replace(':id', vcId);
            createVendorWorkCategorySelect2(url);
        });

        $('#vendor_type-select').on('change', function (e) {
            if(parseInt($(this).val()) == {{{ \PCK\Vendor\Vendor::TYPE_WATCH_LIST }}}){
                $('#watchlist-date').show();
            }else{
                $('#watchlist-date').hide();
            }
        });

        $('#vendorDeleteModal').on('shown.bs.modal', function(e){
            var vid = $(e.relatedTarget).data('vendor-id');
            if(parseInt(vid)){
                $('.btn-ok').one('click', function(e){
                    e.stopPropagation();
                    app_progressBar.toggle();
                    var url = '{{ route("vendorProfile.vendor.delete", ":id") }}';
                    url = url.replace(':id', parseInt(vid));
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data:{'_token':"{{csrf_token()}}"}
                    }).then(function (resp) {
                        $.smallBox({
                            title : "{{ trans('general.success') }}",
                            content : "<i class='fa fa-check'></i> <i>{{ trans('forms.deleted') }}</i>",
                            color : "#179c8e",
                            sound: true,
                            iconSmall : "fa fa-save",
                            timeout : 1000
                        });
                        resetForm();
                        app_progressBar.maxOut();
                        $('#vendorDeleteModal').modal('hide');
                        if(vendorWorkCategoriesTable){
                            vendorWorkCategoriesTable.setData();
                        }
                        app_progressBar.toggle();
                    });
                });
            }
        });

        $("#vendor_edit-cancel-btn").on('click', function(){
            resetForm();
        });

        // remove and reconfigure textarea styles
        // bootstrap adds it's own stylings for unknown reasons
        $('#editVendorProfileRemarksModal').on('show.bs.modal', function() {
            $('#vendorProfileRemarksTextarea').removeAttr('style');
            $('#vendorProfileRemarksTextarea').css('height', '200px');
            $('#vendorProfileRemarksTextarea').css('overflow-y', 'scroll');
        });

        $('#editVendorProfileRemarksModal').on('shown.bs.modal', function() {
            $('#vendorProfileRemarksTextarea').focus();
        });

        $('#editVendorProfileRemarksModal [data-action="saveContent"]').on('click', function(e) {
            e.preventDefault();

            var url 	= $(this).data('url');
            var remarks = DOMPurify.sanitize($('#vendorProfileRemarksTextarea').val().trim());

            if(remarks == '') return;

            app_progressBar.toggle();

            $.post(url, {
                _token: _csrf_token,
                remarks: remarks,
            })
            .done(function(data) {
                if(data.success) {
                    app_progressBar.maxOut();
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksUpdated') }}");
                } else {
                    SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                }

                if(vendorProfileRemarksTable){
                    vendorProfileRemarksTable.setData();//reload
                }

                $('#vendorProfileRemarksTextarea').val("");

                $('#editVendorProfileRemarksModal').modal('hide');

                app_progressBar.toggle();
            })
            .fail(function(data) {
                app_progressBar.toggle();
                SmallErrorBox.refreshAndRetry();
            });
        });

        $('#deleteVendorProfileRemarkYesNoModal [data-action="actionYes"]').on('click', function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            app_progressBar.toggle();

            $.post(url, {
                _token: _csrf_token,
            })
            .done(function(data) {
                if(data.success) {
                    app_progressBar.maxOut();
                    SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('vendorProfile.remarksDeleted') }}");
                } else {
                    SmallErrorBox.formValidationError("{{ trans('forms.invalidInput') }}", data.errors[Object.keys(data.errors)[0]]);
                }

                if(vendorProfileRemarksTable){
                    vendorProfileRemarksTable.setData();//reload
                }

                $('#deleteVendorProfileRemarkYesNoModal').modal('hide');

                app_progressBar.toggle();
            })
            .fail(function(data) {
                app_progressBar.toggle();
                SmallErrorBox.refreshAndRetry();
            });
        });

        $('#company-details-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serializeArray();
            var data = {};

            formData.forEach(function(item, index) {
                data[item.name] = item.value.trim();
            });

            var contractGroupCategoryId = data['contract_group_category_id'].trim();

            if("{{ $company->contract_group_category_id }}".trim() !== contractGroupCategoryId) {
                $('#changeVendorGroupYesNoModal').modal('show');

                return false;
            }

            $(this)[0].submit();
        })
    });

    $('#changeVendorGroupYesNoModal [data-action="actionYes"]').on('click', function(e) {
        $('#company-details-form')[0].submit();
    });

    function createVendorWorkCategorySelect2(url, vendorWorkCategoryId){
        var vendorWorkCategorySelect = $('#vendor_work_category-vendor_work_category_id-select');
        $.ajax({
            type: 'GET',
            url: url
        }).then(function (resp) {
            var data = [];
            for (const prop in resp.data) {
                var selectedId = parseInt({{Input::old('vendor.vendor_work_category_id')}});
                selectedId = (vendorWorkCategoryId && !selectedId) ? vendorWorkCategoryId : selectedId;
                var selected = (parseInt(prop)==selectedId);
                data.push({id: prop, text: resp.data[prop], selected:selected});
            }
            
            vendorWorkCategorySelect.html('').select2({
                data:data
            });
            vendorWorkCategorySelect.data('select2').$container.addClass("select2-container--bootstrap");
            vendorWorkCategorySelect.data('select2').$container.removeClass("select2-container--default");
        });
    }

    function vendorEdit(vid){
        vid = parseInt(vid);
        if(vid){
            app_progressBar.toggle();
            $("[id^=input_label-]").removeClass('state-error');
            $("[id^=input_error-]").html('');
            var url = '{{ route("vendorProfile.vendor.edit", ":id") }}';
            url = url.replace(':id', parseInt(vid));
            $.ajax({
                type: 'GET',
                url: url
            }).then(function (data) {
                app_progressBar.maxOut();
                $("#vendor_edit-cancel-btn").show();
                $("#is_qualified-input").prop( "checked", data.is_qualified);
                $('#vendor_work_categories-header').html("{{ trans('forms.edit') }} "+data.vendor_work_category_name);
                $('#vendor_work_category-vendor_category_id-select').val(data.vendor_category_id);
                $('#vendor_work_category-vendor_category_id-select').select2().trigger('change');
                $('#vendor_work_category-vendor_category_id-select').data('select2').$container.addClass("select2-container--bootstrap");
                $('#vendor_work_category-vendor_category_id-select').data('select2').$container.removeClass("select2-container--default");
                $('#vendor_type-select').val(data.type);
                $('#vendor_type-select').select2().trigger('change');
                $('#vendor_type-select').data('select2').$container.addClass("select2-container--bootstrap");
                $('#vendor_type-select').data('select2').$container.removeClass("select2-container--default");
                var vwcUrl = '{{ route("registration.vendorWorkCategories", ":id") }}';
                vwcUrl = vwcUrl.replace(':id', data.vendor_category_id);
                createVendorWorkCategorySelect2(vwcUrl, parseInt(data.vendor_work_category_id));
                if(parseInt(data.type)=={{{ \PCK\Vendor\Vendor::TYPE_WATCH_LIST }}}){
                    $('#watch_list_entry_date-input').val(data.watch_list_entry_date);
                    $('#watch_list_release_date-input').val(data.watch_list_release_date);
                }
                $('#vendor_id-hidden').val(data.id);
                console.log(data);
                app_progressBar.toggle();
            });
        }
    }

    function resetForm(){
        $("#vendor_edit-cancel-btn").hide();
        $("[id^=input_label-]").removeClass('state-error');
        $("[id^=input_error-]").html('');
        $("#is_qualified-input").prop( "checked", true);
        $('#vendor_work_categories-header').html("{{ trans('forms.add') }} {{ trans('vendorManagement.vendorWorkCategory') }}");
        $('#vendor_work_category-vendor_category_id-select').val("");
        $('#vendor_work_category-vendor_category_id-select').select2().trigger('change');
        $('#vendor_work_category-vendor_category_id-select').data('select2').$container.addClass("select2-container--bootstrap");
        $('#vendor_work_category-vendor_category_id-select').data('select2').$container.removeClass("select2-container--default");
        $('#vendor_type-select').val("");
        $('#vendor_type-select').select2().trigger('change');
        $('#vendor_type-select').data('select2').$container.addClass("select2-container--bootstrap");
        $('#vendor_type-select').data('select2').$container.removeClass("select2-container--default");
        var vwcUrl = '{{ route("registration.vendorWorkCategories", -1) }}';
        createVendorWorkCategorySelect2(vwcUrl);
        $('#watch_list_entry_date-input').val("");
        $('#watch_list_release_date-input').val("");
        $('#vendor_id-hidden').val(-1);
    }
</script>
@endsection