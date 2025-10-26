<div class="modal" id="assign-blocked-users-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-color-red">
                <h4 class="modal-title txt-color-white">
                    <i class="fa fa-user-slash"></i> {{ trans('companies.blockedUsers') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" style="text-align: center;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">{{ trans('users.name') }}</th>
                                <th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                <th style="text-align: center;width:100px;">{{ trans('users.admin') }}</th>
                                <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($blockedUsers as $user)
                            <tr class="alert alert-warning">
                                <td style="text-align: left;">{{{ $user->name }}}</td>
                                <td>{{{ $user->designation }}}</td>
                                <td>@if($company->isCompanyAdmin($user)) {{{ trans('forms.yes') }}} @endif</td>
                                <td>{{{ $user->email }}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($blockedImportedUsers->count() > 0)
                        <h1 class="pull-left"><i class="fa fa-user"></i><i class="fa fa-plus"></i> {{ trans('users.importedUsers') }}</h1>
                        <table class="table " style="text-align: center;">
                            <thead>
                            <tr>
                                <th style="text-align: center;width:100px;">{{ trans('users.editor') }}</th>
                                <th style="text-align: left;">{{ trans('users.name') }}</th>
                                <th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($blockedImportedUsers as $user)
                                <tr class="alert alert-warning">
                                    <td>
                                    @if(isset($assignedUsers[$user->id]) and $assignedUsers[$user->id])
                                        {{ Form::button('<i class="fa fa-user-friends"></i>', ['type' => 'button', 'class' => 'btn btn-success btn-xs', 'title'=>'Reassign User'] ) }}

                                        {{ Form::button('<i class="fa fa-user-slash"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-xs', 'title'=>'Unassign User'] ) }}
                                    @endif
                                    </td>
                                    <td style="text-align: left;">{{{ $user->name }}}</td>
                                    <td>{{{ $user->designation }}}</td>
                                    <td>{{{ $user->email }}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>