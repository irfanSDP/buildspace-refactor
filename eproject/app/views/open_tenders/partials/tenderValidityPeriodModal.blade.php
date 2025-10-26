<div class="modal fade" id="tenderValidityPeriodModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-color-green">
                <h6 class="modal-title" id="myModalLabel">
                    Tender Validity Period
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            {{ Form::model($tender, array('class' => '', 'method' => 'PUT', 'route' => array('projects.openTender.validityPeriod.update', $project->id, $tender->id))) }}
            <div class="modal-body">
                <div class="row">
                    <article class="col-sm-12 col-md-12 col-lg-12">
                        <div class="well">
                            <label for="tender_validity_period-input"></label>
                            <label class="input">
                                <input v-model="numberOfDays" v-on="keyup: onKeyUp" name="validity_period_in_days" type="number" id="tender_validity_period-input" class="pull-right form-control" style="width:120px;" value="{{{ ($tender->validity_period_in_days) ? $tender->validity_period_in_days : 0 }}}"/>
                            </label>
                            <label style="height:38px;vertical-align:middle;">Day(s)</label>
                            <p>Valid until: <span class="text-success">[ @{{ dateView }} ]</span></p>
                        </div>
                    </article>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="tender_validity_period-save-button">Save</button>
            </div>
            {{ Form::close() }}

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->