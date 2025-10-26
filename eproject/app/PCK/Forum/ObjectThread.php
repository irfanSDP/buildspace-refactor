<?php namespace PCK\Forum;

use Illuminate\Database\Eloquent\Model;

class ObjectThread extends Model {

    protected $table = 'object_forum_threads';

    protected $fillable = [
        'thread_id',
        'object_id',
        'object_type',
    ];

    public function thread()
    {
        return $this->belongsTo('PCK\Forum\Thread', 'thread_id', 'id');
    }

    public static function objectHasThread($object)
    {
        return ! is_null( self::getObjectThread($object) );
    }

    public static function getObjectThread($object)
    {
        $objectThreadRecord = \PCK\Forum\ObjectThread::where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->whereHas('thread', function($q){
                $q->where('type', '=', \PCK\Forum\Thread::TYPE_SECRET);
            })
            ->first();

        if( is_null($objectThreadRecord) ) return null;

        return $objectThreadRecord->thread;
    }
}