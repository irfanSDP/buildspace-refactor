<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidMessagingTurns;
use PCK\Exceptions\MessagingFlowHasEnded;

class ExtensionOfTimeFilters {

    public function checkPreviousFirstLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ExtensionOfTimeFirstLevelMessages\ExtensionOfTimeFirstLevelMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('eotId'));

        $project = $route->getParameter('projectId');

        // if no message then we will default to check whether current user is architect
        // or not, the first message should always be architect who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            throw new InvalidMessagingTurns('Architect must initiate the First Level Message first before Contractor can reply.');
        }

        // all messaging flow has to be stopped once conclusion has been made by Architect
        if( $message and $message->decision )
        {
            throw new MessagingFlowHasEnded('First Level Message conclusion has been made by Architect.');
        }

        // only one to one messaging is possible between Contractor and Architect
        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with First Level Message.');
        }
    }

    public function checkPreviousSecondLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessageRepository');

        $project = $route->getParameter('projectId');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('eotId'));

        // if no message then we will default to check whether current user is contractor
        // or not, the first message should always be architect who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
        {
            throw new InvalidMessagingTurns('Contractor must initiate the Second Level Message first before Architect can reply.');
        }

        // only one to one messaging is possible between Contractor and Architect
        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with Second Level Message.');
        }
    }

    public function checkPreviousThirdLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ExtensionOfTimeThirdLevelMessages\ExtensionOfTimeThirdLevelMessageRepository');

        $project = $route->getParameter('projectId');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('eotId'));

        // if no message then we will default to check whether current user is Architect
        // or not, the first message should always be architect who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            throw new InvalidMessagingTurns('Architect must initiate the Third Level Message first before Contractor can reply.');
        }

        // only one to one messaging is possible between Contractor and Architect
        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with Third Level Message.');
        }
    }

    public function checkPreviousFourthLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessageRepository');

        $project = $route->getParameter('projectId');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('eotId'));

        // if no message then we will default to check whether current user is Architect
        // or not, the first message should always be architect who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
        {
            throw new InvalidMessagingTurns('Architect must initiate the Fourth Level Message first before Contractor can reply.');
        }

        // only one to one messaging is possible between Contractor and Architect
        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with Fourth Level Message.');
        }

        // if current last message has been set to locked, no one can reply anymore
        if( $message and $message->locked )
        {
            throw new MessagingFlowHasEnded('Fourth Level Message has been locked.');
        }
    }

    public function checkPreviousContractorConfirmDelay(Route $route)
    {
        $repo = \App::make('\PCK\ExtensionOfTimes\ExtensionOfTimeRepository');
        $eot  = $repo->find($route->getParameter('projectId'), $route->getParameter('eotId'));

        if( $eot->eotContractorConfirmDelay )
        {
            throw new MessagingFlowHasEnded('Contractor confirm delay is over has been posted.');
        }
    }

    public function checkPreviousEOTClaim(Route $route)
    {
        $repo = \App::make('\PCK\ExtensionOfTimes\ExtensionOfTimeRepository');
        $eot  = $repo->find($route->getParameter('projectId'), $route->getParameter('eotId'));

        if( ! $eot->eotContractorConfirmDelay )
        {
            throw new \InvalidArgumentException('Contractor must create Confirmation of Delay for EOT before proceeding.');
        }

        if( $eot->extensionOfTimeClaim )
        {
            throw new MessagingFlowHasEnded('EOT\'s Claim has been posted.');
        }
    }

}