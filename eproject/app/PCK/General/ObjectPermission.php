<?php namespace PCK\General;

use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;

class ObjectPermission extends Model {

    protected $fillable = [
        'user_id',
        'module_identifier',
    ];

    public function object()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public static function grant(User $user, $object)
    {
        $record = new self;
        $record->object()->associate($object);
        $record->user_id   = $user->id;

        return $record->save();
    }

    public static function getRecord(User $user, $object)
    {
        return self::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->where('user_id', '=', $user->id)
            ->first();
    }

    public static function isEditor(User $user, $object)
    {
        if( $user->isSuperAdmin() ) return true;

        return self::getRecord($user, $object)->is_editor ?? false;
    }

    public static function isAssigned(User $user, $object)
    {
        if( $user->isSuperAdmin() ) return true;

        return static::getRecord($user, $object) ? true : false;
    }

    public static function revoke(User $user, $object)
    {
        if( $record = self::getRecord($user, $object) ) return $record->delete();

        return true;
    }

    public static function getUserList($object)
    {
        return self::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->get();
    }

    public static function getViewerList($object)
    {
        return self::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->where('is_editor', '=', false)
            ->get();
    }

    public static function getEditorList($object)
    {
        return self::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->where('is_editor', '=', true)
            ->get();
    }

    public static function setAsEditor(User $user, $object, $setAsEditor = true)
    {
        if( ! $record = self::getRecord($user, $object) ) return false;

        $record->is_editor = $setAsEditor;

        return $record->save();
    }

    public static function getRecordsByObjectType(User $user, $object)
    {
        return self::where('object_type', '=', get_class($object))
            ->where('user_id', '=', $user->id)
            ->get();
    }

}