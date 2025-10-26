<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <div role="content">
            <div class="widget-body">
                <!-- widget content -->
                <div class="widget-body no-padding">
                {{ Form::model($openTenderRequirement, array('class'=>'smart-form', 'id'=>'tender-requirement-form','route' => array('projects.tender.update_open_tender_tender_requirements', $project->id, $tenderId), 'method' => 'PUT')) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                {{ Form::textarea('description', Input::old('description'), array('class' => 'form-control padded-less-left summernote')) }}
                                {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    @if($approvalStatus != PCK\Tenders\OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL)
                    <footer>
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('openTender.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
                    </footer>
                    @endif
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>