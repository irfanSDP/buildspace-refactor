<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\ContractorQuestionnaire\Question;
use PCK\Exceptions\InvalidAccessLevelException;
use PCK\Projects\Project;

class TenderQuestionnaireFilters
{
    public function canCreateQuestionnaire(Route $route)
    {
        $project = $route->getParameter('projectId');

        if($project->status_id == Project::STATUS_TYPE_POST_CONTRACT)
        {
            throw new InvalidAccessLevelException(trans('general.invalidOperation'));
        }
    }

    public function canEditQuestion(Route $route)
    {
        $question = Question::find($route->getParameter('questionId'));
        
        if(!$question->deletable())
        {
            throw new InvalidAccessLevelException(trans('general.invalidOperation'));
        }
    }
}