<?php namespace PCK\StructuredDocument;

use Illuminate\Database\Eloquent\Model;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

class StructuredDocument extends Model {

    const DEFAULT_MARGIN    = 10;
    const DEFAULT_FONT_SIZE = 14;

    protected $fillable = [
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'font_size',
        'title',
        'heading',
        'footer_text',
    ];

    public static function getDocument($object)
    {
        return self::where('object_id', '=', $object->id)->where('object_type', '=', get_class($object))->first();
    }

    public function object()
    {
        return $this->morphTo();
    }

    public function clauses()
    {
        return $this->hasMany('PCK\StructuredDocument\StructuredDocumentClause')->whereNull('parent_id')->orderBy('priority', 'asc');
    }

    public function isEdited()
    {
        return ( $this->created_at != $this->updated_at );
    }

    public function isTemplate()
    {
        switch(get_class(StructuredDocument::find($this->id)->object))
        {
            case get_class(new TemplateTenderDocumentFolder):
                return true;
            case get_class(new TenderDocumentFolder):
                return false;
            default:
                throw new \Exception('Invalid parent object.');
        }
    }

}