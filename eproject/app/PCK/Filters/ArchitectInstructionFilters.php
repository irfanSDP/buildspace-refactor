<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidMessagingTurns;
use PCK\Exceptions\MessagingFlowHasEnded;

class ArchitectInstructionFilters {

    public function checkPreviousAIFirstLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ArchitectInstructionMessages\ArchitectInstructionMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('aiId'));

        $project = $route->getParameter('projectId');

        // if no message then we will default to check whether current user is contractor
        // or not, the first message should always be contractor who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            throw new InvalidMessagingTurns('Contractor must initiate the First Level Message first before Architect can reply.');
        }

        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with First Level Message.');
        }
    }

    public function checkPreviousAIThirdLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('aiId'));

        $project = $route->getParameter('projectId');

        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with Third Level Message');
        }

        // prevent other user from submitting additional enquiries if Interim Claim has been submitted
        if( $message and $message->architectInstruction->architectInstructionInterimClaim )
        {
            throw new MessagingFlowHasEnded('Interim Claim has been submitted !');
        }
    }

    public function checkPreviousAIInterimClaim(Route $route)
    {
        $repo = \App::make('PCK\ArchitectInstructions\ArchitectInstructionRepository');

        $aiId = $route->getParameter('aiId');
        $ai   = $repo->find($route->getParameter('projectId'), $aiId);

        // check for previous Interim Claim, if available then block all other action
        if( $ai->architectInstructionInterimClaim )
        {
            throw new MessagingFlowHasEnded('Interim Claim has been submitted !');
        }
    }

}