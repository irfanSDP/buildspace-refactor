<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model {

    protected $table = 'form_of_tender_clauses';
    const DEFEAULT_TEXT = 'Template Clause Item';

    protected static function boot()
    {
        parent::boot();

        static::updating(function(self $clause)
        {
            if( ( ! $clause->is_template ) && ( ! $clause->is_editable ) )
            {
                $clause->clause = $clause->getOriginal('clause');
            }
        });
    }

    public function children()
    {
        return $this->hasMany('PCK\FormOfTender\Clause', 'parent_id');
    }

    public function formOfTender()
    {
        return $this->belongsTo('PCK\FormOfTender\FormOfTender');
    }

    public function dissociateChildren()
    {
        foreach($this->children as $child)
        {
            $child->dissociateFromParent();
        }
    }

    public function dissociateFromParent()
    {
        $this->parent_id = 0;
        $this->save();
    }

}