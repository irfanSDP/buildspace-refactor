@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.submitted-defect') }}}</li>
    </ol>

@endsection

@section('content')
    <style>
        .horizontal_dashed {
            border-top:1px dashed #000;
        }
    </style>

    <div class="modal fade" id="enlargeImageModal" tabindex="-1" role="dialog" aria-labelledby="enlargeImageModal" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <img src="" class="enlargeImageModalSource" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <?php
        $locations = PCK\Buildspace\ProjectStructureLocationCode::where("root_id",$record->projectStructureLocationCode->root_id)->where("level", "<=",$record->projectStructureLocationCode->level)->where('lft', '<=',$record->projectStructureLocationCode->lft )->where('rgt', '>=',$record->projectStructureLocationCode->rgt )->orderBy("level", "asc")->get();
    ?>
    
    <div class="row">
        <article class="col col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
        <div class="jarviswidget jarviswidget-sortable">
            <header role="heading">
                <span class="widget-icon"> <i class="fa fa-edit"></i> </span>

                <h2>{{{ trans('siteManagementDefect.submitted-defect') }}}</h2>
            </header>

            <!-- widget div-->
            <div role="content">
                <!-- widget content -->
                <div class="widget-body no-padding">
                    <div class="smart-form">
                        <fieldset>
                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.contractor') }}}&#58;</label>
                                <label class="input">
                                    @if($record->contractor_id == NULL)
                                     {{{ trans('siteManagementDefect.not-selected') }}} 
                                     @else
                                    {{{$record->company->name}}}
                                    @endif
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.category') }}}&#58;</label>
                                <label class="input">
                                    {{{$record->defect_category->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.location') }}}&#58;</label>
                                <label class="input">
                                    @foreach($locations as $location)
                                    {{{$location->description}}}<br>
                                     @endforeach
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.defect') }}}&#58;</label>
                                <label class="input">
                                    @if($record->defect_id == NULL)
                                     {{{ trans('siteManagementDefect.not-selected') }}} 
                                     @else
                                     {{{$record->defect->name}}}
                                     @endif
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.trade') }}}&#58;</label>
                                <label class="input">
                                    {{{$record->preDefinedLocationCode->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.remark') }}}&#58;</label>
                                <label class="input">
                                    {{{$record->remark}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.date-submitted') }}}&#58;</label>
                                <label class="input">
                                   {{{$project->getProjectTimeZoneTime($record->created_at)}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.pic') }}}&#58;</label>
                                <label class="input">
                                    @if($record->pic_user_id == NULL)
                                        <b>{{{ trans('siteManagementDefect.not-assigned') }}}</b>
                                     @else
                                        {{{$record->user->name}}}
                                     @endif
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.submitted-by') }}}</label>
                                <label class="input">
                                    {{{$record->submittedUser->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.photo') }}}&#58;</label>
                                @include('site_management.uploaded_files')
                            </section>
                        </fieldset>
                    </div>
                </div>
                <!-- end widget content -->
            </div>
            <!-- end widget div -->
        </div>
        </article>
    </div>

    <hr>

    <!-- Response Details start -->

    @if(PCK\SiteManagement\SiteManagementDefectFormResponse::checkRecordExists($form_id))

    <div class="row">
        <article class="col col-sm-12 col-md-12 col-lg-12">
                <div style="padding-bottom:6px;cursor:pointer;" data-toggle="collapse" data-target="#responses">
                    <strong>
                        {{{ trans('siteManagementDefect.previous-responses') }}} <i class="fa fa-lg fa-chevron-circle-down pull-right"></i>
                    </strong>
                </div>
                
                <div class="collapse" id="responses">
                    @foreach($responses as $response)
                        <?php
                            $status = PCK\SiteManagement\SiteManagementDefectFormResponse::getResponseText($response->response_identifier);
                            $uploadedResponseFilesId = PCK\ModuleUploadedFiles\ModuleUploadedFile::where('uploadable_type', get_class($response))->where('uploadable_id', $response->id)->lists('upload_id');
                            $uploadedResponseItems = PCK\Base\Upload::whereIn('id', $uploadedResponseFilesId)->get();
                        ?>
                        <fieldset>
                            <div class="well">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{{ trans('siteManagementDefect.submitted-by') }}} :</dt>
                                    <dd>{{{$response->user->name}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.remark') }}} :</dt>
                                    <dd>{{{$response->remark}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.status') }}} :</dt>
                                    <dd><b>{{{$status}}}</b></dd>
                                    <dt>{{{ trans('siteManagementDefect.date-submitted') }}} :</dt>
                                    <dd>{{{$project->getProjectTimeZoneTime($response->created_at)}}}</dd>
                                </dl>
                            </div>
                            <section>
                                @if(count($uploadedResponseItems) > 0)
                                    <div class="table-responsive" style="overflow:hidden;">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('siteManagement.filename') }}</th>
                                                    <th style="width:110px;text-align:center;">{{ trans('siteManagement.uploaded') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($uploadedResponseItems as $uploadedResponseItem)
                                                <tr>
                                                    <td>
                                                        <a href="{{{$uploadedResponseItem->getDownloadUrlAttribute()}}}" download>{{{$uploadedResponseItem->filename}}}</a>
                                                    </td>
                                                    <td style="width:110px;text-align:center;">
                                                       <img id="uploaded-item" src="{{{$uploadedResponseItem->getDownloadUrlAttribute()}}}" style="width:100px;height:100px;border:1px solid black;border-radius: 4px;"/>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-warning fade in">{{ trans('siteManagement.no_photo_uploaded') }}</div>
                                @endif
                            </section>
                        </fieldset>
                        <hr class="horizontal_dashed">
                    @endforeach
                </div>
                <hr>
        </article>
    </div>

    @endif

    <!-- Response Details End -->


    <!-- Backcharge Details Start-->

    @if(PCK\SiteManagement\SiteManagementDefectBackchargeDetail::checkRecordExists($form_id))

    <div class="row">
        <article class="col col-sm-12 col-md-12 col-lg-12">
            <div style="padding-bottom:6px;cursor:pointer;" data-toggle="collapse" data-target="#backcharge_details">
                <strong>
                    {{{ trans('siteManagementDefect.backcharge-details') }}} <i class="fa fa-lg fa-chevron-circle-down pull-right"></i>
                </strong>
            </div>

                <div class="collapse" id="backcharge_details">
                @foreach($backcharges as $backcharge)
                <?php
                    $status = PCK\SiteManagement\SiteManagementDefectBackchargeDetail::getStatusText($backcharge->status_id);
                    foreach($verifierRecords = \PCK\Verifier\Verifier::getAssignedVerifierRecords($backcharge) as $record)
                    {
                        $selectedVerifiers[] = $record->verifier;
                    }
                ?>
                        <fieldset>
                            <div class="well">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{{ trans('siteManagementDefect.machinery') }}} :</dt>
                                    <dd>{{{$project->modified_currency_code}}}&nbsp;{{{$backcharge->machinery}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.material') }}} :</dt>
                                    <dd>{{{$project->modified_currency_code}}}&nbsp;{{{$backcharge->material}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.labour') }}} :</dt>
                                    <dd>{{{$project->modified_currency_code}}}&nbsp;{{{$backcharge->labour}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.total') }}} :</dt>
                                    <dd>{{{$project->modified_currency_code}}}&nbsp;{{{$backcharge->total}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.status') }}} :</dt>
                                    <dd><b>{{{$status}}}</b></dd>
                                    <dt>{{{ trans('siteManagementDefect.date-submitted') }}} :</dt>
                                    <dd>{{{$project->getProjectTimeZoneTime($backcharge->created_at)}}}</dd>
                                    <dt>{{{ trans('siteManagementDefect.submitted-by') }}} :</dt>
                                    <dd>{{{$backcharge->user->name}}}</dd>
                                </dl>
                            </div>
                            
                            @include('verifiers.verifier_status_overview')
                        </fieldset>
                        <hr class="horizontal_dashed">
                @endforeach
                </div>

                <hr>
                
        </article>

    </div>

    @endif

    <!-- Backcharge Details End -->

    @include('site_management_defect.respond_form')

@endsection


@section('js')
<script src="{{asset('js/angular.min.js')}}"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('img#uploaded-item').on('click', function() {
        $('.enlargeImageModalSource').attr('src', $(this).attr('src'));
        $('#enlargeImageModal').modal('show');
    });

    $('button[id="response"]').on('click', function(){
        app_progressBar.toggle();
        app_progressBar.maxOut();

    });

    $('input[type=number]').on('keyup', function(){
        doSum();
    });

});

function doSum(){

     var my_input1 = document.getElementById('machinery').value;
     var my_input2 = document.getElementById('material').value;
     var my_input3 = document.getElementById('labour').value;

     var sum = parseFloat(my_input1) + parseFloat(my_input2)+ parseFloat(my_input3);
     document.getElementById('total').value = sum;
}

</script>
    
@endsection