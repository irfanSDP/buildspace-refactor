<?php

use PCK\Tenders\Tender;

class RecommendationOfTenderersController extends \BaseController {

	public function initiateThread(\PCK\Projects\Project $project, $tenderId)
	{
	    $user = Confide::user();

	    $tender = Tender::find($tenderId);

	    $object = $tender->recommendationOfTendererInformation;

	    if( ! $object ) return App::abort(404);

	    $isCurrentVerifier = $object->isBeingValidated() && in_array($user->id, $object->latestVerifier->lists('id'));

	    if( ! \PCK\Forum\ObjectThread::objectHasThread($object) && $isCurrentVerifier)
	    {
	        $thread = \PCK\Forum\Thread::init($project, $user, $object, trans('projects.recommendationOfTenderer'));

	        $object->syncApprovalForumUsers();

	        return Redirect::route('forum.threads.show', array($project->id, $thread->id));
	    }

	    return App::abort(404);
	}

}