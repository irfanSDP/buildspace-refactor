<?php

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\LetterOfAward\LetterOfAwardClauseCommentRepository;

class LetterOfAwardClauseCommentController extends BaseController {
    private $letterOfAwardClauseCommentRepo;

    public function __construct(LetterOfAwardClauseCommentRepository $letterOfAwardClauseCommentRepo) {
        $this->letterOfAwardClauseCommentRepo = $letterOfAwardClauseCommentRepo;
    }

    public function getClauseComments(Project $project) {
        $inputs = Input::all();
        $letterOfAwardClauseId = $inputs['letterOfAwardClauseId'];
        $clauseComments = $this->letterOfAwardClauseCommentRepo->getClauseComments($letterOfAwardClauseId);

        $this->letterOfAwardClauseCommentRepo->setCommentReadFlag($letterOfAwardClauseId);
        
        return $clauseComments;
    }

    public function saveClauseComment(Project $project) {
        $inputs = Input::all();
        $success = $this->letterOfAwardClauseCommentRepo->saveClauseComment($inputs);

        return Response::json([
            'success' => $success
        ]);
    }
}

