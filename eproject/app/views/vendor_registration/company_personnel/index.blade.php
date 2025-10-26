@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.companyPersonnel') }}}</li>
    </ol>
@endsection

@section('css')
<style>
    .spaced {
        margin-right: 5px;
    }
</style>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('vendorManagement.companyPersonnel') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('vendors.vendorRegistration.companyPersonnel.create') }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forms.add') }}}
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @if(!empty($instructionSettings->company_personnel))
            <div class="padded label-success text-white"><strong>{{ nl2br($instructionSettings->company_personnel) }}</strong></div>
            <br>
            @endif
            @if(!empty($section->amendment_remarks))
            <div class="well @if($section->amendmentsRequired()) border-danger @elseif($section->amendmentsMade()) border-warning @endif">
                {{ nl2br($section->amendment_remarks) }}
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.directors') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="directors-table"></div>
                        @if($setting->has_attachments)
                        {{ Form::open(array('route' => array('vendors.vendorRegistration.companyPersonnel.uploads.directors'), 'class' => 'smart-form')) }}
                            <section>
                                <label class="label">{{{ trans('forms.attachments') }}}:</label>

                                @include('file_uploads.partials.upload_file_modal', array('id' => 'directors-upload', 'tableId' => 'directors-upload-table', 'uploadedFiles' => $directorUploadedFiles))
                            </section>
                            <footer>
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.shareholders') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="shareholders-table"></div>
                        @if($setting->has_attachments)
                        {{ Form::open(array('route' => array('vendors.vendorRegistration.companyPersonnel.uploads.shareholder'), 'class' => 'smart-form')) }}
                            <section>
                                <label class="label">{{{ trans('forms.attachments') }}}:</label>

                                @include('file_uploads.partials.upload_file_modal', array('id' => 'shareholders-upload', 'tableId' => 'shareholders-upload-table', 'uploadedFiles' => $shareholderUploadedFiles))
                            </section>
                            <footer>
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.headOfCompany') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div id="head-of-company-table"></div>
                        @if($setting->has_attachments)
                        {{ Form::open(array('route' => array('vendors.vendorRegistration.companyPersonnel.uploads.companyHead'), 'class' => 'smart-form')) }}
                            <section>
                                <label class="label">{{{ trans('forms.attachments') }}}:</label>

                                @include('file_uploads.partials.upload_file_modal', array('id' => 'company-heads-upload', 'tableId' => 'company-heads-upload-table', 'uploadedFiles' => $headOfCompanyUploadedFiles))
                            </section>
                            <footer>
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget padded">
                <footer>
                    <a href="{{ route('vendors.vendorRegistration.projectTrackRecord') }}" class="btn btn-info pull-right">{{ trans('forms.next') }}</a>
                    {{ link_to_route('vendors.vendorRegistration.index', trans('forms.back'), array(), array('class' => 'btn btn-default pull-right spaced')) }}
                </footer>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
    <script>
        $(document).ready(function () {
            var defaultColumns = [
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    show: function(cell){
                        return cell.getData().hasOwnProperty('id');
                    },
                    innerHtml: [
                        {
                            tag: 'a',
                            rowAttributes: {href:'route:edit'},
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            innerHtml: function(rowData){
                                if(rowData['deletable'])
                                {
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }

                                return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                            }
                        },
                    ]
                }}
            ];
            var shareholdersColumns = [
                {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false},
                {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                    show: function(cell){
                        return cell.getData().hasOwnProperty('id');
                    },
                    innerHtml: [
                        {
                            tag: 'a',
                            rowAttributes: {href:'route:edit'},
                            attributes: {class:'btn btn-xs btn-warning', title: '{{ trans("vendorPreQualification.updateItem") }}'},
                            innerHtml: {
                                tag: 'i',
                                attributes: {class: 'fa fa-edit'}
                            }
                        },{
                            innerHtml: function(){
                                return '&nbsp;';
                            }
                        },{
                            innerHtml: function(rowData){
                                if(rowData['deletable'])
                                {
                                    return '<a href="'+rowData['route:delete']+'" class="btn btn-xs btn-danger" data-id="'+rowData['id']+'" data-method="delete" data-csrf_token="{{{ csrf_token() }}}"><i class="fa fa-trash"></i></a>';
                                }

                                return '<button type="button" class="btn btn-xs invisible"><i class="fa fa-trash"></i></button>';
                            }
                        },
                    ]
                }}
            ];
            new Tabulator('#directors-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($directorsData) }},
                layout:"fitColumns",
                columns:defaultColumns,
            });
            new Tabulator('#shareholders-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($shareholdersData) }},
                layout:"fitColumns",
                columns:shareholdersColumns,
            });
            new Tabulator('#head-of-company-table', {
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($headOfCompanyData) }},
                layout:"fitColumns",
                columns:defaultColumns,
            });
        });
    </script>
@endsection