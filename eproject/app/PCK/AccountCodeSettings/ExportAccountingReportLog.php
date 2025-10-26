<?php namespace PCK\AccountCodeSettings;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\AccountCodeSettings\ExportAccountingReportLogDetail;

class ExportAccountingReportLog extends Model
{
    protected $table    = 'accounting_report_export_logs';
    protected $fillable = [ 'claim_certificate_id', 'user_id' ];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function logDetails()
    {
        return $this->hasMany('PCK\AccountCodeSettings\ExportAccountingReportLogDetail', 'accounting_report_export_log_id');
    }

    public static function addEntry($claimCertificateId, User $user, $paramStrings)
    {
        $log = new self(array( 'claim_certificate_id' => $claimCertificateId, 'user_id' => $user->id ));
        $log->save();

        foreach($paramStrings as $paramString)
        {
            $pos         = strpos($paramString, '[');
            $pcsId       = substr($paramString, 0, $pos);
            $itemCodeIds = explode('|', str_replace(['[', ']'], '', substr($paramString, $pos)));

            ExportAccountingReportLogDetail::addEntry($log->id, $pcsId, $itemCodeIds);
        }
    }

    public static function getLog($claimCertificateId)
    {
        return self::where('claim_certificate_id', '=', $claimCertificateId)->orderBy('created_at', 'desc')->get();
    }
}

