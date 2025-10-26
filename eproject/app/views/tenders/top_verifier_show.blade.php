@extends('layout.main')
<?php use PCK\Tenders\TenderStages; ?>
@section('css')
    <link href="{{ asset('js/summernote-master/dist/summernote.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <?php $showROTForm = $showLOTForm = $showCTForm = false; ?>

    @if ( $tender->recommendationOfTendererInformation )
        <?php $showROTForm = true; ?>
    @endif

    @if ( $tender->listOfTendererInformation )
        <?php $showLOTForm = true; ?>
    @endif

    @if ( $tender->callingTenderInformation )
        <?php $showCTForm = true; ?>
    @endif

    <article class="col-sm-12">
		<div class="row">
            <div class="col-xs-8">
                <h1 class="page-title">{{{ $tender->current_tender_name }}}</h1>
            </div>
        </div>
        <div class="row">
            <article class="col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget" role="widget">
                    <div role="content">
                        <div class="widget-body">
                            @if ($showROTForm OR $showLOTForm OR $showCTForm)
                                <ul id="myTab1" class="nav nav-tabs bordered">

                                    @if ( $showROTForm )
                                        @include('tenders.partials.rec_of_tenderer_tab_header')
                                    @endif

                                    @if ( $showLOTForm )
                                        @include('tenders.partials.list_of_tenderer_tab_header')
                                    @endif

                                    @if ( $showCTForm )
                                        @include('tenders.partials.calling_tender_tab_header')
                                    @endif

                                </ul>

                                <div id="myTabContent1" class="tab-content" style="padding: 13px!important;">
                                    @if ( $showROTForm )
                                        <div class="tab-pane active"
                                            id="{{{ PCK\Forms\TenderRecommendationOfTendererInformationForm::TAB_ID }}}">
                                            @include('tenders.partials.top_verifier_recommendation_of_tenderer_information_form')
                                        </div>
                                    @endif

                                    @if ( $showLOTForm )
                                        <div class="tab-pane {{{ $showROTForm ? null : 'active' }}}"
                                            id="{{{ PCK\Forms\TenderListOfTendererInformationForm::TAB_ID }}}">
                                            @include('tenders.partials.top_verifier_list_of_tenderer_form')
                                        </div>
                                    @endif

                                    @if ( $showCTForm )
                                        <div class="tab-pane {{{ ($showROTForm OR $showLOTForm) ? null : 'active' }}}"
                                            id="{{{ PCK\Forms\TenderCallingTenderInformationForm::TAB_ID }}}">
                                            @include('tenders.partials.top_verifier_calling_tender_form')
                                        </div>
                                    @endif
                                </div>
                            @else
                                <h2 style="color: red;">Sorry, currently there are no viewable forms for your Role!</h2>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </article>

    @include('tenders.partials.commitment_status_log_modal')
    @include('consultant_management.partials.vendor_profile.modal')
    @include('templates.generic_table_modal', [
        'modalId'    => 'duplicatedCompanyPersonnelsModal',
        'title'      => trans('vendorManagement.duplicateCompanyPersonnels'),
        'tableId'    => 'duplicatedCompanyPersonnelsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
        'modalDialogClass' => 'modal-xl',
    ])
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            var url = window.location.href;

            if(location.hash) {
                $('a[href=' + location.hash + ']').tab('show');
            }

            $(document.body).on("click", "a[data-toggle]", function(event) {
                location.hash = this.getAttribute("href");
            });

            $(window).on('popstate', function() {
                var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
                $('a[href="' + anchor + '"]').tab('show');
            });

            $('#commitmentStatusLogModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var companyId = button.attr('companyId');
                var tenderStage = button.attr('tenderStage');
                var modal = $(this);
                modal.find('.modal-body ol').empty();
                modal.find('.message').empty();

                $.ajax({
                    url: "{{ route('topManagementVerifiers.projects.tender.get_contractors_commitment_status_log', array($project->id, $tender->id)) }}",
                    data: {
                        companyId: companyId,
                        tenderStage: tenderStage
                    },
                    success: function(data){
                        var logEntry;
                        var updatedBy;
                        var updatedAt;
                        var status;
                        var remarks;

                        if(data.length < 1){
                            modal.find('.message').append('No changes have been made');
                        }

                        for(dataIndex in data){
                            updatedBy = '<span style="color:green">'+data[dataIndex].user + ' (' + data[dataIndex].userEmail + ')</span>';
                            status = '<span style="color:blue">' + data[dataIndex].status + '</span>';
                            updatedAt = '<span style="color:red">' + data[dataIndex].date + '</span>';
                            remarks = data[dataIndex].remarks ? data[dataIndex].remarks : '';

                            logEntry = updatedAt + ' ' + status + ' ' + updatedBy + ' ' + remarks;
                            modal.find('.modal-body ol').append('<li>' + logEntry + '</li>');
                        }
                    }
                });
            });

            @if($tender->getTenderStageInformation())

            <?php $selectContractorsRoute =  $isVendorManagementEnabled ? route('list.of.vm.contractors.get', [$project->id, $tender->id]) : route('list.of.contractors.get', [$project->id, $tender->id]); ?>

            var selectContractorsTable = null;

            $('[data-action="show-vendor-profile"]').on('click', function(e) {
                e.preventDefault();

                $('#vendorProfileModal').data('id', $(this).data('id'));
                $('#vendorProfileModal').modal('show');
            })

            $('#vendorProfileModal').on('show.bs.modal', function(e) {
                var id = $(this).data('id');

                $('#vp-vendor_categories').val(null).trigger('change');
                $('#contractor-section').hide();
                $('#consultant-section').hide();
                $('#vp-vendor_performance_evaluation-rows').empty();

                var url = "{{ route('topManagementVerifiers.vendor.profile.info', ':id')}}";
                url = url.replace(':id', parseInt(id));

                app_progressBar.toggle();
                $.get(url, function(data){
                    $.each(data.details, function(key,val){
                        if(key=='vendor_categories'){
                            $.each(val, function(k,v){
                                $('#vp-vendor_categories').append('<option selected>'+v+'</option>').trigger('change');
                            });
                        }else if(key != 'is_contractor' || key != 'is_consultant'){
                            $('#vp-'+key).html(val);
                        }
                    });

                    if(data.details.is_contractor){
                        $('#contractor-section').show();
                    }

                    if(data.details.is_consultant){
                        $('#consultant-section').show();
                    }

                    $.each(data.vpe_rows, function(key,row){
                        $('#vp-vendor_performance_evaluation-rows').append(row);
                    });

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_DIRECTOR]) }}";
                    url = url.replace(':id', parseInt(id));
                    var CPDTable = Tabulator.prototype.findTable("#company-personnel-directors-table")[0];
                    if(CPDTable){
                        CPDTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_SHAREHOLDERS]) }}";
                    url = url.replace(':id', parseInt(id));
                    var CPSTable = Tabulator.prototype.findTable("#company-personnel-shareholders-table")[0];
                    if(CPSTable){
                        CPSTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.company.personnel.list', [':id', \PCK\CompanyPersonnel\CompanyPersonnel::TYPE_HEAD_OF_COMPANY]) }}";
                    url = url.replace(':id', parseInt(id));
                    var CPHODTable = Tabulator.prototype.findTable("#company-personnel-head-of-company-table")[0];
                    if(CPHODTable){
                        CPHODTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_COMPLETED]) }}";
                    url = url.replace(':id', parseInt(id));
                    var CompPTRTable = Tabulator.prototype.findTable("#completed-project-track-record-table")[0];
                    if(CompPTRTable){
                        CompPTRTable.setData(url);
                    }

                    url = "{{ route('vendorProfile.track.record.list', [':id', \PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT]) }}";
                    url = url.replace(':id', parseInt(id));
                    var CurrPTRTable = Tabulator.prototype.findTable("#current-project-track-record-table")[0];
                    if(CurrPTRTable){
                        CurrPTRTable.setData(url);
                    }

                    var preQUrl = "{{ route('consultant.management.vendor.profile.preq.list', ':id') }}";
                    preQUrl = preQUrl.replace(':id', parseInt(id));
                    var preQTable = Tabulator.prototype.findTable("#vendor-prequalification-table")[0];
                    if(preQTable){
                        preQTable.setData(preQUrl, {}, "GET");
                    }

                    var vwcUrl = "{{ route('vendorProfile.vendor.list', [':id']) }}";
                    vwcUrl = vwcUrl.replace(':id', parseInt(id));
                    var vwcTable = Tabulator.prototype.findTable("#vendor_work_categories-table")[0];
                    if(vwcTable){
                        vwcTable.setData(vwcUrl, {}, "GET");
                    }

                    var apUrl = "{{ route('vendorProfile.awardedProjects', [':id']) }}";
                    apUrl = apUrl.replace(':id', parseInt(id));
                    var apTable = Tabulator.prototype.findTable("#awarded-projects-table")[0];
                    if(apTable){
                        $.get(apUrl, function(data){
                            apTable.setData(data.data, {}, "GET");
                        });
                    }

                    var cpUrl = "{{ route('vendorProfile.completedProjects', [':id']) }}";
                    cpUrl = cpUrl.replace(':id', parseInt(id));
                    var cpTable = Tabulator.prototype.findTable("#completed-projects-table")[0];
                    if(cpTable){
                        $.get(cpUrl, function(data){
                            cpTable.setData(data.data, {}, "GET");
                        });
                    }

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                });
            });

            new Tabulator("#company-personnel-directors-table", {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                layout:"fitColumns",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                ]
            });

            new Tabulator("#company-personnel-shareholders-table", {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                layout:"fitColumns",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.designation') }}", field:"designation", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.amountOfShare') }}", field:"amount_of_share", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.holdingPercentage') }}", field:"holding_percentage", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                ]
            });

            new Tabulator("#company-personnel-head-of-company-table", {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                layout:"fitColumns",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.identificationNumber') }}", field:"identification_number", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.emailAddress') }}", field:"email_address", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.contactNumber') }}", field:"contact_number", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearsOfExperience') }}", field:"years_of_experience", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                ]
            });

            new Tabulator('#completed-project-track-record-table', {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", width: 480, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicOrConquasScore') }}", field:"has_qlassic_or_conquas_score", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:'tick', headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicScore') }}", field:"qlassic_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.qlassicYearOfAchievement') }}", field:"qlassic_year_of_achievement", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.conquasScore') }}", field:"conquas_score", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.conquasYearOfAchievement') }}", field:"conquas_year_of_achievement", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.awardsReceived') }}", field:"awards_received", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfAwardsReceived') }}", field:"year_of_recognition_awards", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
                ]
            });

            new Tabulator('#current-project-track-record-table', {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.title') }}", field:"title", minWidth: 480, hozAlign:"left", headerSort:false, headerFilter: true},
                    {title:"{{ trans('propertyDevelopers.propertyDeveloper') }}", field:"property_developer_name", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorCategory') }}", field:"vendor_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendor_work_category_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.vendorSubWorkCategory') }}", field:"vendor_work_subcategory_name", width: 300, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmount') }}", field:"project_amount", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:"money", headerFilter: true},
                    {title:"{{ trans('currencies.currency') }}", field:"currency", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.projectAmountRemarks') }}", field:"project_amount_remarks", width: 200, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfSitePosession') }}", field:"year_of_site_possession", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.yearOfCompletion') }}", field:"year_of_completion", width: 150, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter: true},
                    {title:"{{ trans('vendorManagement.remarks') }}", field:"remarks", minWidth: 320, hozAlign:"left", headerSort:false, cssClass:"text-center text-middle", headerFilter: true}
                ],
            });

            new Tabulator("#vendor-prequalification-table", {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
                layout:"fitColumns",
                paginationSize: 100,
                pagination: "remote",
                columns:[
                    {title:"{{ trans('vendorManagement.form') }}", field:"form", minWidth: 200, hozAlign:'left', headerSort:false},
                    {title:"{{ trans('vendorManagement.vendorWorkCategory') }}", field:"vendorWorkCategory", width: 280, hozAlign:'left', headerSort:false},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 100, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.grade') }}", field:"grade", width: 250, cssClass:"text-center text-middle", headerSort:false}
                ]
            });

            new Tabulator('#vendor_work_categories-table', {
                height:360,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data:[],
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
                    {title:"{{ trans('general.status') }}", field:"status", width: 180, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false}
                ],
            });

            new Tabulator('#awarded-projects-table', {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: [],
                ajaxConfig: "GET",
                layout:"fitColumns",
                dataLoaded:function(data){
                    if(data.length < 1) return;
                },
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('projects.status') }}", field:"status", width: 120, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
                ],
            });

            new Tabulator('#completed-projects-table', {
                height:280,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: [],
                ajaxConfig: "GET",
                layout:"fitColumns",
                dataLoaded:function(data){
                    if(data.length < 1) return;
                },
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('projects.project') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('projects.currency') }}", field:"currency", width: 90, hozAlign:"center", headerSort:false},
                    {title:"{{ trans('projects.contractSum') }}", field:"contractSum", width: 150, hozAlign:"right", headerSort:false}
                ],
            });

            $('[data-action="view-duplicate-company-personnels"]').on('click', function(e) {
                e.preventDefault();

                var id = $(this).data('id');

                $('#duplicatedCompanyPersonnelsModal').data('id', $(this).data('id'));
                $('#duplicatedCompanyPersonnelsModal').modal('show');
            });

            $('#duplicatedCompanyPersonnelsModal').on('show.bs.modal', function(e) {
                var id = $(this).data('id');
                var url = "{{ route('company.duplicated.company.personnels.get', [$project->id, $tender->id, ':id']) }}";
                url = url.replace(':id', parseInt(id));

                var duplicatedCompanyPersonnelsTable = Tabulator.prototype.findTable("#duplicatedCompanyPersonnelsTable")[0];

                if(duplicatedCompanyPersonnelsTable){
                    duplicatedCompanyPersonnelsTable.setData(url, {}, "GET");
                }
            });

            new Tabulator('#duplicatedCompanyPersonnelsTable', {
                height: 450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: [],
                ajaxConfig: "GET",
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorManagement.identificationNumber') }}", width:200, field:"identification_number", hozAlign:"center", headerSort:false},
                    {title:"{{ trans('general.type') }}", field:"type", width: 150, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('companies.company') }}", field:"company", width: 300, hozAlign:"left", headerSort:false},
                ],
            });
            @endif
        });
    </script>
@endsection