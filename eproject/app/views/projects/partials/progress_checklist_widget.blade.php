@if($user->hasCompanyProjectRole($project, \PCK\Tenders\Tender::rolesAllowedToUseModule($project)) && (!$project->isPostContract()))
    <?php
        $checklistItems = array();
        if($project->tenders->first()->isFirstTender())
        {
            $checklistItems = $project->getProgressChecklist($project->tenders->first());
        }
        elseif( $user->hasCompanyProjectRole($project, $project->getTenderAddendumRoles()) )
        {
            $checklistItems = $project->getAddendumChecklist($project->tenders->first());
        }
    ?>

    @if($checklistItems)
        <?php
                $metCriteria = 0;
                $totalCriteria = count($checklistItems);

                foreach($checklistItems as $item)
                {
                    if($item['checked']) $metCriteria++;
                }
        ?>
        <div class="jarviswidget">
            <header>
                <h2 class="font-md">{{ trans('projects.projectProgressChecklist') }}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <?php
                    $barPercentage = ($totalCriteria == 0) ? 100 : ($metCriteria / $totalCriteria * 100);
                    $fullBar = ( $barPercentage >= 100 );
                    ?>
                    <div class="row">
                        <div class="col col-xs-11">
                            <div class="text-center {{{ $fullBar ? 'text-success' : 'text-warning' }}}">
                                <strong>
                                    {{{ $metCriteria }}} / {{{ $totalCriteria }}}
                                </strong>
                            </div>
                            <div class="progress progress-micro progress-striped active">
                                <div class="progress-bar {{{ $fullBar ? 'bg-color-green' : 'bg-color-orange' }}}" role="progressbar" style="width: {{{ $barPercentage }}}%"></div>
                            </div>
                        </div>
                        <div class="col col-xs-1">
                            <button type="button" class="btn btn-warning btn-xs pull-right" data-action="expandToggle" data-target="checkList">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div id="checkList" data-id="checkList" data-type="expandable" style="margin-top:12px;">
                        <ul class="list-group">
                            @foreach($checklistItems as $item)
                                <?php $isRelevantParty = $currentUser->hasCompanyProjectRole($project, array_keys($item['parties'])); ?>
                                <li class="list-group-item {{{ $item['checked'] ? 'list-group-item-success' : 'list-group-item-warning' }}}">
                                    <i class="fa {{{ $item['checked'] ? 'fa-check' : 'fa-times-circle' }}}"></i>
                                    @if($isRelevantParty && $item['link'])
                                        {{ HTML::link($item['link'], $item['description'], array('style' => 'color:inherit')) }}
                                    @else
                                        {{{ $item['description'] }}}
                                    @endif

                                    <br/>

                                    <span class="text-danger">
                                        @if(!empty($item['parties']))
                                            ({{{ implode(', ', $item['parties']) }}})
                                        @endif
                                    </span>

                                    <br/>
                                    @if($isRelevantParty && $item['link'])
                                        {{ HTML::link($item['link'], trans('general.view'), array('style' => 'color:inherit')) }} |
                                    @endif
                                    <a href="{{{ $item['reference'] }}}" style="color: inherit" target="_blank">
                                        See {{ trans('general.tutorial') }}
                                    </a>

                                    &nbsp;&nbsp;
                                    @if($item['skippable'])
                                    <input type="checkbox" name="skip-steps[]" data-id="{{{$item['id']}}}" @if($item['checked']) checked="true" @endif>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="text-right">
                            <?php
                            $link = '#';
                            if($user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER)) $link = trans('links.businessUnitTutorials');
                            if($user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT)) $link = trans('links.groupContractDivisionTutorials');
                            if($user->hasCompanyProjectRole($project, array(
                                    \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER,
                                    \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_1,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_2,
                            ))) $link = trans('links.consultantTutorials');
                            ?>
                            <a href="{{{ $link }}}" target="_blank">
                                More Tutorials
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif