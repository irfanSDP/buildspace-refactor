<?php namespace PCK\TechnicalEvaluationAttachments;

use Illuminate\Database\Eloquent\Model;

class TechnicalEvaluationAttachment extends Model {

    protected $fillable = [
        'company_id',
        'item_id',
        'upload_id',
        'filename',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $attachment)
        {
            \DB::transaction(function () use ($attachment)
            {
                $attachment->upload->delete();
            });
        });
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    public function listItem()
    {
        return $this->belongsTo('PCK\TechnicalEvaluationAttachmentListItems\TechnicalEvaluationAttachmentListItem', 'item_id');
    }

    public function upload()
    {
        return $this->belongsTo('PCK\Base\Upload');
    }

    public function getPresentableFileName($length = null, $ellipsisMark = '...')
    {
        $filename = $this->filename;

        if( $length ) $filename = substr($filename, 0, $length) . $ellipsisMark;

        return $filename . '.' . $this->upload->extension;
    }

}