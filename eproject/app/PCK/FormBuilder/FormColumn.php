<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormColumn extends Model
{
    protected $table = 'form_columns';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (self $model)
        {
            $model->deleteRelatedModels();
        });

        static::deleted(function (self $model)
        {
            self::updatePriority($model);
        });
    }

    public function dynamicForm()
    {
        return $this->belongsTo('PCK\FormBuilder\DynamicForm', 'dynamic_form_id');
    }

    public function sections()
    {
        return $this->hasMany('PCK\FormBuilder\FormColumnSection', 'form_column_id');
    }

    public static function createNewColumn(DynamicForm $form, $name)
    {
        $column                  = new self();
        $column->dynamic_form_id = $form->id;
        $column->name            = $name;
        $column->priority        = self::getNextFreePriority($form);
        $column->save();

        return self::find($column->id);
    }

    public function clone(DynamicForm $form)
    {
        $newColumn = self::createNewColumn($form, $this->name);

        foreach($this->sections()->orderBy('priority', 'ASC')->get() as $originSection)
        {
            $originSection->clone($newColumn);
        }
    }

    public static function getNextFreePriority(DynamicForm $form)
    {
        $latestRecord = self::where('dynamic_form_id', $form->id)->orderBy('priority', 'DESC')->first();

        if(is_null($latestRecord)) return 0;

        return ($latestRecord->priority + 1);
    }

    public static function updatePriority(self $removedRecord)
    {
        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE dynamic_form_id = ' . $removedRecord->dynamicForm->id . ' AND priority > ' . $removedRecord->priority . ';');

        DB::update($query);
    }

    public static function swap(self $draggedColumn, self $swappedColumn)
    {
        $draggedColumnPriority = $draggedColumn->priority;
        $swappedColumnPriority = $swappedColumn->priority;

        $draggedColumn->priority = $swappedColumnPriority;
        $draggedColumn->save();

        $swappedColumn->priority = $draggedColumnPriority;
        $swappedColumn->save();
    }

    private function deleteRelatedModels()
    {
        foreach($this->sections as $section)
        {
            $section->delete();
        }
    }
}