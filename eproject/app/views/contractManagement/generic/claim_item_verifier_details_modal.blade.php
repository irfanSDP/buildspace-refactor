<?php $modalId = isset($modalId) ? $modalId : 'verifierStatusOverViewModal' ?>

<div class="modal full-screen" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header {{{ $headerClass }}} txt-color-white">
                <h4 class="modal-title">
                    {{{ $moduleName }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="well">{{{ $object->displayDescription }}}</div>
                <form class="smart-form" style="overflow-y: auto; height: 420px;">
                    @include('verifiers.verifier_status_overview', array('verifierRecords' => $verifierRecords[$objectId], 'additionalFields' => array(trans('verifiers.daysPending') => 'daysPending')))
                </form>
                @if(\PCK\Buildspace\ContractManagementClaimVerifier::isCurrentVerifier($project, $currentUser, $moduleIdentifier, $objectId))
                    <br/>
                    <div class="well text-right">
                        <?php $buildspaceLink = getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_APPROVAL . "&id={$project->id}&module_identifier={$moduleIdentifier}&object_id={$objectId}" ;?>
                        <a href="{{{ $buildspaceLink }}}" class="btn btn-default"><i class="fa fa-search"></i>&nbsp;{{ trans('verifiers.inspect') }}</a>
                    </div>
                @elseif($filterClass::canSubstitute($project, $currentUser, $objectId))
                    <br/>
                    <div class="well text-right">
                        <?php $currentVerifier = \PCK\Buildspace\ContractManagementClaimVerifier::getCurrentVerifier($project, $moduleIdentifier, $objectId) ?>
                        <a data-intercept="confirmation" class="btn btn-danger" href="{{ route($substituteAndRejectRoute, array($project->id, $currentVerifier->id, $objectId)) }}">
                            <i class="fa fa-thumbs-down"></i>
                            {{ trans('contractManagement.rejectAsSubstitute') }}
                        </a>
                        <a data-intercept="confirmation" class="btn btn-success" href="{{ route($substituteAndApproveRoute, array($project->id, $currentVerifier->id, $objectId)) }}">
                            <i class="fa fa-thumbs-up"></i>
                            {{ trans('contractManagement.approveAsSubstitute') }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>