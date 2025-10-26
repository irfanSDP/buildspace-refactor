<?php namespace PCK\AssignCompaniesLogs;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use PCK\Helpers\ModelOperations;

class AssignCompaniesLog extends Model {

    use PresentableTrait;

    protected $table = 'assign_companies_logs';

    protected $with = array( 'user', 'tenderDocumentPermissionLog', 'inDetailLogs' );

    protected $presenter = 'PCK\AssignCompaniesLogs\AssignCompaniesLogPresenter';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $assignCompaniesLog)
        {
            $assignCompaniesLog->deleteRelatedModels();
        });
    }

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function tenderDocumentPermissionLog()
    {
        return $this->hasOne('PCK\ContractGroupTenderDocumentPermissionLogs\ContractGroupTenderDocumentPermissionLog', 'assign_company_log_id');
    }

    public function inDetailLogs()
    {
        return $this->hasMany('PCK\AssignCompanyInDetailLogs\AssignCompanyInDetailLog', 'assign_company_log_id');
    }

    /**
     * Delete related records.
     */
    protected function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->inDetailLogs,
            $this->tenderDocumentPermissionLog,
        ));
    }

}