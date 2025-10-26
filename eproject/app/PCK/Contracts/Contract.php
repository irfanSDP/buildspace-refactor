<?php namespace PCK\Contracts;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model {

    const TYPE_PAM2006                          = 1;
    const TYPE_PAM2006_NAME                     = 'Standard';
    const TYPE_INDONESIA_CIVIL_CONTRACT         = 2;
    const TYPE_INDONESIA_CIVIL_CONTRACT_NAME    = 'Indonesia Civil Contract';
    const TYPE_INDONESIA_BUILDING_CONTRACT      = 3;
    const TYPE_INDONESIA_BUILDING_CONTRACT_NAME = 'Indonesia Building Contract';

    protected $with = array( 'menus' );

    protected $fillable = [ 'name', 'type' ];

    public static function getContractTypeIdByName($name)
    {
        $types = array(
            static::TYPE_PAM2006_NAME                     => static::TYPE_PAM2006,
            static::TYPE_INDONESIA_CIVIL_CONTRACT_NAME    => static::TYPE_INDONESIA_CIVIL_CONTRACT,
            static::TYPE_INDONESIA_BUILDING_CONTRACT_NAME => static::TYPE_INDONESIA_BUILDING_CONTRACT,
        );

        return $types[ $name ] ?? null;
    }

    public function projects()
    {
        return $this->hasMany('PCK\Projects\Project');
    }

    public function menus()
    {
        return $this->hasMany('PCK\Menus\Menu')->orderBy('id', 'asc')->rememberForever();
    }

    public static function findByType($type)
    {
        return self::where('type', '=', $type)->first();
    }

    public function clauses()
    {
        return $this->hasMany('PCK\Clauses\Clause')->orderBy('id', 'desc');
    }

}