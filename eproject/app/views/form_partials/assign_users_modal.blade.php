<?php $modalId = isset($modalId) ? $modalId : 'assignUsersModal' ?>
<?php $title = isset($title) ? $title : trans('users.assignUsers') ?>
<?php $saveButtonLabel = isset($saveButtonLabel) ? $saveButtonLabel : trans('forms.save') ?>
<?php $actionLabel = isset($actionLabel) ? $actionLabel : trans('users.assign') ?>
<div class="modal scrollable-modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-check-square"></i>
                        {{{ $title }}}
                    <i class="fa fa-users"></i>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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
                            <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.number') }}}</th>
                            <th class="text-middle text-left text-nowrap">{{{ trans('users.name') }}}</th>
                            <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.email') }}}</th>
                            <th class="text-middle text-center text-nowrap squeeze">{{{ trans('users.company') }}}</th>
                            <th class="text-middle text-left text-nowrap squeeze">{{{ $actionLabel }}}</th>
                        </tr>
                        </thead>
                        @if(isset($userList))
                            <tbody>
                            <?php $count = 0; ?>
                            @foreach($userList as $user)
                                <tr>
                                    <td class="text-middle text-center text-nowrap squeeze">{{{ ++$count }}}</td>
                                    <td class="text-middle text-left text-nowrap">{{{ $user->name }}}</td>
                                    <td class="text-middle text-center text-nowrap squeeze">{{{ $user->email }}}</td>
                                    <td class="text-middle text-center text-nowrap squeeze">{{{ $user->company->name }}}</td>
                                    <td class="text-middle text-center text-nowrap squeeze">
                                        <input type="checkbox" data-type="user-selection" value="{{{ $user->id }}}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ trans('forms.close') }}</button>

                <h4 class="pull-right">&nbsp;</h4>

                <input type="button" data-action="submit" class="btn btn-primary pull-right" value="{{{ $saveButtonLabel }}}"/>

            </div>
        </div>
    </div>
</div>