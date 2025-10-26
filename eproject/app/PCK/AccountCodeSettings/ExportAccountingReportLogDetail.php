<?php namespace PCK\AccountCodeSettings;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\AccountCodeSettings\AccountingReportExportLogItemCodes;

class ExportAccountingReportLogDetail extends Model
{
    protected $table = 'accounting_report_export_log_details';
    protected $fillable = ['accounting_report_export_log_id', 'project_code_setting_id'];

    public function accountingExportLog($claimCertificateId)
    {
        return $this->belongsTo('PCK\AccountCodeSettings\ExportAccountingReportLog');
    }

    public function projectCodeSetting()
    {
        return $this->belongsTo('PCK\Buildspace\ProjectCodeSetting');
    }

    public function accountingReportExportLogItemCodes()
    {
        return $this->hasMany('PCK\AccountCodeSettings\AccountingReportExportLogItemCodes', 'accounting_report_export_log_detail_id');
    }

    public static function addEntry($logId, $pcsId, $itemCodeIds)
    {
        $logDetail = new self(['accounting_report_export_log_id' => $logId, 'project_code_setting_id' => $pcsId]);
        $logDetail->save();

        AccountingReportExportLogItemCodes::addEntries($logDetail->id, $itemCodeIds);
    }
}

