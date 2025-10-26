<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;

class DebitCreditNoteClaimItem extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_debit_credit_note_claim_items';

    public function debitCreditNoteClaim()
    {
        return $this->belongsTo('PCK/Buildspace/DebitCreditNoteClaim', 'debit_credit_note_claim_id');
    }
}

