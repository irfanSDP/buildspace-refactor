<?php namespace PCK\AccountCodeSettings;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class AccountingReportExportLogItemCodes extends Model 
{
    protected $table    = 'accounting_report_export_log_item_codes';
    protected $fillable = [ 'accounting_report_export_log_detail_id', 'item_code_setting_id' ];

    public function exportAccountingReportLogDetail()
    {
        return $this->belongsTo('PCK\AccountCodeSettings\ExportAccountingReportLogDetail');
    }

    public function itemCodeSetting()
    {
        return $this->belongsTo('PCK\Buildspace\ItemCodeSetting');
    }

    public static function addEntries($logDetailId, $itemCodeIds)
    {
        foreach($itemCodeIds as $itemCodeId)
        {
            $logDetail = new self(['accounting_report_export_log_detail_id' => $logDetailId, 'item_code_setting_id' => $itemCodeId]);
            $logDetail->save();
        }

    }
}

