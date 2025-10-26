<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;

class LetterOfAwardClauseComment extends Model {

    protected $table = 'letter_of_award_clause_comments';

    public function readLogs()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardClauseCommentReadLog', 'clause_comment_id');
    }
}

