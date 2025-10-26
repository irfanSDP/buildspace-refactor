<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\Project as ProjectStructure;

class DebitCreditNoteClaim extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_debit_credit_note_claims';

    public function debitCreditNoteClaimItems()
    {
        return $this->hasMany('PCK\Buildspace\DebitCreditNoteClaimItem', 'debit_credit_note_claim_id');
    }

    public function accountGroup()
    {
        return $this->belongsTo('PCK\Buildspace\AccountGroup', 'account_group_id');
    }

    public static function getCreditDebitNoteTotalByType($claimCertificateId, $type)
    {
        $query = "select dcnc.claim_certificate_id, ac.type, ROUND(COALESCE(SUM(dcnci.rate * dcnci.quantity), 0), 2) as total
            from bs_debit_credit_note_claims dcnc
            inner join bs_debit_credit_note_claim_items dcnci on dcnci.debit_credit_note_claim_id = dcnc.id
            inner join bs_account_codes ac on ac.id = dcnci.account_code_id
            where claim_certificate_id = {$claimCertificateId}
            and ac.type = {$type}
            group by dcnc.claim_certificate_id, ac.type";
        
        $pdo = \DB::connection('buildspace')->getPdo();

        $stmt = $pdo->prepare($query);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }
}

