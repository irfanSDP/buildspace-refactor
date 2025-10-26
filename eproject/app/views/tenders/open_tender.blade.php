@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote/summernote.min.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tender.index', trans('navigation/projectnav.tenders'), array($project->id)) }}</li>
        <li><a href="{{route('projects.tender.show', array( $tender->project->id, $tender->id )) . '#s2'}}">{{trans('tenders.listOfTenderersInformation')}}</a></li>
        <li>{{{ trans('openTender.openTender') }}}</li>
    </ol>
    @include('projects.partials.project_status')
@endsection

@section('content')
<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <div role="content">
            <div class="widget-body">
                <ul id="open-tender-tab" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#tenderInfo" data-toggle="tab">{{{ trans('openTender.tenderInfo') }}}</a>
                    </li>
                    <li>
                        <a href="#tenderDocuments" data-toggle="tab">{{{ trans('openTender.tenderDocuments') }}}</a>
                    </li>
                    <li>
                        <a href="#tenderRequirements" data-toggle="tab">{{{ trans('openTender.tenderRequirements') }}}</a>
                    </li>
                    <li>
                        <a href="#industryCode" data-toggle="tab">{{{ trans('openTender.industryCode') }}}</a>
                    </li>
                    <li>
                        <a href="#announcementInfo" data-toggle="tab">{{{ trans('openTender.announcementInfo') }}}</a>
                    </li>
                    <li>
                        <a href="#personInCharge" data-toggle="tab">{{{ trans('openTender.personInCharge') }}}</a>
                    </li>
                </ul>
                <div id="myTabContent1" class="tab-content" style="padding: 30px!important;">
                    <div class="tab-pane active" id="tenderInfo">
                        <div class="widget-body" class="form-group">
                            @include('tenders.partials.open_tender_information_form', array('disabled' => $disabled))
                        </div>
                    </div>
                    <div class="tab-pane" id="tenderDocuments">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('tenders.partials.open_tender_tender_document_index', array('project'=>$project, 'tenderId' => $tenderId, 'documentRecords'=>$documentRecords))
                        </div>
                    </div>
                    <div class="tab-pane" id="tenderRequirements">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('tenders.partials.open_tender_tender_requirements_form')
                        </div>
                    </div>
                    <div class="tab-pane" id="industryCode">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                        @include('tenders.partials.open_tender_industry_code_index', array('project'=>$project, 'tenderId' => $tenderId,'industryCodeRecords'=>$industryCodeRecords))
                        </div>
                    </div>
                    <div class="tab-pane" id="announcementInfo">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('tenders.partials.open_tender_announcement_index', array('project'=>$project, 'tenderId' => $tenderId, 'announcementRecords'=>$announcementRecords))
                        </div>
                    </div>
                    <div class="tab-pane" id="personInCharge">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('tenders.partials.open_tender_person_in_charge_index', array('project'=>$project, 'tenderId' => $tenderId, 'personInChargeRecords'=>$personInChargeRecords))
                        </div>
                    </div>
                </div>
            </div>
            <footer>
                {{ link_to_route('projects.tender.index', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
            </footer>
        </div>
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>

@include('tenders.partials.verifier_remarks_modal')
@include('tenders.partials.verifier_log_modal')

@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/summernote/summernote.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.datetimepicker').datepicker({
                dateFormat : 'yy-mm-dd',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                showTodayButton: true,
            });

            $('.datetimepickerTimeSelection').datetimepicker({
                format: 'DD-MMM-YYYY hh:mm A',
                stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                showTodayButton: true,
                allowInputToggle: true
            });

            $('.summernote').summernote({
                focus: true,
                disableResizeEditor: true,
                placeholder: "{{ trans('openTender.message') }}",
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['insert', ['link', 'picture', 'table', 'hr']],
                    ['color', ['color']],
                    ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                    ['codeview', ['codeview']],
                    ['help', ['help']],
                ]
            });

            if($('#special_permission').prop("checked")){
                $('#special_permission_true').show();
            }

            $('#special_permission').change(function(object){
                if($(this).prop("checked")){
                    $('#special_permission_true').show();
                }
                else{
                    $('#special_permission_true').hide();
                }
            });
        
            
            $("form").on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });

            var formType = {{json_encode($form)}}
            console.log(formType);

            $('a[href="#' + formType + '"]').tab('show');


            $('#btnViewLogs').on('click', function(e) {
				e.preventDefault();
				$('#openTenderPageInformationVerifierLogModal').modal('show');
			});

            $('#verifierForm button[name=approve], #verifierForm button[name=reject]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#openTenderPageInformationRejectRemarksModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#openTenderPageInformationApproveRemarksModal').modal('show');           
                } 
			});

            $('button#verifier_approve_open_tender_page_information_submit_btn, button#verifier_reject_open_tender_page_information_submit_btn').on('click', function(e) {
				e.preventDefault();

				var remarksId;

                console.log("button clicked");
				            
				switch(this.id) {
					case 'verifier_approve_open_tender_page_information_submit_btn':
						var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
						$('#verifierForm').append(input);
						remarksId = 'approve_verifier_remarks';
						break;
					case 'verifier_reject_open_tender_page_information_submit_btn':
						remarksId = 'reject_verifier_remarks';
						break;
				}

				if($('#'+remarksId)){
					$('#verifierForm').append($("<input>")
					.attr("type", "hidden")
					.attr("name", "verifier_remarks").val($('#'+remarksId).val()));
				}

                $('#verifierForm').submit();
            });
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();

            return !input.some(function(element){
                return (element.value > 0);
            });
        }

    </script>
@endsection