<?php
    $showSkipToPostContract = $user->isEditor($project) && $user->hasCompanyProjectRole($project, array( \PCK\ContractGroups\Types\Role::PROJECT_OWNER, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT)) && $project->canManuallySkipToPostContract();
    $showContractorAccessToggle = $project->isCurrentTenderStatusClosed() && $user->isEditor($project) && $user->hasCompanyProjectRole($project, array( \PCK\ContractGroups\Types\Role::PROJECT_OWNER, \PCK\ContractGroups\Types\Role::GROUP_CONTRACT));
    $showProjectCompletionOption = $project->isPostContract() && $user->isEditor($project) && $user->hasCompanyProjectRole($project, PCK\Filters\TenderFilters::getListOfTendererFormRole($project));

    $showActionButton = ( $showSkipToPostContract || $showContractorAccessToggle || $showProjectCompletionOption );
?>

@if($showActionButton)
    <div class="dropdown {{{ $classes ?? '' }}}">
        <a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
        <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
            <?php $hasPreviousItems = false;?>
            <!-- @if($showSkipToPostContract)
                @if($hasPreviousItems)
                    <li class="divider"></li>
                @endif
                <?php $hasPreviousItems = true;?>
                <li>
                    <a href="{{ route('projects.skip.postContract.confirmation', array($project->id)) }}" class="btn btn-block btn-warning btn-md">
                        <i class="fa fa-fast-forward"></i>
                        {{ trans('projects.skipToPostContract') }}
                    </a>
                </li>
            @endif -->
            @if($showContractorAccessToggle)
                @if($hasPreviousItems)
                    <li class="divider"></li>
                @endif
                <?php $hasPreviousItems = true;?>
                <li>
                    {{ Form::open(array('route' => array('projects.contractorAccess.toggle', $project->id), 'id' => 'contractorAccessForm')) }}
                    {{ Form::close() }}
                    <a href="javascript:void(0);" onclick="document.getElementById('contractorAccessForm').submit()" class="btn btn-block btn-info btn-md">
                        @if($project->contractor_access_enabled)
                            <i class="fa fa-check"></i>
                        @else
                            <i class="fa fa-square-o"></i>
                        @endif
                        {{ trans('projects.contractorAccess') }}
                    </a>
                </li>
                @if($project->contractor_access_enabled)
                    <li>
                        {{ Form::open(array('route' => array('projects.contractorAccess.contractualClaim.toggle', $project->id), 'id' => 'contractorClaimAccessForm')) }}
                        {{ Form::close() }}
                        <a href="javascript:void(0);" onclick="document.getElementById('contractorClaimAccessForm').submit()" class="btn btn-block btn-info btn-md">
                            @if($project->contractor_contractual_claim_access_enabled)
                                <i class="fa fa-check"></i>
                            @else
                                <i class="fa fa-square-o"></i>
                            @endif
                            {{ trans('projects.contractorClaimAccess') }}
                        </a>
                    </li>
                @endif
            @endif
            @if ($showProjectCompletionOption)
                @if($hasPreviousItems)
                    <li class="divider"></li>
                @endif
                <?php $hasPreviousItems = true;?>
                <li>
                    <a href="{{route('projects.completion.create', array($project->id))}}" class="btn btn-block btn-md btn-success {{{$project->isCompleted() ? 'disabled' : ''}}}">
                        <i class="fa fa-flag-checkered"></i>
                        {{ trans('projects.completion') }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
@endif