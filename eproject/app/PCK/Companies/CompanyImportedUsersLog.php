<?php namespace PCK\Companies;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class CompanyImportedUsersLog extends Model {

    protected $table = 'company_imported_users_log';

    protected $fillable = [
        'company_id',
        'user_id',
        'created_by',
        'import',
    ];

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public static function log(Company $company, User $user, $import = true)
    {
        return self::create(array(
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'created_by' => \Confide::user()->id,
            'import'     => $import,
        ));
    }
}