<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Buildspace\AccountGroup;

class AccountCode extends Model
{
    use SoftDeletingTrait;

    protected $connection = 'buildspace';
    protected $table      = 'bs_account_codes';

    const ACCOUNT_TYPE_PIV = 1;
    const ACCOUNT_TYPE_PCN = 2;
    const ACCOUNT_TYPE_PDN = 4;
    const ACCOUNT_CODE_TYPE_IV = 5;
    const ACCOUNT_CODE_TYPE_CN = 6;
    const ACCOUNT_CODE_TYPE_DN = 7;
    const ACCOUNT_CODE_TYPE_CM = 8;

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public static function getTypeText($type)
    {
        switch($type)
        {
            case self::ACCOUNT_TYPE_PIV:
                return 'PIV';
            case self::ACCOUNT_TYPE_PCN:
                return 'PCN';
            case self::ACCOUNT_TYPE_PDN:
                return 'PDN';
            case self::ACCOUNT_CODE_TYPE_IV:
                return 'IV';
            case self::ACCOUNT_CODE_TYPE_CN:
                return 'CN';
            case self::ACCOUNT_CODE_TYPE_DN:
                return 'DN';
            case self::ACCOUNT_CODE_TYPE_CM:
                return 'CM';
            default:
                throw new \Exception('Invalid account code type');
        }
    }
}

