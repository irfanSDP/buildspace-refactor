@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.submitted-defect') }}}</li>
    </ol>

@endsection

@section('content')

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
    
    <div class="row">

        <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
        <div class="jarviswidget jarviswidget-sortable">
            <header role="heading">
                <h2>{{{ trans('siteManagementDefect.submitted-defect') }}}</h2>
            </header>

            <!-- widget div-->
            <div role="content">
                <!-- widget content -->
                <div class="widget-body no-padding">
                    <div class="smart-form">
                    <?php
                    $locations = PCK\Buildspace\ProjectStructureLocationCode::where("root_id",$record->projectStructureLocationCode->root_id)->where("level", "<=",$record->projectStructureLocationCode->level)->where('lft', '<=',$record->projectStructureLocationCode->lft )->where('rgt', '>=',$record->projectStructureLocationCode->rgt )->orderBy("level", "asc")->get();
                    ?>
                        <fieldset>
                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.contractor') }}}&#58;</label>
                                <label class="input">
                                    @if($record->contractor_id == NULL)
                                     &nbsp;{{{ trans('siteManagementDefect.not-selected') }}}
                                     @else
                                     &nbsp;{{{$record->company->name}}}
                                    @endif
                                </label>
                            </section>

                             <section>
                                <label class="label">{{{ trans('siteManagementDefect.category') }}}&#58;</label>
                                <label class="input">
                                    &nbsp;{{{$record->defect_category->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.location') }}}&#58;</label>
                                <label class="input">
                                     @foreach($locations as $location)
                                        &nbsp;{{{$location->description}}}<br>
                                     @endforeach
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.defect') }}}&#58;</label>
                                <label class="input">
                                    @if($record->defect_id == NULL)
                                     &nbsp;{{{ trans('siteManagementDefect.not-selected') }}}
                                     @else
                                     &nbsp;{{{$record->defect->name}}}
                                     @endif
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.trade') }}}&#58;</label>
                                <label class="input">
                                    &nbsp;{{{$record->preDefinedLocationCode->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.remark') }}}&#58;</label>
                                <label class="input">
                                    &nbsp;{{{$record->remark}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.date-submitted') }}}&#58;</label>
                                <label class="input">
                                    &nbsp;{{{$project->getProjectTimeZoneTime($record->created_at)}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.pic') }}}&#58;</label>
                                <label class="input">
                                    @if($record->pic_user_id == NULL)
                                        &nbsp;<b>{{{ trans('siteManagementDefect.not-assigned') }}}</b>
                                     @else
                                        &nbsp;{{{$record->user->name}}}
                                     @endif
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.submitted-by') }}}&#58;</label>
                                <label class="input">
                                    &nbsp;{{{$record->submittedUser->name}}}
                                </label>
                            </section>

                            <section>
                                <label class="label">{{{ trans('siteManagementDefect.photo') }}}&#58;</label>
                                @include('site_management.uploaded_files')
                            </section>

                            @if(PCK\SiteManagement\SiteManagementUserPermission::isPmUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
                            {{ Form::open()}}
                                <section>
                                    <label class="label">{{{ trans('siteManagementDefect.assign-pic') }}}&#58;</label>
                                    <select name="site" id="site" class="form-control" required>
                                        <option selected disabled="">Select</option>
                                        @foreach($siteUsers as $siteUser)
                                            <option value="{{{ $siteUser->user_id }}}">
                                                {{{ $siteUser->user->name }}}
                                            </option>
                                        @endforeach
                                    </select>
                                    {{ $errors->first('site', '<em class="invalid">:message</em>') }}
                                </section>
                                <br><br>
                                <footer>
                                    {{ Form::submit(trans('siteManagementDefect.submit'), array('class' => 'btn btn-default', 'name' => 'Submit')) }}
                                    {{ link_to_route('site-management-defect.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                                </footer>
                            {{ Form::close()}}
                            @endif
                        </fieldset>

                    </div>
                </div>
                <!-- end widget content -->
            </div>
            <!-- end widget div -->
        </div>
        </article>

    </div>

@endsection

@section('js')

<script>

$(document).ready(function() {
    $('img#uploaded-item').on('click', function() {
        $('.enlargeImageModalSource').attr('src', $(this).attr('src'));
        $('#enlargeImageModal').modal('show');
    });
});

</script>

@endsection

