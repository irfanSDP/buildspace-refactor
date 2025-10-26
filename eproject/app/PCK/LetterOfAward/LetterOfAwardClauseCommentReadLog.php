<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;

class LetterOfAwardClauseCommentReadLog extends Model {

    protected $table = 'letter_of_award_clause_comment_read_logs';

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public static function getReadLogsByUserAndClauseCommentId($userId, $clauseCommentId) {
        return self::where('user_id', $userId)
                ->where('clause_comment_id', $clauseCommentId)
                ->get();
    }
}

