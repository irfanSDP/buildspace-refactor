<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\InvalidMessagingTurns;
use PCK\Exceptions\MessagingFlowHasEnded;

class AdditionalExpenseFilters {

    public function checkPreviousFirstLevelMessage(Route $route)
    {
        $user = \Confide::user();
        $repo = \App::make('PCK\AdditionalExpenseFirstLevelMessages\AdditionalExpenseFirstLevelMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('aeId'));

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
        $repo = \App::make('PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('aeId'));

        $project = $route->getParameter('projectId');

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
        $repo = \App::make('PCK\AdditionalExpenseThirdLevelMessages\AdditionalExpenseThirdLevelMessageRepository');

        $message = $repo->checkLatestMessagePosterRole($route->getParameter('aeId'));

        $project = $route->getParameter('projectId');

        // if no message then we will default to check whether current user is Architect or QS
        // or not, the first message should always be Architect or QS who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            throw new InvalidMessagingTurns('Architect or QS Consultant must initiate the Third Level Message first before Contractor can reply.');
        }

        // only one to one messaging is possible between Architect and Contractor
        if( $message and $message->type == $user->getAssignedCompany($project)->getContractGroup($project)->group )
        {
            throw new InvalidMessagingTurns('Reply must be made by other party before proceeding with Third Level Message.');
        }
    }

    public function checkPreviousFourthLevelMessage(Route $route)
    {
        $user    = \Confide::user();
        $repo    = \App::make('PCK\AdditionalExpenseFourthLevelMessages\AdditionalExpenseFourthLevelMessageRepository');
        $aeId    = $route->getParameter('aeId');
        $message = $repo->checkLatestMessagePosterRole($aeId);
        $project = $route->getParameter('projectId');

        // if no message then we will default to check whether current user is Architect
        // or not, the first message should always be architect who initiate
        if( ! $message and ! $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            throw new InvalidMessagingTurns('Architect or QS Consultant must initiate the Fourth Level Message first before Contractor can reply.');
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

        // prevent other user from submitting additional enquiries if Interim Claim has been submitted
        if( $message and $message->additionalExpense->additionalExpenseInterimClaim )
        {
            throw new MessagingFlowHasEnded('Fourth Level Message has been locked due to Interim Claim has been claimed.');
        }

        // only allow Contractor to post when there is a last post from Architect
        if( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) AND ! $repo->checkLatestMessageByArchitect($aeId) )
        {
            throw new InvalidMessagingTurns('Contractor are only allowed to post when there is reply from Architect for Fourth Level Message');
        }
    }

    public function checkPreviousContractorConfirmDelay(Route $route)
    {
        $repo = \App::make('PCK\AdditionalExpenses\AdditionalExpenseRepository');
        $ae   = $repo->find($route->getParameter('projectId'), $route->getParameter('aeId'));

        if( $ae->contractorConfirmDelay )
        {
            throw new MessagingFlowHasEnded('Contractor confirm delay is over has been posted.');
        }
    }

    public function checkPreviousClaim(Route $route)
    {
        $repo = \App::make('PCK\AdditionalExpenses\AdditionalExpenseRepository');
        $ae   = $repo->find($route->getParameter('projectId'), $route->getParameter('aeId'));

        if( ! $ae->contractorConfirmDelay )
        {
            throw new \InvalidArgumentException('Contractor must create Confirmation of Delay for Additional Expense before proceeding.');
        }

        if( $ae->additionalExpenseClaim )
        {
            throw new MessagingFlowHasEnded('AE\'s Claim has been posted.');
        }
    }

    public function checkPreviousAEInterimClaim(Route $route)
    {
        $repo = \App::make('PCK\AdditionalExpenses\AdditionalExpenseRepository');

        $aeId = $route->getParameter('aeId');
        $ae   = $repo->find($route->getParameter('projectId'), $aeId);

        // check for previous Interim Claim, if available then block all other action
        if( $ae->additionalExpenseInterimClaim )
        {
            throw new MessagingFlowHasEnded('AE\'s Claim has been posted.');
        }
    }

}