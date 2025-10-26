<?php
$maxTodoRecords = 5;
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget" id="dashboard-contract_management_review_list" data-widget-editbutton="true" data-widget-colorbutton="false" data-widget-deletebutton="true" data-widget-sortable="false" data-widget-collapsed="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-fw fa-list"></i></span>
                <h2>{{ trans('contractManagement.reviewList') }}
                @if(($pendingUserReviews->count() + count($pendingTenderProcesses) + $pendingSiteModuleProcesses->count()) > 0)
                    <span class="badge bg-color-red">{{{ $pendingUserReviews->count() + count($pendingTenderProcesses) + $pendingSiteModuleProcesses->count() }}}</span>
                @endif
                </h2>
            </header>
            <!-- widget div-->
            <div class="widget-body">
                <!-- tabs -->
                <ul id="toDoListTab" class="nav nav-tabs bordered">
                    @if (count($pendingTenderProcesses) > 0)
                        <li class="active">
                            <a href="#toDoListTabContent1" data-toggle="tab"><i class="fa fa-fw fa-lg fa-inbox"></i>{{ trans('contractManagement.tendering') }}<span class="badge bg-color-red">{{{ count($pendingTenderProcesses) }}}</span></a>
                        </li>
                    @endif
                    
                    @if ($pendingUserReviews->count() > 0)
                        <li @if(!count($pendingTenderProcesses)) class="active" @endif>
                            <a href="#toDoListTabContent2" data-toggle="tab"><i class="far fa-fw fa-lg fa-file"></i>{{ trans('contractManagement.postContract') }}<span class="badge bg-color-red">{{{ $pendingUserReviews->count() }}}</span></a>
                        </li>
                    @endif

                    @if ($pendingSiteModuleProcesses->count() > 0)
                        <li @if((count($pendingTenderProcesses) == 0) && ($pendingUserReviews->count() == 0)) class="active" @endif>
                            <a href="#toDoListTabContent3" data-toggle="tab"><i class="far fa-fw fa-lg fa-file"></i>{{ trans('contractManagement.siteModule') }}<span class="badge bg-color-red">{{{ $pendingSiteModuleProcesses->count() }}}</span></a>
                        </li>
                    @endif
                </ul>
                <div id="toDoListTabContentPane" class="tab-content padding-10" style="height: 100%;">
                    <div class="tab-pane fade in @if (count($pendingTenderProcesses) !== 0) active @endif" id="toDoListTabContent1">
                        <div class="widget-body">
                            @if (count($pendingTenderProcesses) > 0)
                                @if (count($pendingTenderProcesses) > $maxTodoRecords)
                                    <button id="showMoreTendering" type="button" class="btn btn-xs btn-info" data-action="toggle-review-list-show-all">{{ trans('general.showMore') }}</button>
                                    <br /><br />
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-hover table-condensed" id="pending-tenders-list" data-state="view-less">
                                        <thead>
                                            <tr>
                                                <th class="text-middle text-center text-nowrap" style="width:20px;">{{ trans('general.no') }}</th>
                                                <th class="text-middle text-left text-nowrap" style="width:auto;min-width:200px;">{{ trans('projects.project') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:100px;">{{ trans('verifiers.module') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:80px;">{{ trans('verifiers.daysPending') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:60px;">{{ trans('general.view') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $tCount = 1; ?>
                                        @foreach ($pendingTenderProcesses as $item)
                                            <tr data-id="{{{ $tCount }}}" {{{ $tCount > $maxTodoRecords ? 'hidden' : '' }}}>
                                                <td class="text-middle text-center text-nowrap" style="width:20px;">{{{ $tCount }}}</td>
                                                <td class="text-middle text-left" style="width:auto;min-width:200px;">
                                                    <div class="well">
                                                    {{{ $item['project_title'] }}}
                                                    </div>
                                                </td>
                                                <td class="text-middle text-center text-nowrap" style="width:100px;">{{{ $item['module'] }}}</td>
                                                <td class="text-middle text-center {{{ count($pendingTenderProcesses) > 0 ? 'text-danger' : '' }}}" style="width:80px;">
                                                    {{{ $item['days_pending'] }}}
                                                </td>
                                                <td class="text-middle text-center text-nowrap" style="width:60px;">
                                                    <a href="{{{ $item['route'] }}}" class="btn btn-warning btn-xs">{{ trans('general.view') }}</a>
                                                </td>
                                            </tr>
                                        <?php $tCount++?>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="tab-pane fade in @if (empty(count($pendingTenderProcesses)) && ($pendingUserReviews->count() > 0)) active @endif" id="toDoListTabContent2">
                        <!-- widget content -->
                        <div class="widget-body">
                            @if($pendingUserReviews->count() > 0)
                                @if($pendingUserReviews->count() > $maxTodoRecords)
                                    <button id="showMoreClaims" type="button" class="btn btn-xs btn-info" data-action="toggle-review-list-show-all">{{ trans('general.showMore') }}</button>
                                    <br /><br />
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-hover table-condensed" id="review-list" data-state="view-less">
                                        <thead>
                                            <tr>
                                                <th class="text-middle text-center text-nowrap" style="width:20px;">{{ trans('general.no') }}</th>
                                                @if(!isset($project))
                                                <th class="text-middle text-left text-nowrap" style="width:auto;min-width:200px;">{{ trans('projects.project') }}</th>
                                                @endif
                                                <th class="text-middle text-left" style="@if(!isset($project)) width:200px; @else width:auto;min-width:200px; @endif">{{ trans('verifiers.description') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:160px;">{{ trans('verifiers.module') }}</th>
                                                <th class="text-middle text-left text-nowrap" style="width:80px;">{{ trans('verifiers.daysPending') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:60px;">{{ trans('general.view') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 1; ?>
                                        @foreach($pendingUserReviews as $record)
                                            <tr data-id="{{{ $count }}}" {{{ $count > $maxTodoRecords ? 'hidden' : '' }}}>
                                                <td class="text-middle text-center text-nowrap" style="width:20px;">{{{ $count }}}</td>

                                                @if(!isset($project))
                                                    <td class="text-middle text-left text-nowrap squeeze" style="width:auto;min-width:200px;">
                                                        <div class="well">
                                                            <a href="{{ route('projects.show', array($record->getProject()->id)) }}" class="plain" title="{{{ $record->getProject()->title }}}">
                                                                {{{ $record->getProject()->title }}}
                                                            </a>
                                                            @if($record->getProject()->parentProject)
                                                            <div class="well">
                                                            <a href="{{ route('projects.show', array($record->getProject()->parentProject->id)) }}" class="plain" title="{{{ $record->getProject()->parentProject->title }}}">
                                                                {{{ $record->getProject()->parentProject->title }}}
                                                            </a>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif

                                                <td class="text-middle text-left" style="@if(!isset($project)) width:200px; @else width:auto;min-width:200px; @endif">{{{ $record->getObjectDescription() }}}</td>
                                                <td class="text-middle text-center text-nowrap" style="width:160px;">{{{ $record->getModuleName() }}}</td>
                                                <td class="text-middle text-center {{{ $record->daysPending > 0 ? 'text-danger' : '' }}}" style="width:80px;">{{{ $record->daysPending }}}</td>
                                                <td class="text-middle text-center text-nowrap" style="width:60px;">
                                                    <a href="{{{ $record->getRoute() }}}" class="btn btn-warning btn-xs">{{ trans('general.view') }}</a>
                                                </td>
                                            </tr>
                                            <?php $count++;?>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="well">
                                    {{ trans('verifiers.nothingToVerify') }}...
                                </div>
                            @endif
                        </div>
                        <!-- end widget content -->
                    </div>

                    <div class="tab-pane fade in @if ((count($pendingTenderProcesses) == 0) && ($pendingUserReviews->count() == 0)) active @endif" id="toDoListTabContent3">
                        <!-- widget content -->
                        <div class="widget-body">
                            @if($pendingSiteModuleProcesses->count() > 0)
                                @if($pendingSiteModuleProcesses->count() > $maxTodoRecords)
                                    <button id="showMoreClaims" type="button" class="btn btn-xs btn-info" data-action="toggle-review-list-show-all">{{ trans('general.showMore') }}</button>
                                    <br /><br />
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-hover table-condensed" id="review-list" data-state="view-less">
                                        <thead>
                                            <tr>
                                                <th class="text-middle text-center text-nowrap" style="width:20px;">{{ trans('general.no') }}</th>
                                                @if(!isset($project))
                                                <th class="text-middle text-left text-nowrap" style="width:auto;min-width:200px;">{{ trans('projects.project') }}</th>
                                                @endif
                                                <th class="text-middle text-left" style="@if(!isset($project)) width:200px; @else width:auto;min-width:200px; @endif">{{ trans('verifiers.description') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:160px;">{{ trans('verifiers.module') }}</th>
                                                <th class="text-middle text-left text-nowrap" style="width:80px;">{{ trans('verifiers.daysPending') }}</th>
                                                <th class="text-middle text-center text-nowrap" style="width:60px;">{{ trans('general.view') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php $count = 1; ?>
                                        @foreach($pendingSiteModuleProcesses as $record)
                                            <tr data-id="{{{ $count }}}" {{{ $count > $maxTodoRecords ? 'hidden' : '' }}}>
                                                <td class="text-middle text-center text-nowrap" style="width:20px;">{{{ $count }}}</td>

                                                @if(!isset($project))
                                                    <td class="text-middle text-left text-nowrap squeeze" style="width:auto;min-width:200px;">
                                                        <div class="well">
                                                            <a href="{{ route('projects.show', array($record->getProject()->id)) }}" class="plain" title="{{{ $record->getProject()->title }}}">
                                                                {{{ $record->getProject()->title }}}
                                                            </a>
                                                            @if($record->getProject()->parentProject)
                                                            <div class="well">
                                                            <a href="{{ route('projects.show', array($record->getProject()->parentProject->id)) }}" class="plain" title="{{{ $record->getProject()->parentProject->title }}}">
                                                                {{{ $record->getProject()->parentProject->title }}}
                                                            </a>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif

                                                <td class="text-middle text-left" style="@if(!isset($project)) width:200px; @else width:auto;min-width:200px; @endif">{{{ $record->getObjectDescription() }}}</td>
                                                <td class="text-middle text-center text-nowrap" style="width:160px;">{{{ $record->getModuleName() }}}</td>
                                                <td class="text-middle text-center {{{ $record->daysPending > 0 ? 'text-danger' : '' }}}" style="width:80px;">{{{ $record->daysPending }}}</td>
                                                <td class="text-middle text-center text-nowrap" style="width:60px;">
                                                    <a href="{{{ $record->getRoute() }}}" class="btn btn-warning btn-xs">{{ trans('general.view') }}</a>
                                                </td>
                                            </tr>
                                            <?php $count++;?>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="well">
                                    {{ trans('verifiers.nothingToVerify') }}
                                </div>
                            @endif
                        </div>
                        <!-- end widget content -->
                    </div>
                </div>
                <!-- end tabs -->
                <br />
            </div>
            <!-- end widget div -->

        </div>
    </div>
</div>
<script>
    $('[data-action=toggle-review-list-show-all]').on('click', function(e){
        var buttonID = $(this).prop('id');
        var queryString;

        switch(buttonID) {
            case 'showMoreTendering':
                queryString = 'table#pending-tenders-list';
                break;
            case 'showMoreClaims':
                queryString = 'table#review-list';
                break;
            default:
                console.log("Error : Unknown Element ID : " + buttonID);
        }

        var toggleableList = $(queryString + ' tr[data-id]').filter(function(){
            return $(this).data('id') > {{{$maxTodoRecords}}};
        });

        var buttonText;
        var state = $(queryString).data('state');

        if(state == 'view-less') {
            toggleableList.show();
            $(queryString).data('state', 'view-more');
            buttonText = '{{ trans('general.showLess') }}';
        } else {
            toggleableList.hide();
            $(queryString).data('state', 'view-less');
            buttonText = '{{ trans('general.showMore') }}';
        }

        $(this).html(buttonText);
    });
</script>
