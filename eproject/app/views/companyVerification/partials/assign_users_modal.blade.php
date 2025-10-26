<?php $modalId = 'assignUsersModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg fill-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-check-square"></i>
                        {{{ trans('companyVerification.assignUsers') }}}
                    <i class="fa fa-users"></i>
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ trans('forms.close') }}</span></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table  table-hover" id="assign-users-table">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th class="hasinput">
                                <input type="text" class="form-control" placeholder="{{{ trans('tables.filter') }}}"/>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th style="width: 5%;">{{{ trans('users.number') }}}</th>
                            <th style="width: auto;">{{{ trans('users.name') }}}</th>
                            <th style="width: 15%;" class="text-center">{{{ trans('users.email') }}}</th>
                            <th class="text-center">{{{ trans('users.company') }}}</th>
                            <th class="text-center">{{{ trans('companyVerification.assign') }}}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ trans('forms.close') }}</button>

                <h4 class="pull-right">&nbsp</h4>

                <input type="button" data-action="assign-users-submit" class="btn btn-primary pull-right" value="{{trans('forms.save')}}"/>

            </div>
        </div>
    </div>
</div>