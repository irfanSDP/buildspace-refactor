@if(PCK\SiteManagement\SiteManagementDefect::checkStatus($form_id) == 
    PCK\SiteManagement\SiteManagementDefect::STATUS_BACKCHARGE || 
    PCK\SiteManagement\SiteManagementDefect::checkStatus($form_id) == 
    PCK\SiteManagement\SiteManagementDefect::STATUS_BACKCHARGE_REJECTED)

    @if(PCK\SiteManagement\SiteManagementUserPermission::isSiteUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))

    <!-- Backcharge Form Start -->

        <div class="row">
        <!-- NEW COL START -->
        <article class="col-sm-12 col-md-12 col-lg-12">
            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget">
                <header>
                    <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                     <h2>{{{ trans('siteManagementDefect.backcharge') }}}</h2> 
                </header>

                <!-- widget div-->
                <div>
                    <!-- widget content -->
                    <div class="widget-body no-padding">
                         {{ Form::open(array('class'=>'smart-form','route' => array('site-management-defect.storeBackcharge', $project->id, $form_id))) }}
                             <fieldset id="form">
                                <h2><strong>{{{$project->modified_currency_code}}}&nbsp;&nbsp;{{{ trans('siteManagementDefect.lump-sum') }}}</strong></h2>  
                                <section>
                                    <label for="machinery">{{{ trans('siteManagementDefect.machinery') }}}</label>
                                    <input class="form-control" type="number" step="0.01" name="machinery" id="machinery" value="{{{(int)(Input::old('machinery'))}}}">
                                    {{ $errors->first('machinery', '<em class="invalid">:message</em>') }}
                                </section>
                                <section>
                                    <label for="material">{{{ trans('siteManagementDefect.material') }}}</label>
                                    <input class="form-control" type="number" step="0.01" name="material" id="material" value="{{{(int)(Input::old('material'))}}}">
                                    {{ $errors->first('material', '<em class="invalid">:message</em>') }}
                                </section>
                                <section>
                                    <label for="labour">{{{ trans('siteManagementDefect.labour') }}}</label>
                                    <input class="form-control" type="number" step="0.01" name="labour" id="labour" value="{{{(int)(Input::old('labour'))}}}">
                                    {{ $errors->first('labour', '<em class="invalid">:message</em>') }}
                                </section>
                                <section>
                                    <label for="total">{{{ trans('siteManagementDefect.total') }}}</label>
                                    <input class="form-control" type="number" step="0.01" name="total" id="total">
                                </section>
                                @include('verifiers.select_verifiers')
                            </fieldset>
                            <footer>
                                {{ Form::submit(trans('siteManagementDefect.submit'), array('class' => 'btn btn-default', 'name' => 'Submit')) }}
                                {{ link_to_route('site-management-defect.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                    <!-- end widget content -->
                </div>
                <!-- end widget div -->
            </div>
            <!-- end widget -->
        </article>
        <!-- END COL -->
        </div>
    <!-- Backcharge Form End -->

    @endif

@elseif(PCK\SiteManagement\SiteManagementDefect::checkStatus($form_id) != 
        PCK\SiteManagement\SiteManagementDefect::STATUS_CLOSED &&
        (! PCK\SiteManagement\SiteManagementDefectBackchargeDetail::checkRecordExists($form_id)))


        @if(PCK\SiteManagement\SiteManagementUserPermission::isSiteUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
            PCK\SiteManagement\SiteManagementUserPermission::isPmUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project) ||
            $user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR))

        <!-- Respond Form Start -->

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                    {{ Form::open(array('route' => array('site-management-defect.storeResponse', $project->id, $form_id), 'files' => true)) }}

                    <fieldset id="form">
                        <h3>{{{ trans('siteManagementDefect.respond') }}}</h3>  
                        <div class="row">
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <label for="remark">{{{ trans('siteManagementDefect.remark') }}}</label>
                            </div>
                            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                                <textarea class="form-control" rows="5" name="remark" id="remark" value="{{{Input::old('remark')}}}"></textarea>
                                {{ $errors->first('remark', '<em class="invalid">:message</em>') }}
                            </div>
                        </div>
                        <br><br>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label class="label">{{{ trans('siteManagement.attachments') }}}:</label>
                                @include('file_uploads.partials.upload_file_modal')
                            </section>
                        </div>
                        <br><br>
                        @if(PCK\SiteManagement\SiteManagementUserPermission::isSiteUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
                            <div>
                                <button id="response" class="btn btn-success btn-md header-btn" type="submit" name="response" value="1">
                                    {{{ trans('siteManagementDefect.accept') }}}
                                </button>
                                <button id="response" class="btn btn-warning btn-md header-btn" type="submit" name="response" value="2">
                                    {{{ trans('siteManagementDefect.reject') }}}
                                </button>
                                <button id="response" class="btn btn-danger btn-md header-btn" type="submit" name="response" value="3">
                                    {{{ trans('siteManagementDefect.backcharge') }}}
                                </button>

                            </div>
                        @elseif(PCK\SiteManagement\SiteManagementUserPermission::isPmUser(PCK\SiteManagement\SiteManagementUserPermission::MODULE_IDENTIFIER_DEFECT, $user, $project))
                                <button id="response" class="btn btn-success btn-md header-btn" type="submit" name="response" value="1">
                                    {{{ trans('siteManagementDefect.accept') }}}
                                </button>
                                <button id="response" class="btn btn-warning btn-md header-btn" type="submit" name= "response" value="2">
                                    {{{ trans('siteManagementDefect.reject') }}}
                                </button>
                                @if(PCK\SiteManagement\SiteManagementDefectBackchargeDetail::checkRecordExists($form_id) == NULL)
                                <button id="response" class="btn btn-danger btn-md header-btn" type="submit" name="response" value="3">
                                    {{{ trans('siteManagementDefect.backcharge') }}}
                                </button>
                                @endif
                                @if($record->mcar_status == PCK\SiteManagement\SiteManagementMCAR::MCAR_NONE)
                                    <button id="response" class="btn btn-info btn-md header-btn" type="submit" name= "response" value="4">
                                        {{{ trans('siteManagementDefect.mcar') }}}
                                    </button>
                                @endif
                        @elseif($user->hasCompanyProjectRole($project, PCK\ContractGroups\Types\Role::CONTRACTOR))
                            <div>
                                <button id="response" class="btn btn-success btn-md header-btn" type="submit" name= "response" value="5">
                                    {{{ trans('siteManagementDefect.submit') }}}
                                </button>
                                <a href="{{ route('site-management-defect.index',$project->id )}}">
                                    <div class="btn btn-info btn-md header-btn" >{{{ trans('siteManagementDefect.back') }}}</div>
                                </a>
                            </div>
                        @endif
                    </fieldset>
                    {{ Form::close()}}
                </div>
            </div>

        <!-- Respond Form End -->

        @endif
@endif