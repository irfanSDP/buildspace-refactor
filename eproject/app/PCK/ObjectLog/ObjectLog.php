<?php namespace PCK\ObjectLog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ObjectLog extends Model
{
    protected $table = 'object_logs';

    const MODULE_VENDOR_REGISTRATION_COMPANY_DETAILS          = 1;
    const MODULE_VENDOR_REGISTRATION_REGISTRATION_FORM        = 2;
    const MODULE_VENDOR_REGISTRATION_COMPANY_PERSONNEL        = 3;
    const MODULE_VENDOR_REGISTRATION_PROJECT_TRACK_RECORD     = 4;
    const MODULE_VENDOR_REGISTRATION_VENDOR_PREQUALIFICATION  = 5;
    const MODULE_VENDOR_REGISTEATION_SUPPLIER_CREDIT_FACILITY = 6;

    const ACTION_CREATE     = 1;
    const ACTION_EDIT       = 2;
    const ACTION_DELETE     = 3;
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActionDescription()
    {
        $description = '';

        switch($this->action_identifier)
        {
            case self::ACTION_CREATE:
                $description = trans('general.create');
                break;
            case self::ACTION_EDIT:
                $description = trans('general.edit');
                break;
            case self::ACTION_DELETE:
                $description = trans('general.delete');
                break;
        }

        return $description;
    }

    public static function recordAction(Model $object, $actionIdentifier, $moduleIdentifier = null)
    {
        $log = new self;
        $log->object_id         = $object->id;
        $log->object_class      = get_class($object);
        $log->module_identifier = $moduleIdentifier;
        $log->action_identifier = $actionIdentifier;
        $log->user_id           = \Confide::user()->id;
        $log->save();
    }

    public static function getActionLogs(Model $object, $moduleIdentifier = null, $order = 'DESC')
    {
        $query = self::where('object_id', $object->id)->where('object_class', get_class($object));

        if(is_null($moduleIdentifier))
        {
            $query->whereNull('module_identifier');
        }
        else
        {
            $query->where('module_identifier', $moduleIdentifier);
        }

        $records = $query->orderBy('id', $order)->get();

        $actionLogs = [];

        foreach($records as $record)
        {
            $actionLogs[] = [
                'user'     => $record->user->name,
                'action'   => $record->getActionDescription(),
                'datetime' => Carbon::parse($record->created_at)->format(\Config::get('dates.readable_timestamp_slash')),
            ];
        }

        return $actionLogs;
    }
}