<?php namespace PCK\DigitalStar\Evaluation;

use Illuminate\Database\Eloquent\Model;
use PCK\DigitalStar\TemplateForm\DsTemplateForm;

class DsCycleTemplateForm extends Model
{
    protected $table = 'ds_cycle_template_forms';

    protected static $bindingTypes = [
        'company',
        'project'
    ];

    public static function initialize(DsCycle $cycle)
    {
        foreach (self::$bindingTypes as $bindingType) {
            $exists = self::where('ds_cycle_id', $cycle->id)
                ->where('type', $bindingType)
                ->exists();

            if (! $exists) {
                $record = new self;
                $record->ds_cycle_id = $cycle->id;
                $record->type = $bindingType;
                $record->save();
            }
        }
    }

    public function cycle()
    {
        return $this->belongsTo(DsCycle::class, 'ds_cycle_id');
    }

    public function templateForm()
    {
        return $this->belongsTo(DsTemplateForm::class, 'ds_template_form_id');
    }
}