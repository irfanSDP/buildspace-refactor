@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
    <li>{{ link_to_route('dashboard.group.index', trans('dashboard.dashboardGroups'), array()) }}</li>
    <li>{{{ $dashboardGroup->getName() }}}</li>
</ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-edit"></i> {{ trans('dashboard.dashboardGroup') }} - {{{$dashboardGroup->getName()}}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <header>
                <ul class="nav nav-tabs pull-right in" id="dashboardGroupTabContent">
                    <li class="active">
                        <a data-toggle="tab" href="#dashboardGroupInfoTabContent"><i class="fa fa-chart-pie"></i> <span class="hidden-mobile hidden-tablet">{{ trans('dashboard.dashboardGroupInfo') }}</span></a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#assignedUsersTabContent"><i class="fa fa-users"></i> <span class="hidden-mobile hidden-tablet">{{ trans('dashboard.users') }}</span></a>
                    </li>
                    @if (in_array($dashboardGroup->type, [\PCK\Dashboard\DashboardGroup::TYPE_DEVELOPER, \PCK\Dashboard\DashboardGroup::TYPE_MAIN_CONTRACTOR]))
                        <li>
                            <a data-toggle="tab" href="#excludedProjectsTabContent"><i class="fa fa-layer-group"></i> <span class="hidden-mobile hidden-tablet">{{ trans('dashboard.excludedProjects')}}</span></a>
                        </li>
                    @endif
                </ul>
            </header>

            <div class="no-padding">
                <div class="widget-body">
                    <div id="dashboardGroupTabContent" class="tab-content">

                        <div id="dashboardGroupInfoTabContent" class="tab-pane fade active in padding-10 no-padding-bottom">
                            @if($user->isSuperAdmin())
                                {{ Form::open(['route' => ['dashboard.group.store', $dashboardGroup->type], 'class' => 'smart-form']) }}
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('projects.title') }}} <span class="required">*</span>:</label>
                                        <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
                                            {{ Form::text('title', Input::old('title', (isset($dashboardGroup->title)) ? $dashboardGroup->title : $dashboardGroup->getName()), ['required' => 'required']) }}
                                        </label>
                                        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                                <footer>
                                    {{ link_to_route('dashboard.group.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                                    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                                </footer>
                                {{ Form::close() }}
                            @else
                                <div class="row">
                                    <div class="col col-lg-12">
                                        <dl class="dl-horizontal no-margin">
                                            <dt>{{{ trans('dashboard.name') }}}:</dt>
                                            <dd>
                                                <div class="well">{{{ $dashboardGroup->getName() }}}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div id="assignedUsersTabContent" class="tab-pane fade">
                            <div class="row" style="padding:4px;">
                                <div class="col col-lg-12">
                                    <button class="btn btn-sm btn-warning pull-right" data-toggle="modal" data-target="#assignUsersModal" data-action="assignUsers">
                                        <i class="fa fa-user-plus"></i> {{trans('dashboard.addUser')}}
                                    </button>
                                </div>
                            </div>

                            <div class="row no-space">
                                <div id="dashboard_group_user_list-table"></div>
                            </div>
                        </div>

                    @if (in_array($dashboardGroup->type, [\PCK\Dashboard\DashboardGroup::TYPE_DEVELOPER, \PCK\Dashboard\DashboardGroup::TYPE_MAIN_CONTRACTOR]))
                        <div id="excludedProjectsTabContent" class="tab-pane fade">
                            <div class="row" style="padding:4px;">
                                <div class="col col-lg-12">
                                    <button class="btn btn-sm btn-warning pull-right" data-toggle="modal" data-target="#excludeProjectsModal" data-action="excludeProjects">
                                        <i class="fa fa-plus-square"></i> {{trans('dashboard.browseProject')}}
                                    </button>
                                </div>
                            </div>

                            <div class="row no-space">
                                <div id="dashboard_excluded_project_list-table"></div>
                            </div>
                        </div>
                    @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('form_partials.assign_users_modal_2', array('modalId' => 'assignUsersModal','tableId' => 'assignUsersTable'))
@include('form_partials.assign_users_modal_2', array('modalId' => 'excludeProjectsModal','tableId' => 'excludeProjectsTable', 'title'=>trans('dashboard.projectsToBeExcluded'), 'actionLabel'=>trans('dashboard.exclude')))

@endsection

@section('js')
    @include('dashboard.group.partials.show.javascript')
@endsection