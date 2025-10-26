<?php namespace PCK\TechnicalEvaluationItems;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TechnicalEvaluationItem extends Model {

    const TYPE_SET      = 1;
    const TYPE_ASPECT   = 2;
    const TYPE_CRITERIA = 4;
    const TYPE_ITEM     = 8;
    const TYPE_OPTION   = 16;

    const VALUE_NAME_ASPECT  = 'Weighting';
    const VALUE_NAME_DEFAULT = 'Score';

    const NAME_SET      = 'Sets';
    const NAME_ASPECT   = 'Aspects';
    const NAME_CRITERIA = 'Criteria';
    const NAME_ITEM     = 'Items';
    const NAME_OPTION   = 'Options';

    protected $fillable = [
        'parent_id',
        'name',
        'value',
        'type',
    ];
    protected $hidden   = [
        'updated_at',
        'created_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function(self $item)
        {
            $item->value = round($item->value, 2);

            if( ! $item->isValid() ) return false;

            if( $item->isDirty('value') )
            {
                $item->recalculateChildrenValue();
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationItems\TechnicalEvaluationItem', 'parent_id', 'id');
    }

    public function getValueAttribute($value)
    {
        return round($value, 2);
    }

    public function children()
    {
        return $this->hasMany('PCK\TechnicalEvaluationItems\TechnicalEvaluationItem', 'parent_id')
            ->where('type', '!=', $this->type ?? 0)
            ->where('type', '!=', self::TYPE_SET)
            ->orderBy('value', 'desc')
            ->orderBy('id', 'asc');
    }

    public function loadChildren()
    {
        foreach($this->children as $child) $child->loadChildren();
    }

    /**
     * Returns the type of the item's children.
     *
     * @return bool|int
     * @throws \Exception
     */
    public function getChildrenType()
    {
        switch($this->type)
        {
            case static::TYPE_SET:
                return static::TYPE_ASPECT;
            case static::TYPE_ASPECT:
                return static::TYPE_CRITERIA;
            case static::TYPE_CRITERIA:
                return static::TYPE_ITEM;
            case static::TYPE_ITEM:
                return static::TYPE_OPTION;
            case static::TYPE_OPTION:
                return false;
            default:
                throw new \Exception('Invalid Item Type');
        }
    }

    /**
     * Returns the name of the corresponding item type.
     *
     * @param $type
     *
     * @return null|string
     * @throws \Exception
     */
    public static function getTypeName($type)
    {
        switch($type)
        {
            case static::TYPE_SET:
                return static::NAME_SET;
            case static::TYPE_ASPECT:
                return static::NAME_ASPECT;
            case static::TYPE_CRITERIA:
                return static::NAME_CRITERIA;
            case static::TYPE_ITEM:
                return static::NAME_ITEM;
            case static::TYPE_OPTION:
                return static::NAME_OPTION;
            default:
                throw new \Exception('Invalid Item Type');
        }
    }

    /**
     * Returns the name of the item's type.
     *
     * @return null|string
     * @throws \Exception
     */
    public function typeName()
    {
        return static::getTypeName($this->type);
    }

    /**
     * Returns the name of the value based on the item's type.
     *
     * @param $type
     *
     * @return null|string
     * @throws \Exception
     */
    public static function getValueName($type)
    {
        switch($type)
        {
            case static::TYPE_SET:
                return null;
            case static::TYPE_ASPECT:
                return static::VALUE_NAME_ASPECT;
            case static::TYPE_CRITERIA:
            case static::TYPE_ITEM:
            case static::TYPE_OPTION:
                return static::VALUE_NAME_DEFAULT;
            default:
                throw new \Exception('Invalid Item Type');
        }
    }

    /**
     * Returns the name of the item's type.
     *
     * @return null|string
     * @throws \Exception
     */
    public function valueName()
    {
        return static::getValueName($this->type);
    }

    /**
     * Returns the item's ancestors.
     *
     * @return array
     */
    public function getAncestors()
    {
        $ancestors = array();

        if( $this->parent_id )
        {
            $parent = static::find($this->parent_id);

            foreach($parent->getAncestors() as $ancestor)
            {
                array_push($ancestors, $ancestor);
            }

            array_push($ancestors, $parent);
        }

        return $ancestors;
    }

    /**
     * Calculates the value of all sibling items.
     *
     * @return int
     */
    public function getSiblingsValue()
    {
        $siblingsValue = 0;
        foreach($this->getSiblings() as $sibling)
        {
            $siblingsValue += $sibling->value;
        }

        return $siblingsValue;
    }

    /**
     * Returns all other item's under the same parent.
     *
     * @return mixed
     */
    public function getSiblings()
    {
        $siblings = static::where('parent_id', '=', $this->parent_id)
            ->where('type', '=', $this->type);

        if( $this->exists )
        {
            $siblings->where('id', '!=', $this->id);
        }

        return $siblings->get();
    }

    /**
     * Returns true if the item's properties are valid.
     *
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {
        return ( $this->value <= $this->getMaxValidValue() );
    }

    /**
     * Throws an Exception if the item's type does not match.
     *
     * @param $item
     * @param $type
     *
     * @throws \Exception
     */
    public static function validateType($item, $type)
    {
        if( $item->type != $type ) throw new \Exception("Wrong item type. Type must be " . static::getTypeName($type));
    }

    /**
     * Returns the maximum value for the childrens' total.
     * The sum of the children's total cannot exceed this value.
     *
     * @return int|mixed
     * @throws \Exception
     */
    public function getMaxChildrenValueTotal()
    {
        switch($this->type)
        {
            case static::TYPE_SET:
                return 1;
            case static::TYPE_ASPECT:
                return 100;
            case static::TYPE_CRITERIA:
            case static::TYPE_ITEM:
            case static::TYPE_OPTION:
                break;
            default:
                throw new \Exception('Invalid Item Type');
        }

        return $this->value;
    }

    /**
     * Returns the maximum valid value for an item.
     *
     * @return int|mixed
     * @throws \Exception
     */
    public function getMaxValidValue()
    {
        switch($this->type)
        {
            case static::TYPE_SET:
                $value = $this->getMaxChildrenValueTotal();
                break;

            case static::TYPE_ASPECT:
            case static::TYPE_CRITERIA:
            case static::TYPE_ITEM:
                $value = $this->parent->getMaxChildrenValueTotal() - $this->getSiblingsValue();
                break;

            case static::TYPE_OPTION:
                $value = $this->parent->getMaxChildrenValueTotal();
                break;

            default:
                throw new \Exception('Invalid Item Type');
        }

        return round($value, 2);
    }

    /**
     * Calculates the value total of the item's children.
     *
     * @return int
     */
    public function getChildrenValueTotal()
    {
        $totalValue = 0;

        foreach($this->children as $child)
        {
            $totalValue += $child->value;
        }

        return round($totalValue, 2);
    }

    /**
     * Returns true if no children can be added to the item.
     *
     * @return bool
     * @throws \Exception
     */
    public function saturated()
    {
        if( $this->type == static::TYPE_ITEM ) return false;

        return ( $this->getChildrenValueTotal() >= $this->getMaxChildrenValueTotal() );
    }

    /**
     * Returns true if all descendants of the item are sufficient (sum of the children's score is sufficient).
     *
     * @return bool
     */
    public function isDescendantsSufficient()
    {
        // Any number of options greater than zero is sufficient for an item type.
        if( $this->type == static::TYPE_ITEM ) return ( $this->children->count() > 0 );

        // Options do not have children.
        if( $this->type == static::TYPE_OPTION ) return true;

        if( ! $this->saturated() ) return false;

        foreach($this->children as $child)
        {
            if( ! $child->isDescendantsSufficient() ) return false;
        }

        return true;
    }

    /**
     * Recalculates the value of the item's children,
     * based on the item's new value.
     */
    private function recalculateChildrenValue()
    {
        if( $this->type == static::TYPE_SET ) return;
        if( $this->type == static::TYPE_ASPECT ) return;
        if( $this->type == static::TYPE_OPTION ) return;

        $originalValue = $this->getOriginal('value');

        $totalAvailable = $this->value;

        foreach($this->children as $child)
        {
            $newValue = $this->value;

            if( $originalValue != 0 )
            {
                $childWeighting = $child->value / $originalValue;

                $newValue = $childWeighting * $this->value;
            }

            if( $child->type == static::TYPE_OPTION )
            {
                $child->value = $newValue;
                $child->save();
                continue;
            }

            // Ensure that the sum of the childrens' values will not be greater than the parent's.

            // If remainder is less than newValue,
            // use remainder instead of newValue.
            if( $totalAvailable < $newValue ) $newValue = $totalAvailable;

            $totalAvailable -= $newValue;

            if( $totalAvailable < 0 ) $newValue = 0;

            $child->value = $newValue;
            $child->save();
        }
    }

    /**
     * Copies an item and its descendants.
     *
     * @param null $parentId
     *
     * @return Model
     */
    public function copy($parentId = null)
    {
        $copy = $this->replicate(array(
            'id',
            'created_at',
        ));

        $copy->parent_id = $parentId;

        $copy->save();

        foreach($this->children as $child)
        {
            $child->copy($copy->id);
        }

        return $copy;
    }

    /**
     * Returns a collection of the item's descendants of a specific level (type).
     *
     * @param $type
     *
     * @return Collection|mixed
     * @throws \Exception
     */
    public function getLevel($type)
    {
        if( $this->getChildrenType() == $type ) return $this->children;

        $collection = new Collection();

        foreach($this->children as $child)
        {
            $descendantsOfType = $child->getLevel($type);

            if( $descendantsOfType->count() > 0 )
            {
                foreach($descendantsOfType as $descendant)
                {
                    $collection->push($descendant);
                }
            }
        }

        return $collection;
    }

}