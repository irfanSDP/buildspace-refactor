<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\Elements\Element;

class ElementAttribute extends Model
{
    protected $table = 'element_attributes';

    public static function createNewAttribute($element, $attrName, $attrValue = null)
    {
        if(self::findAttribute($element, $attrName)) return null;

        $attribute                = new self();
        $attribute->element_id    = $element->id;
        $attribute->element_class = get_class($element);
        $attribute->name          = $attrName;
        $attribute->value         = $attrValue;
        $attribute->save();

        return self::find($attribute->id);
    }

    public static function getElementAttributes($element)
    {
        $elementAttributes = self::where('element_id', $element->id)->where('element_class', get_class($element))->orderBy('id', 'ASC')->get();
        $attributes        = [];

        foreach($elementAttributes as $attribute)
        {
            $attributes[$attribute->name] = $attribute->value;
        }

        return $attributes;
    }

    public static function findAttribute($element, $attrName)
    {
        return self::where('element_id', $element->id)->where('element_class', get_class($element))->where('name', $attrName)->first();
    }

    public static function updateAttribute($element, $name, $value = null)
    {
        $attribute = self::findAttribute($element, $name);

        if($attribute)
        {
            $attribute->value = $value;
            $attribute->save();
        }
    }

    public static function deleteAttribute($element, $name)
    {
        $attribute = self::findAttribute($element, $name);

        if($attribute)
        {
            $attribute->delete();
        }
    }

    public static function purge($element)
    {
        self::where('element_id', $element->id)->where('element_class', get_class($element))->delete();
    }
}