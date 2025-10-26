<?php namespace PCK\StructuredDocument;

use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\ModelOperations;

class StructuredDocumentClause extends Model {

    protected $fillable = [
        'content',
        'is_editable',
        'parent_id',
        'priority',
        'structured_document_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $clause)
        {
            if( ! $clause->isDeletable() )
            {
                return false;
            }

            ModelOperations::deleteWithTrigger($clause->children);
        });
    }

    public function children()
    {
        return $this->hasMany('PCK\StructuredDocument\StructuredDocumentClause', 'parent_id')->orderBy('priority', 'asc');
    }

    public function isTemplate()
    {
        return StructuredDocument::find($this->structured_document_id)->isTemplate();
    }

    public function isDeletable()
    {
        if( $this->isTemplate() ) return true;
        if( $this->is_editable ) return true;

        return false;
    }

    public function isEditable()
    {
        return ( $this->isTemplate() || $this->is_editable );
    }

}