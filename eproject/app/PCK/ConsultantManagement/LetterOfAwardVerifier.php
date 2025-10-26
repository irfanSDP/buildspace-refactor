<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\ConsultantManagement\LetterOfAward;
use PCK\Users\User;

class LetterOfAwardVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_letter_of_award_verifiers';

    protected $fillable = ['consultant_management_letter_of_award_id', 'user_id'];

    public function approvalDocument()
    {
        return $this->belongsTo(LetterOfAward::class, 'consultant_management_letter_of_award_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}