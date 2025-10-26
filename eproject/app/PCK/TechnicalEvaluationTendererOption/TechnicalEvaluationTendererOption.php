<?php namespace PCK\TechnicalEvaluationTendererOption;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem;

class TechnicalEvaluationTendererOption extends Model {

    protected $fillable = [
        'item_id',
        'company_id',
        'option_id',
    ];
    protected $hidden   = [
        'updated_at',
        'created_at'
    ];

    public function tenderer()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationItems\TechnicalEvaluationItem', 'item_id', 'id')
            ->where('type', '=', TechnicalEvaluationItem::TYPE_ITEM)
            ->where('id', '=', $this->item_id);
    }

    public function option()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationItems\TechnicalEvaluationItem', 'option_id', 'id')
            ->where('type', '=', TechnicalEvaluationItem::TYPE_OPTION)
            ->where('id', '=', $this->option_id);
    }

    /**
     * Calculates the tenderer's total score for the item.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $object
     *
     * @return int|mixed
     * @throws \Exception
     */
    public static function getTendererScore(Company $company, TechnicalEvaluationItem $object)
    {
        $totalScore = 0;

        switch($object->type)
        {
            case TechnicalEvaluationItem::TYPE_SET:

                foreach($object->getLevel(TechnicalEvaluationItem::TYPE_ASPECT) as $aspect)
                {
                    $totalScore += self::getTendererScore($company, $aspect) * $aspect->value;
                }

                break;
            case TechnicalEvaluationItem::TYPE_ASPECT:
            case TechnicalEvaluationItem::TYPE_CRITERIA:

                foreach($object->getLevel(TechnicalEvaluationItem::TYPE_ITEM) as $item)
                {
                    if( $option = static::getTendererOption($company, $item) ) $totalScore += $option->value;
                }

                break;
            case TechnicalEvaluationItem::TYPE_ITEM:

                if( $option = static::getTendererOption($company, $object) ) $totalScore += $option->value;

                break;
            case TechnicalEvaluationItem::TYPE_OPTION:

                if( $option = static::getTendererOption($company, $object->parent) ) $totalScore += $option->value;

                break;
            default:
                throw new \Exception('Invalid item type');
        }

        return $totalScore;
    }

    /**
     * Returns the option chosen by the tenderer.
     * Returns null if no option has been chosen for the item.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $item
     *
     * @return \Illuminate\Support\Collection|null|static
     * @throws \Exception
     */
    public static function getTendererOption(Company $company, TechnicalEvaluationItem $item)
    {
        TechnicalEvaluationItem::validateType($item, TechnicalEvaluationItem::TYPE_ITEM);

        $record = static::where('item_id', '=', $item->id)
            ->where('company_id', '=', $company->id)
            ->first();

        return $record ? TechnicalEvaluationItem::find($record->option_id) : null;
    }

    /**
     * Removes all of the Tenderer's previous chosen options.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $set
     *
     * @throws \Exception
     */
    public static function removeTendererOptions(Company $company, TechnicalEvaluationItem $set)
    {
        TechnicalEvaluationItem::validateType($set, TechnicalEvaluationItem::TYPE_SET);

        $items = $set->getLevel(TechnicalEvaluationItem::TYPE_ITEM);

        foreach($items as $item)
        {
            self::where('company_id', '=', $company->id)
                ->where('item_id', '=', $item->id)
                ->delete();
        }
    }

    /**
     * Adds a tenderer option.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $item
     * @param TechnicalEvaluationItem $option
     * @param null                    $remarks
     *
     * @return bool
     * @throws \Exception
     */
    public static function add(Company $company, TechnicalEvaluationItem $item, TechnicalEvaluationItem $option, $remarks = null)
    {
        TechnicalEvaluationItem::validateType($item, TechnicalEvaluationItem::TYPE_ITEM);
        TechnicalEvaluationItem::validateType($option, TechnicalEvaluationItem::TYPE_OPTION);

        if( $option->parent_id != $item->id ) throw new \Exception('Option does not belong to Item.');

        $remarks = trim($remarks);

        $tendererOption             = new self;
        $tendererOption->company_id = $company->id;
        $tendererOption->item_id    = $item->id;
        $tendererOption->option_id  = $option->id;
        $tendererOption->remarks    = empty( $remarks ) ? null : $remarks;

        return $tendererOption->save();
    }

    /**
     * Returns a collection of all the Tenderer's chosen options.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $set
     *
     * @return Collection
     * @throws \Exception
     */
    public static function getTendererOptions(Company $company, TechnicalEvaluationItem $set)
    {
        TechnicalEvaluationItem::validateType($set, TechnicalEvaluationItem::TYPE_SET);

        $collection = new Collection();

        foreach($set->getLevel(TechnicalEvaluationItem::TYPE_ITEM) as $item)
        {
            $collection->push(self::getTendererOption($company, $item));
        }

        return $collection;
    }

    /**
     * Returns the ids of all the Tenderer's chosen options.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $set
     *
     * @return array
     * @throws \Exception
     */
    public static function getTendererOptionIds(Company $company, TechnicalEvaluationItem $set)
    {
        TechnicalEvaluationItem::validateType($set, TechnicalEvaluationItem::TYPE_SET);

        $ids = array();

        foreach(self::getTendererOptions($company, $set) as $option)
        {
            if( ! $option ) continue;

            $ids[] = $option->id;
        }

        return $ids;
    }

    /**
     * Returns the Tenderer's remarks for the option.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $option
     *
     * @return null
     */
    public static function getOptionRemarks(Company $company, TechnicalEvaluationItem $option)
    {
        $tendererOption = self::where('company_id', '=', $company->id)
            ->where('option_id', '=', $option->id)
            ->first();

        return $tendererOption ? $tendererOption->remarks : null;
    }

    /**
     * Returns the Tenderer's remarks for the a list of options, grouped by option id
     *
     * @param Company $company
     * @param array   $optionIds
     *
     * @return array
     */
    public static function getOptionRemarksGroupedByOptionIds(Company $company, $optionsIds)
    {
        $tendererOptions = self::where('company_id', '=', $company->id)
            ->whereIn('option_id', $optionsIds)
            ->get();

        return $tendererOptions->lists('remarks', 'option_id');
    }

    /**
     * Returns the remarks of all Options for a tenderer.
     *
     * @param Company                 $company
     * @param TechnicalEvaluationItem $set
     *
     * @return array
     */
    public static function getAllOptionRemarks(Company $company, TechnicalEvaluationItem $set)
    {
        TechnicalEvaluationItem::validateType($set, TechnicalEvaluationItem::TYPE_SET);

        $optionRemarks = [];
        $optionsIds    = [];

        foreach(self::getTendererOptions($company, $set) as $option)
        {
            if( ! $option ) continue;

            array_push($optionsIds, $option->id);
        }

        $optionRemarksGroupedByOptionIds = self::getOptionRemarksGroupedByOptionIds($company, $optionsIds);

        foreach($optionRemarksGroupedByOptionIds as $optionId => $remarks)
        {
            $optionRemarks[$optionId] = is_null($remarks) ? null : trim($remarks);
        }

        return $optionRemarks;
    }
}