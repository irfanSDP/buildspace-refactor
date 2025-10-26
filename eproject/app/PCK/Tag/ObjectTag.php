<?php namespace PCK\Tag;

use Illuminate\Database\Eloquent\Model;

class ObjectTag extends Model {

    protected $table = 'object_tags';

    protected $fillable = ['tag_id', 'object_id', 'object_class'];

    public function tag()
    {
        return $this->belongsTo('PCK\Tag\Tag');
    }

    public static function getTagIds($object, $category)
    {
        return self::whereHas('tag', function($q) use ($category){
                $q->where('category', '=', $category);
            })
            ->where('object_id', '=', $object->id)
            ->where('object_class', '=', get_class($object))
            ->lists('tag_id');
    }

    public static function getTagNames($object, $category)
    {
        return self::getTags($object, $category)->lists('name');
    }

    public static function getTags($object, $category)
    {
        return Tag::whereIn('id', self::getTagIds($object, $category))->orderBy('name', 'asc')->get();
    }

    public static function syncTags($object, $category, array $tagStrings)
    {
        $currentTags = self::getTags($object, $category)->lists('name');

        $tagsToRemove = array_diff($currentTags, $tagStrings);
        $tagsToAdd    = array_diff($tagStrings, $currentTags);

        self::removeTags($object, $category, $tagsToRemove);
        self::addTags($object, $category, $tagsToAdd);
    }

    public static function addTags($object, $category, array $tagStrings)
    {
        foreach($tagStrings as $tagString)
        {
            $tag = Tag::firstOrCreate([
                'category' => $category,
                'name'     => $tagString,
            ]);

            self::firstOrCreate([
                'tag_id'       => $tag->id,
                'object_id'    => $object->id,
                'object_class' => get_class($object),
            ]);
        }
    }

    public static function removeTags($object, $category, array $tagStrings)
    {
        foreach($tagStrings as $tagString)
        {
            $tag = Tag::where('category', '=', $category)
                ->where('name', '=', $tagString)
                ->first();

            if( ! $tag ) continue;

            self::where('tag_id', '=', $tag->id)
                ->where('object_id', '=', $object->id)
                ->where('object_class', '=', get_class($object))
                ->delete();

            if( self::where('tag_id', '=', $tag->id)->count() < 1)
            {
                $tag->delete();
            }
        }
    }
}