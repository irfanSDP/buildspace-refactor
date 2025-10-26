<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\LetterOfAward;

class LetterOfAwardAttachment extends Model
{
    protected $table = 'consultant_management_letter_of_award_attachments';

    public function letterOfAward()
    {
        return $this->belongsTo(LetterOfAward::class, 'consultant_management_letter_of_award_id');
    }
}