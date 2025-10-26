<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\Users\User;

class ApprovalDocumentVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_approval_document_verifiers';

    protected $fillable = ['consultant_management_approval_document_id', 'user_id'];

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}