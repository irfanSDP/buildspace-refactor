<?php namespace PCK\RequestForVariation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\RequestForVariation\RequestForVariationCategory;

class RequestForVariationCategoryKpiLimitUpdateLog extends Model
{
    protected $table = 'request_for_variation_category_kpi_limit_update_logs';

    public function requestForVariationCategory()
    {
        return $this->belongsTo('PCK\RequestForVariation\RequestForVariationCategory', 'request_for_variation_category_id');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public static function createEntry(RequestForVariationCategory $requestForVariationCategory, $kpiLimit, $remarks)
    {
        $record = new self;
        $record->request_for_variation_category_id = $requestForVariationCategory->id;
        $record->kpi_limit = $kpiLimit;
        $record->created_by = \Confide::user()->id;
        $record->remarks = $remarks;
        $record->save();
    }

    public static function getKpiLimitUpdateLogs(RequestForVariationCategory $requestForVariationCategory)
    {
        $updateLogs = [];

        $records = self::where('request_for_variation_category_id', $requestForVariationCategory->id)->orderBy('created_at', 'ASC')->get();
        $previousRecord = null;

        foreach($records as $record)
        {
            array_push($updateLogs, [
                'id'                                => $record->id,
                'request_for_variation_category_id' => $record->request_for_variation_category_id,
                'previous_kpi_limit'                => is_null($previousRecord) ? null : $previousRecord->kpi_limit,
                'current_kpi_limit'                 => is_null($record->kpi_limit) ? null : $record->kpi_limit,
                'updated_by'                        => $record->user->name,
                'updated_at'                        => Carbon::parse($record->updated_at)->format(\Config::get('dates.full_format')),
                'remarks'                           => $record->remarks,
            ]);

            $previousRecord = $record;
        }

        return array_reverse($updateLogs);
    }
}


