<?php namespace PCK\Users;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\LoggableInterface;

class UserCompanyLog extends Model implements LoggableInterface {

    protected $table = 'user_company_log';

    protected $fillable = [
        'user_id',
        'company_id',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function actionBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function elaboration():string
    {
        return trans('users.transferredTo', array( 'a' => $this->company->name ));
    }

    public static function flushRecords(User $user)
    {
        $records = self::where('user_id', $user->id)
            ->orWhere('created_by', $user->id)
            ->get();

        foreach($records as $record)
        {
            $record->delete();
        }
    }
}