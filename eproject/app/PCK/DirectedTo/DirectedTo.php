<?php namespace PCK\DirectedTo;

use Illuminate\Database\Eloquent\Model;

/**
 * This class is the relationship map for objects being directed to other objects (targets).
 */
class DirectedTo extends Model {

    protected $table = 'directed_to';

    public function object()
    {
        return $this->morphTo();
    }

    public function target()
    {
        return $this->morphTo();
    }

    /**
     * Directs the object to the target.
     *
     * @param Model $target
     * @param Model $object
     *
     * @return bool
     */
    public static function directTo(Model $target, Model $object)
    {
        // Object is already directed to the target.
        if( self::isDirectedTo($target, $object) ) return true;

        $record = new self;
        $record->target()->associate($target);
        $record->object()->associate($object);

        return $record->save();
    }

    /**
     * Directs multiple objects to the target.
     *
     * @param       $targets
     * @param Model $object
     *
     * @throws \Exception
     */
    public static function directMultipleTo($targets, Model $object)
    {
        foreach($targets as $target)
        {
            static::directTo($target, $object);
        }
    }

    /**
     * Returns true if the object is directed to the target.
     *
     * @param Model $target
     * @param Model $object
     *
     * @return bool
     */
    public static function isDirectedTo(Model $target, Model $object)
    {
        foreach($object->directedTo as $record)
        {
            if( $record->target == $target ) return true;
        }

        return false;
    }

    /**
     * Returns the targets the object is targeted to.
     *
     * @param Model $object
     *
     * @return array
     */
    public static function getTargets(Model $object)
    {
        $targets = array();

        foreach(static::where('object_type', '=', get_class($object))->where('object_id', '=', $object->id)->get() as $record)
        {
            array_push($targets, $record->target);
        }

        return $targets;
    }

    /**
     * Return the ids of the targets.
     *
     * @param Model $object
     *
     * @return array
     */
    public static function getTargetIds(Model $object)
    {
        $ids = array();
        foreach(static::getTargets($object) as $target)
        {
            $ids[] = $target->id;
        }

        return $ids;
    }

    /**
     * Removes all directed-to relations of the object.
     *
     * @param Model $object
     *
     * @return bool
     */
    public static function removeRelations(Model $object)
    {
        return static::where('object_type', '=', get_class($object))->where('object_id', '=', $object->id)->delete();
    }

}