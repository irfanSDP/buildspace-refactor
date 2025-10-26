@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('eBidding.edit_ebidding') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-users"></i> {{{ trans('eBidding.assignCommittees') }}}
            </h1>
        </div>
    </div>

    <div class="well">
        	{{ Form::open(array('method' => 'PUT')) }}
        		<div class="table-responsive">
                    @if($buContractGroup)
                        <h2>{{ trans('eBidding.assignUserFromGroup') }} ({{{ $project->getRoleName($buContractGroup->group) }}}) to {{ trans('eBidding.ebidding') }}</h2>
                        <table class="table" style="text-align: center;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;width:100px;">{{ trans('users.committee') }}</th>
                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                    <th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                    <th style="text-align: center;width:100px;">{{ trans('users.admin') }}</th>
                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($buCommittees as $user)
                                    @if(isset($buAssigned[$user->id]))
                                    <tr>
                                        <td>
                                            {{ Form::checkbox('is_committee[]', $user->id, isset($buAssignedCommittees[$user->id]) ? true : false, array('data-channel' => 'editor', 'data-validate_url' => route('projects.editor.remove.validate', [$project->id, $user->id]))) }}
                                        </td>
                                        <td style="text-align: left;">{{{ $user->name }}}</td>
                                        <td>{{{ $user->designation }}}</td>
                                        <td>@if($buCompany->isCompanyAdmin($user)) {{{ trans('forms.yes') }}} @endif</td>
                                        <td>{{{ $user->email }}}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($gcdContractGroup)
                        <h2>{{ trans('eBidding.assignUserFromGroup') }} ({{{ $project->getRoleName($gcdContractGroup->group) }}}) to {{ trans('eBidding.ebidding') }}</h2>
                        <table class="table" style="text-align: center;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;width:100px;">{{ trans('users.committee') }}</th>
                                    <th style="text-align: left;">{{ trans('users.name') }}</th>
                                    <th style="text-align: center;width:200;">{{ trans('users.designation') }}</th>
                                    <th style="text-align: center;width:100px;">{{ trans('users.admin') }}</th>
                                    <th style="text-align: center;width:220px;">{{ trans('users.email') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gcdCommittees as $user)
                                    @if(isset($gcdAssigned[$user->id]))
                                        <tr>
                                            <td>
                                                {{ Form::checkbox('is_committee[]', $user->id, isset($gcdAssignedCommittees[$user->id]) ? true : false, array('data-channel' => 'editor', 'data-validate_url' => route('projects.editor.remove.validate', [$project->id, $user->id]))) }}
                                            </td>
                                            <td style="text-align: left;">{{{ $user->name }}}</td>
                                            <td>{{{ $user->designation }}}</td>
                                            <td>@if($gcdCompany->isCompanyAdmin($user)) {{{ trans('forms.yes') }}} @endif</td>
                                            <td>{{{ $user->email }}}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
        			<div class="form-group">
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary pull-right', 'style' => 'margin-left:10px;'] )  }}
                        <a href="{{ $backButtonUrl }}" class="btn btn-default pull-right">{{ trans('forms.back') }}</a>
        			</div>
        		</div>
        	{{ Form::close() }}
    </div>
@endsection