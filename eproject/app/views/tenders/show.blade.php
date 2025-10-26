@extends('layout.main')
<?php use PCK\Tenders\TenderStages; ?>
@section('css')
    <link href="{{ asset('js/summernote-master/dist/summernote.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tender.index', trans('navigation/projectnav.tenders'), array($project->id)) }}</li>
        <li>{{{ $tender->current_tender_name }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection
<?php use PCK\ContractGroups\ContractGroup; ?>
<?php use PCK\ContractGroups\Types\Role; ?>
@section('content')
    <?php $buCompany                 = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first(); ?>
    <?php $gcdCompany                = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))->first(); ?>
    <?php $tenderDocumentCompany     = $project->selectedCompanies()->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)->first(); ?>
    <?php $buHasTenderDocumentAccess = is_null($tenderDocumentCompany) ? false : ($buCompany->id === $tenderDocumentCompany->id); ?>

    <?php $showROTForm = $showLOTForm = $showCTForm = false; ?>

    @if ( $tender->isFirstTender() && $user->hasCompanyProjectRole($project, \PCK\Tenders\Tender::viewAllRoles()) )
        @if ( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT) && $tender->recommendationOfTendererInformation && $tender->recommendationOfTendererInformation->isSubmitted() )
            <?php $showROTForm = true; ?>
        @elseif ( $user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER) )
            <?php $showROTForm = true; ?>
        @endif
    @endif

    @if ( $user->hasCompanyProjectRole($project, \PCK\Filters\TenderFilters::getListOfTendererFormRole($project)) && $tender->listOfTendererInformation )
        <?php $showLOTForm = true; ?>
    @elseif ( $tender->listOfTendererInformation && $tender->listOfTendererInformation->isSubmitted() )
        <?php $showLOTForm = true; ?>
    @endif

    <!-- additional check to only allow GCD and BU to view List of Tenderer's tab after submission -->
    @if ( $tender->listOfTendererInformation && $tender->listOfTendererInformation->isSubmitted() && ! $user->hasCompanyProjectRole($project, array(PCK\ContractGroups\Types\Role::GROUP_CONTRACT, PCK\ContractGroups\Types\Role::PROJECT_OWNER)) )
        <?php $showLOTForm = false; ?>
    @endif

    <!-- additional check to hide submitted List of Tenderer's tab  -->
    <!-- if GCD is assigned and BU does not have group access to tender documents, hide -->
    @if ( $tender->listOfTendererInformation 
            && $tender->listOfTendererInformation->isSubmitted()
            && ( $user->company->id == $buCompany->id )
            && !is_null($gcdCompany)
            && !$buHasTenderDocumentAccess )
        <?php $showLOTForm = false; ?>
    @endif

    @if ( $user->hasCompanyProjectRole($project, $project->getCallingTenderRole()) && $tender->callingTenderInformation )
        <?php $showCTForm = true; ?>
    @elseif ( $tender->callingTenderInformation && $tender->callingTenderInformation->isSubmitted() )
        <?php $showCTForm = true; ?>
    @endif

    <!-- additional check to hide submitted Calling Tender's tab  -->
    <!-- if GCD is assigned and BU does not have group access to tender documents, hide -->
    @if ( $tender->callingTenderInformation 
            && $tender->callingTenderInformation->isSubmitted()
            && ( $user->company->id == $buCompany->id )
            && !is_null($gcdCompany)
            && !$buHasTenderDocumentAccess )
        <?php $showCTForm = false; ?>
    @endif
    
    <div class="row">
        <div class="col-xs-8">
            <h1 class="page-title">{{{ $tender->current_tender_name }}}</h1>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
            <div class="btn-group pull-right header-btn">
                @include('tenders.partials.index_actions_menu', array('classes' => 'pull-right'), [
                    'showExportLotInformationButton' => !is_null($tender->listOfTendererInformation),
                    'exportLotInformationURL'        => route('projects.tender.lot.tenderer.info.excel.export', [$project->id, $tender->id]),                
                ])
            </div>
        </div>
    </div>
    <div class="row">
        <article class="col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget" role="widget">
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
                                    @include('tenders.partials.recommendation_of_tenderer_information_form')
                                </div>
                            @endif

                            @if ( $showLOTForm )
                                <div class="tab-pane {{{ $showROTForm ? null : 'active' }}}"
                                    id="{{{ PCK\Forms\TenderListOfTendererInformationForm::TAB_ID }}}">
                                    @include('tenders.partials.list_of_tenderer_form')
                                </div>
                            @endif

                            @if ( $showCTForm )
                                <div class="tab-pane {{{ ($showROTForm OR $showLOTForm) ? null : 'active' }}}"
                                    id="{{{ PCK\Forms\TenderCallingTenderInformationForm::TAB_ID }}}">
                                    @include('tenders.partials.calling_tender_form')
                                </div>
                            @endif
                        </div>
                    @else
                        <h2 style="color: red;">Sorry, currently there are no viewable forms for your Role!</h2>
                    @endif
                </div>
            </div>
        </article>
    </div>

    @include('tenders.partials.commitment_status_log_modal')

    @include('tenders.partials.send_notification_feedback_modal')

    @include('tenders.partials.email_composer_modal')

    @include('tenders.partials.tender_reminder_sent_modal')

    <!-- if show LOT or CT form (they both have different options (editable etc.)-->
    <?php use PCK\Filters\TenderFilters; ?>

    <?php $withModel = $tender->listOfTendererInformation ? true : false; ?>
    <?php $LOTReadOnly = ( ( $withModel && ( $tender->listOfTendererInformation->isBeingValidated() OR $tender->listOfTendererInformation->isSubmitted() ) ) || ( !$isEditor || !$user->hasCompanyProjectRole($project, TenderFilters::getListOfTendererFormRole($project) ) ) ) ? true : false; ?>

    <?php $withModel = $tender->callingTenderInformation ? true : false; ?>
    <?php
        $selectedContractors = array();

        $modes = array(
            'edit' => false,
            'send' => false,
        );

        if( $showCTForm )
        {
            $selectedContractors = $tender->callingTenderInformation->selectedContractors;
            $modes = array(
                'edit' => true,
                'send' => true,
            );
        }
    ?>
    @if ( $showCTForm)
        <div class="tenderInterview">
            @include('tenders.partials.tender_interview_modal', array(
                'selectedContractors' => $selectedContractors,
                'modes' => $modes,
            ))
            @include('tenders.partials.interview.interviewee_preview_modal', array(
                'modes' => $modes,
            ))
            @include('tenders.partials.interview.interviewer_preview_modal', array(
                'modes' => $modes,
            ))
        </div>
    @endif

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
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/plugin/clockpicker/clockpicker.min.js') }}"></script>
    <script src="{{ asset('js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('.datetimepicker').datetimepicker({
                format: 'DD-MMM-YYYY hh:mm A',
                stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                showTodayButton: true,
                allowInputToggle: true
            });

            $('form input[name=date_of_closing_tender],input[name=proposed_date_of_closing_tender]').on('dp.change', function(){
                $('form input[name=technical_tender_closing_date]').val($(this).val());
            });

            function toggleTechnicalClosingDateVisibility(tabId) {
                if($('#' + tabId +' form input[type=checkbox][name=technical_evaluation_required]').prop('checked')){
                    $('#' + tabId +' [data-id=technical_tender_closing_date]').closest('div .row').show();
                }else{
                    $('#' + tabId +' [data-id=technical_tender_closing_date]').closest('div .row').hide();
                }
            }

            $('form input[type=checkbox][name=technical_evaluation_required]').each(function(){
                toggleTechnicalClosingDateVisibility($(this).closest('div .tab-pane').prop('id'));
            });

            $('form input[type=checkbox][name=technical_evaluation_required]').on('change', function(){
                toggleTechnicalClosingDateVisibility($(this).closest('div .tab-pane').prop('id'));
            });

            var url = window.location.href;

            if(location.hash) {
                $('a[href="' + location.hash + '"]').tab('show');
            }

            $(document.body).on("click", "a[data-toggle]", function(event) {
                location.hash = this.getAttribute("href");
            });

            // verifier(s)
            $('.verifier_lists').select2({
                allowClear: true,
                theme: 'bootstrap',
                placeholder: 'Select a Verifier'
            });

            $( 'select.form-control' ).select2({theme: 'bootstrap'});

            // toggle all recipients checkbox
            $(document).on('click', '#select-all-recipients-stage-{{{$tender->getTenderStage()}}}', function() {
                $('#selected-recipients-table-stage-{{{$tender->getTenderStage()}}} tbody input[type="checkbox"]').prop('checked', this.checked);
            });

            // uncheck select-all-recipients checkbox
            $(document).on('click', '#selected-recipients-table-stage-{{{$tender->getTenderStage()}}} tbody input[type="checkbox"]', function() {
                $('#select-all-recipients-stage-{{{$tender->getTenderStage()}}}').prop('checked', false);
            });

            // send notification to selected contractors

            $(document).on('click', '.compose-email-button', function(){
                $('#emailComposerModal').modal('show');

                // Individualise modal
                $('#emailComposerLabel').text($(this).data('title'));
                $('#compose-email-send-button-stage-{{{$tender->getTenderStage()}}}').attr('id', $(this).data('send-button-id'));
            });

            $(document).on('click', '#sendROTNotificationToContractorsButton', function(){
                var selectedContractors = [];
                $('#selected-recipients-table-stage-{{{ \PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER }}} tbody input[type="checkbox"]').each(function() {
                    if(this.checked) {
                        selectedContractors.push(parseInt(this.value));
                    }
                });
                sendNotifications(selectedContractors, '{{{ \PCK\Tenders\TenderStages::TENDER_STAGE_RECOMMENDATION_OF_TENDERER }}}');
            });

            $(document).on('click', '#sendLOTNotificationToContractorsButton', function(){
                var selectedContractors = [];
                $('#selected-recipients-table-stage-{{{ \PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER }}} tbody input[type="checkbox"]').each(function() {
                    if(this.checked) {
                        selectedContractors.push(parseInt(this.value));
                    }
                });
                sendNotifications(selectedContractors, '{{{ \PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER }}}');
            });

            $(document).on('click', '#sendCallingTenderNotificationToContractorsButton', function(){
                var selectedContractors = [];
                @if ( $tender->callingTenderInformation )
                    @foreach ( $tender->callingTenderInformation->selectedContractors as $contractor )
                        selectedContractors.push({{{$contractor->id}}});
                    @endforeach
                @endif
                sendNotifications(selectedContractors, '{{{ \PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER }}}');
            });

            // ajax call
            function sendNotifications(selectedContractors, tenderStage){
                //disable send button
                $('[data-original-id="compose-email-send-button"]').prop('disabled', true);

                var messageInput = $('#message-input').code();
                var employerNameInput = $('#inviter-name-input').val();
                var sendCopyToSelf = $('#send-copy-to-self').prop("checked");

                $.ajax({
                    url: "{{ route('projects.tender.reminder.send', array($project->id, $tender->id)) }}",
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        selectedContractors: selectedContractors,
                        tenderStage: tenderStage,
                        projectId:'{{{$project->id}}}',
                        tenderId:'{{{$tender->id}}}',
                        emailMessage: messageInput,
                        employerName: employerNameInput,
                        sendCopyToSelf: sendCopyToSelf
                    },
                    success:function(data){
                        // closes the email composer modals
                        $('#emailComposerModal').modal('hide');
                        $('#emailComposerPreviewModal').modal('hide');
                        $('#emailComposerSelectRecipientROTModal').modal('hide');
                        $('#emailComposerSelectRecipientLOTModal').modal('hide');
                        $('#emailComposerSelectRecipientCTModal').modal('hide');

                        //enable send button
                        $('[data-original-id="compose-email-send-button"]').prop('disabled', false);

                        // give some user feedback if notifications are sent
                        var feedbackModal = $('#sendNotificationFeedbackModal');

                        feedbackModal.find('.sent-to-list').empty();
                        feedbackModal.find('.sent-to-label').attr('hidden', true);

                        feedbackModal.find('.not-sent-to-list').empty();
                        feedbackModal.find('.not-sent-to-label').attr('hidden', true);

                        feedbackModal.find('.message').empty();

                        feedbackModal.modal('show');

                        if(data!=null){
                            // all emails successfuly sent
                            if(data.contractorsWithAdmin.length > 0){
                                feedbackModal.find('.sent-to-label').attr('hidden', false);
                            }

                            for(dataIndex in data.contractorsWithAdmin){
                                feedbackModal.find('.sent-to-list').append('<li>' + data.contractorsWithAdmin[dataIndex] + '</li>');
                            }

                            if(data.contractorsWithoutAdmin.length > 0){
                                feedbackModal.find('.not-sent-to-label').attr('hidden', false);
                            }

                            for(dataIndex in data.contractorsWithoutAdmin){
                                feedbackModal.find('.not-sent-to-list').append('<li>' + data.contractorsWithoutAdmin[dataIndex] + '</li>');
                            }

                            if( (data.contractorsWithAdmin.length < 1) && (data.contractorsWithoutAdmin.length < 1) ){
                                feedbackModal.find('.message').append('No contractors were selected');
                            }
                        }else{
                            // something went wrong with sending emails
                            feedbackModal.find('.message').append('Sorry! Something went wrong. Not all notifications were sent.');
                        }
                    },
                    error: function(){
                        // give some user feedback if notifications are not sent
                        var feedbackModal = $('#sendNotificationFeedbackModal');
                        feedbackModal.find('.message').empty();
                        feedbackModal.find('.message').append('Sorry! Something went wrong.');
                    }
                });
            }

            $(document).on('click', '#btnResendEmail-tender-stage-{{{ $tender->getTenderStage() }}}', function() {
                var tenderStage = {{{ $tender->getTenderStage() }}};
                $.ajax({
                    url: "{{ route('projects.tender.reminder.email.send', array($project->id, $tender->id)) }}",
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        tenderStage: tenderStage,
                        projectId:'{{{$project->id}}}',
                        tenderId:'{{{$tender->id}}}'
                    },
                    success: function(data) {
                        $('#tenderReminderSentModal').modal('show');
                    }
                });
            });

            $('#commitmentStatusLogModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var companyId = button.attr('companyId');
                var tenderStage = button.attr('tenderStage');
                var modal = $(this);
                modal.find('.modal-body ol').empty();
                modal.find('.message').empty();

                $.ajax({
                    url: "{{ route('projects.tender.get_contractors_commitment_status_log', array($project->id, $tender->id)) }}",
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

        });

        $(window).on('popstate', function() {
            var anchor = location.hash || $("a[data-toggle=tab]").first().attr("href");
            $('a[href="' + anchor + '"]').tab('show');
        });

        $('.summernote').summernote({
            placeholder: 'Email content',
            toolbar: [
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['paragraph']],
                ['view', ['fullscreen']],
                ['help', ['help']],
                ['codeview', ['codeview']]
            ]
        });

        $(document).on('shown.bs.modal', '#emailComposerSelectRecipientROTModal', function(){
            $('#emailComposerPreviewModal').modal('hide');
        });

        $(document).on('shown.bs.modal', '#emailComposerSelectRecipientLOTModal', function(){
            $('#emailComposerPreviewModal').modal('hide');
        });

        $(document).on('shown.bs.modal', '#emailComposerSelectRecipientCTModal', function(){
            $('#emailComposerPreviewModal').modal('hide');
        });

        $(document).on('shown.bs.modal', '#emailComposerPreviewModal', function(){
            $('#emailComposerModal').modal('hide');
        });

        $(document).on('shown.bs.modal', '#emailComposerModal', function(){
            $('#emailComposerModal .note-editable').trigger('focus');
        });

        $(document).on('shown.bs.modal', '#emailComposerPreviewModal', function(){
            $('[data-id=emailMessage]').html($('#message-input').code());
        });

        var emailComposerVue = new Vue({
            el: '#emailComposer',
            methods: {
                updateMessage: function(){
                    $('[data-id=emailMessage]').html($('#message-input').code());
                    $('[data-id=employerName]').html($('#inviter-name-input').val());
                },
                saveAsDraft: function(event){
                    var button = $(event.target);
                    button.prop('disabled', true);
                    $.ajax({
                        url: "{{ route('projects.tender.reminder.saveDraft', array($project->id, $tender->id)) }}",
                        method: 'POST',
                        data: {
                            _token: '{{{ csrf_token() }}}',
                            message: $('#message-input').code()
                        },
                        success:function(data){
                            $.smallBox({
                                title : "{{ trans('general.success') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('forms.draftSaved') }}</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-save",
                                timeout : 5000
                            });

                            button.prop('disabled', false);
                        },
                        error: function(){
                            $.smallBox({
                                title : "{{ trans('forms.anErrorOccured') }}",
                                content : "<i class='fa fa-times-circle'></i> <i>{{ trans('forms.draftCouldNotBeSaved') }}</i>",
                                color : "#C46A69",
                                sound: true,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });

                            button.prop('disabled', false);
                        }
                    });
                }
            }
        });

        $('#emailComposer [data-action=saveAsDraft]').on('click', function(e){
            emailComposerVue.saveAsDraft(e);
        });

        function updateContractLimitVisibility(tabPaneId){
            if($('#'+tabPaneId+' input[type=checkbox][name=technical_evaluation_required]').prop('checked')){
                $('#'+tabPaneId+' [data-id=contract-limit-section]').show();
            }else{
                $('#'+tabPaneId+' [data-id=contract-limit-section]').hide();
            }
        }

        $('#{{{ PCK\Forms\TenderRecommendationOfTendererInformationForm::TAB_ID }}} input[type=checkbox][name=technical_evaluation_required]').change(function(){
            updateContractLimitVisibility('{{{ PCK\Forms\TenderRecommendationOfTendererInformationForm::TAB_ID }}}');
        });

        $('#{{{ PCK\Forms\TenderListOfTendererInformationForm::TAB_ID }}} input[type=checkbox][name=technical_evaluation_required]').change(function(){
            updateContractLimitVisibility('{{{ PCK\Forms\TenderListOfTendererInformationForm::TAB_ID }}}');
        });
    </script>
    <!-- Tender interview -->
    <script>
        function instantiateClockpicker(){
            $('.discussion-time-clockpicker').clockpicker({
                placement: 'left'
            });

            $('.clockpicker').clockpicker({
                placement: 'top'
            });
        }
        $('#tenderInterviewModal').on('shown.bs.modal', function(){
            instantiateClockpicker();
        });

        var tenderInterviewtable = $('#tenderInterviewTable').DataTable({
            sDom: 't',
            paging: false,
            scrollCollapse: true,
            bServerSide:true,
            sServerMethod:'POST',
            sAjaxSource: "{{ route('projects.tender.tenderInterview.get', array($project->id, $tender->id)) }}",
            fnServerParams: function(aoData) {
                var selectedContractors = [];
                @foreach($selectedContractors as $contractor)
                    @if(!$contractor->pivot->deleted_at)
                        selectedContractors.push({{{ $contractor->id }}});
                    @endif
                @endforeach
                aoData.push({name: "selectedContractors", value: selectedContractors});
                aoData.push({name: "_token", value: "{{{ csrf_token() }}}"});
            },
            aoColumnDefs: [{
                    aTargets: [ 0 ],
                    "mData": function ( source, type, val ) {
                        return source['indexNo'];
                    },
                    "sClass": "text-middle text-center occupy-min"
                },{
                    aTargets: [ 1 ],
                    "mData": function ( source, type, val ) {
                        return source['company'];
                    },
                    "sClass": "text-middle text-left"
                },{
                    aTargets: [ 2 ],
                    "mData": function ( source, type, val ) {
                        var data;
                        var defaultTime = source['unmodified'] ? '' : source['time'];
                        @if($modes['edit'])
                                data = '<label class="input"><div class="input-group clockpicker" data-placement="left" data-align="right" data-autoclose="true"><input type="text" data-type="time-input" data-id="'+source['companyId']+'" class="form-control time-input" value="'+defaultTime+'" placeholder="Meeting Time"><span class="input-group-addon"><span class="glyphicon glyphicon-time"></span></span></div></label>';
                        @else
                                data = defaultTime;
                        @endif
                                return data;
                    },
                    "sClass": "text-middle text-center"
                },{
                    aTargets: [ 3 ],
                    "mData": function ( source, type, val ) {
                        return source['status'];
                    },
                    "sClass": "text-middle text-center"
                }
            ]
        });

        tenderInterviewtable.on('draw', function(e, settings){
            instantiateClockpicker();
        });

        $("#tenderInterviewTable thead th input[type=text]").on( 'keyup change', function () {

            tenderInterviewtable
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();

        });

        function getTenderInterviewInfo(){
            var venue = $('#venue-input').val();
            var date = $('#date-input').val();

            $('.time-input').each(function(){
                $(this).val(formatTime($(this).val()));
            });

            var discussionTime = $('#discussion-time-input').val();

            var companies = [];
            @foreach($selectedContractors as $contractor)
                @if(!$contractor->pivot->deleted_at)
                    companies.push({
                        id: '{{{ $contractor->id }}}',
                        time: $('[data-type=time-input][data-id={{{ $contractor->id}}}]').val()
                    });
                @endif
            @endforeach

            return {
                _token: "{{{ csrf_token() }}}",
                venue : venue,
                date : date,
                discussionTime: discussionTime,
                companies: companies
            };
        }

        @if($modes['send'])
            $(document).on('click', '[data-action=send-tender-interview-requests]', function(){
            var self = this;
            $(self).prop('disabled', true);

            $.ajax({
                type: "POST",
                url : "{{ route('projects.tender.tenderInterview.send', array($project->id, $tender->id)) }}",
                data: getTenderInterviewInfo(),
                success: function(data){
                    $(self).prop('disabled', false);

                    if(data['success']){
                        $('#tenderInterviewModal').modal('hide');

                        $.smallBox({
                            title : "Success",
                            content : "<i class='fa fa-check'></i> <i>Interview requests have been sent</i>",
                            color : "#739E73",
                            sound: true,
                            iconSmall : "fa fa-paper-plane",
                            timeout : 5000
                        });
                    }else{
                        $.smallBox({
                            title : "An error occurred",
                            content : "<i class='fa fa-times-circle'></i> <i>Interview requests could not be sent. Please check that all details are keyed in properly</i>",
                            color : "#C46A69",
                            sound: true,
                            iconSmall : "fa fa-exclamation-triangle shake animated"
                        });
                    }
                }
            });
        });
        @endif

        @if($modes['edit'])

            function timeIsFormatted(timeValue){
                return (timeValue.lastIndexOf('AM') > 0) || (timeValue.lastIndexOf('PM') > 0);
            }

            function formatTime(timeValue){
                var newValue = timeValue;
                if( ! timeIsFormatted(timeValue) ){
                    newValue = moment(timeValue, "H:mm").format('h:mm A');
                }
                return newValue;
            }

            $(document).on('blur', '.time-input', function(){
                $(this).val(formatTime($(this).val()));
            });

            $(document).on('click','[data-action=save-tender-interview-info]', function(){
                var self = this;
                $(self).prop('disabled', true);

                $.ajax({
                    type: "POST",
                    url : "{{ route('projects.tender.tenderInterview.update', array($project->id, $tender->id)) }}",
                    data: getTenderInterviewInfo(),
                    success: function(data){
                        $(self).prop('disabled', false);

                        if(data['success']){
                            $.smallBox({
                                title : "Success",
                                content : "<i class='fa fa-check'></i> <i>Details have been saved</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-save",
                                timeout : 5000
                            });
                        }else{
                            $.smallBox({
                                title : "Oops, something went wrong",
                                content : "Please check that all details are keyed in properly",
                                color : "#C46A69",
                                sound: true,
                                timeout : 10000,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        }
                    }
                });
            });
        @endif

        $(document).on('show.bs.modal', '#intervieweePreviewModal,#interviewerPreviewModal', function(){
            tenderInterVue.updatePreview();
        });

        var tenderInterVue = new Vue({
            el: '#tenderInterview',
            methods: {
                updatePreview: function(){
                    var date = $('#date-input').val();
                    var formattedDate = moment(date, "YYYY-MM-DD").format('DD / MM / YYYY (dddd)');
                    $('[data-input=date]').html(formattedDate);
                    $('[data-input=venue]').html($('#venue-input').val());
                    $('[data-input=discussionTime]').html($('#discussion-time-input').val());

                    $('[data-type=time-input]').each(function(){
                        var contractorId = $(this).data('id');
                        var interviewTime = $(this).val();
                        $('[data-id='+contractorId+'][data-type=tender-interview-preview-interview-time]').html(interviewTime);
                    });
                }
            }
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();
            return !input.some(function(element){
                return (element.value > 0);
            });
        }

        @if($tender->getTenderStageInformation())
        <?php $selectContractorsRoute =  $isVendorManagementEnabled ? route('list.of.vm.contractors.get', [$project->id, $tender->id]) : route('list.of.contractors.get', [$project->id, $tender->id]); ?>

        var selectContractorsTable = null;

        @if($isVendorManagementEnabled)
        var columns = [
            { formatter:"rowSelection", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true },
            { title:"{{ trans('vendorManagement.name') }}", field: 'name', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", frozen:true },
            { title:"{{ trans('vendorManagement.vendorGroup') }}", field: 'vendor_group', minWidth:200, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.vendorStatus') }}", field: 'vendor_status', minWidth:100, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.companyStatus') }}", field: 'company_status', minWidth:150, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.vendorCategories') }}", field: 'vendor_categories', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.vendorWorkCategories') }}", field: 'vendor_work_categories', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.vendorSubWorkCategories') }}", field: 'vendor_sub_work_categories', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.watchList') }}", field: 'watch_list_categories', minWidth:300, headerSort:false,
                headerFilterFunc: function(headerValue,cellValue, rowData, filterParams){
                    let isMatch = false;

                    if(headerValue.includes("0")) return true; //check for exclude none
                    if(headerValue.includes("-1") && (Object.keys(rowData['watch_list_categories']).length > 0)) return false; //check for exclude all

                    headerValue.forEach(function(headerValueItem){
                        if(Object.keys(rowData['watch_list_categories']).includes(headerValueItem)){
                            isMatch = true;
                        }
                    });
                    return !isMatch;
                },
                editable: false,
                editor:"select",
                headerFilter:true,
                headerFilterPlaceholder: "{{ trans('general.exclude') }}",
                formatter:function(cell){
                    let data = cell.getData()['watch_list_categories'];
                    let formattedData = [];
                    for(key in data){
                        formattedData.push('<span class="label label-warning text-white">'+data[key]+'</span>');
                    }

                    return formattedData.join('&nbsp;');
                },
                headerFilterParams: function(){
                    let values = {0: 'Exclude none', "-1": 'Exclude all'};
                    let data = this.getData();
                    for(idx in data){
                        for(categoryId in data[idx]['watch_list_categories']){
                            values[categoryId] = data[idx]['watch_list_categories'][categoryId];
                        }
                    }

                    return {values:values, multiselect:true};
                }
            },
            { title:"{{ trans('companies.country') }}", field: 'country', minWidth:100, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('companies.state') }}", field: 'state', minWidth:100, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
        ];
        @else
        var columns = [
            { formatter:"rowSelection", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, frozen:true },
            { title:"{{ trans('general.name') }}", field: 'name', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('vendorManagement.vendorGroup') }}", field: 'vendor_group', minWidth:200, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('tenders.typeOfWork') }}", field: 'work_categories', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('tenders.subCategory') }}", field: 'work_sub_categories', minWidth:300, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('projects.country') }}", field: 'country', minWidth:100, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
            { title:"{{ trans('projects.state') }}", field: 'state', minWidth:100, headerSort:false, headerFilter:"input", headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}" },
        ];
        @endif

        $('#{{ $selectContractorModalId }}').on('show.bs.modal', function(e) {
            selectContractorsTable = new Tabulator('#selectContractorsTable', {
                height:500,
                pagination:"local",
                paginationSize:200,
                columns: columns,
                layout:"fitColumns",
                ajaxURL: "{{ $selectContractorsRoute }}",
                movableColumns:true,
                placeholder:"{{ trans('general.noRecordsFound') }}",
                columnHeaderSortMulti:false,
                selectable:true,
                dataLoaded: function(data){
                    @if($isVendorManagementEnabled)
                        this.getColumn('watch_list_categories').updateDefinition({}); // update columnn to re-run headerFilterParams
                    @endif
                },
                rowSelectionChanged: function(data, rows) {
                    $('#{{ $selectContractorModalId }}').find('[data-action="actionSave"]').prop('disabled', (rows.length <= 0));
                },
            });
        });

        $('#{{ $selectContractorModalId }} [data-action="actionSave"]').on('click', function(e) {
            e.preventDefault();

            $(this).prop('disabled', true);

            var url = window.location.href;
            var selectedContractorIds = [];
            var checkedContractors = [];

            var currentlySavedContractorIds = {{ json_encode($tender->getTenderStageInformation()->selectedContractors->lists('id')) }};

            selectContractorsTable.getSelectedData().forEach(function(item, index, arr) {
                selectedContractorIds.push(item.id);
            });

            checkedContractors = currentlySavedContractorIds.concat(selectedContractorIds);

            $.ajax({
                type: "POST",
                url : "{{ $saveSelectedContractorRoute }}",
                data: {
                    _token: "{{{ csrf_token() }}}",
                    _method: "PUT",
                    checkedContractors : checkedContractors
                },
                success: function(tabId){
                    // cut off previous #tabId
                    var indexOfHash = url.indexOf('#');

                    if(indexOfHash > -1){
                        url = url.slice(0, indexOfHash);
                    }

                    url = url+'#'+tabId;
                    location.href = url;

                    location.reload();
                }
            });
        });

        $('[data-action="show-vendor-profile"]').on('click', function(e) {
            e.preventDefault();

            $('#vendorProfileModal').data('id', $(this).data('id'));
            $('#vendorProfileModal').modal('show');
        })

        $('#vendorProfileModal').on('show.bs.modal', function(e) {
            var id = $(this).data('id');

            $('#vp-vendor_categories').val(null).trigger('change');
            $('#vp-cidb_codes').val(null).trigger('change');
            $('#contractor-section').hide();
            $('#consultant-section').hide();
            $('#vp-vendor_performance_evaluation-rows').empty();

            var url = "{{ route('vendorManagement.vendorProfile.get', ':id')}}";
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
                    $.each(data.details, function(key,val){
                    if(key=='cidb_codes'){
                        $.each(val, function(k,v){
                            $('#vp-cidb_codes').append('<option selected>'+v+'</option>').trigger('change');
                        });
                    }
                });
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
    </script>
@endsection