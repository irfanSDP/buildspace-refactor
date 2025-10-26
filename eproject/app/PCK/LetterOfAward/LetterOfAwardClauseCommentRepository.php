<?php namespace PCK\LetterOfAward;

use Carbon\Carbon;
use PCK\Users\User;
use PCK\Helpers\DataTables;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use PCK\LetterOfAward\LetterOfAwardClauseComment;
use PCK\LetterOfAward\LetterOfAwardClauseCommentReadLog;

class LetterOfAwardClauseCommentRepository {

    public function readLogs()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardClauseCommentReadLog');
    }

    public function getClauseComments($letterOfAwardClauseId) {
        $inputs = \Input::all();
        $letterOfAwardClauseId = $inputs['letterOfAwardClauseId'];

        $idColumn      = "letter_of_award_clause_comments.id";
        $orderByColumn = "letter_of_award_clause_comments.updated_at";
        $selectColumns = [$idColumn, $orderByColumn];

        $fileNameColumn = array(
            'fileName' => 0,
        );

        $allColumns = array(
            'letter_of_award_clause_comments' => $fileNameColumn
        );
        
        $query = LetterOfAwardClauseComment::where('clause_id', $letterOfAwardClauseId);

        $dataTable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);
        
        $dataTable->properties->query->orderBy('letter_of_award_clause_comments.updated_at', 'desc');
        $dataTable->addAllStatements();
        $results = $dataTable->getResults();
        $dataArray = [];

        foreach ( $results as $index => $object ) {
            $indexNo = ( $index + 1 ) + ( $dataTable->properties->pagingOffset );
            $record = LetterOfAwardClauseComment::find($object->id);

            array_push($dataArray, [
                'comments'      => $record->comments,
                'commentor'     => User::find($record->user_id)->name,
                'date'          => Carbon::parse($record->updated_at)->format(\Config::get('dates.full_format')),
            ]);
        }

        // $this->setCommentReadFlag($letterOfAwardClauseId);

        return $dataTable->dataTableResponse($dataArray);
    }

    public function saveClauseComment($inputs) {
        $letterOfAwardClauseId = $inputs['letterOfAwardClauseId'];
        $comments = $inputs['comments'];

        $letterOfAwardClauseComment = new LetterOfAwardClauseComment();
        $letterOfAwardClauseComment->clause_id = $letterOfAwardClauseId;
        $letterOfAwardClauseComment->comments = $comments;
        $letterOfAwardClauseComment->user_id = \Confide::user()->id;

        return $letterOfAwardClauseComment->save();
    }

    public function getUnreadCommentsCountGroupedByClause(LetterOfAward $letterOfAward, $user = null) {

        if($user == null) $user = \Confide::user();

        $groupedUnreadComments = [];
        $clauses = $letterOfAward->clauses;

        foreach($clauses as $clause) {
            $comments = $clause->comments;

            if($comments->isEmpty()) {
                $groupedUnreadComments[$clause->id] = 0;
            } else {
                $unreadCount = 0;

                foreach($comments as $comment) {
                    $readLogs = $comment->readLogs->filter(function($obj) use ($user) {
                        return $obj->user_id == $user->id;
                    });
                    
                    if($readLogs->isEmpty()) {
                        ++$unreadCount;
                    }
                }

                $groupedUnreadComments[$clause->id] = $unreadCount;
            }         
        }

        return $groupedUnreadComments;
    }

    public function setCommentReadFlag($letterOfAwardClauseId) {
        $comments = LetterOfAwardClauseComment::where('clause_id', $letterOfAwardClauseId)->get();
        
        foreach($comments as $comment) {
            $log = LetterOfAwardClauseCommentReadLog::getReadLogsByUserAndClauseCommentId(\Confide::user()->id, $comment->id);
            
            if($log->isEmpty()) {
                $logRecord = new LetterOfAwardClauseCommentReadLog();
                $logRecord->user_id = \Confide::user()->id;
                $logRecord->clause_comment_id = $comment->id;
                $logRecord->save();
            }
        }
    }
}

