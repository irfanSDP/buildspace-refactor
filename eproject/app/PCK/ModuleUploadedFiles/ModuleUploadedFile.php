<?php namespace PCK\ModuleUploadedFiles;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ModuleUploadedFile extends Model {

	const TYPE_CLAIM_CERTIFICATE_INVOICE = 1;

	protected $with = array( 'file' );

	protected $fillable = array( 'upload_id', 'uploadable_id', 'uploadable_type', 'type' );

	public function file()
	{
		return $this->belongsTo('PCK\Base\Upload', 'upload_id');
	}

	public function uploadable()
	{
		return $this->morphTo();
	}

	protected static function deletePreviousAttachments(Model $model, $type = null)
    {
        foreach(self::getAttachments($model, $type) as $attachment)
        {
            $attachment->delete();
        }
    }

    public static function getAttachments(Model $model, $type = null)
    {
        return self::where('uploadable_id', '=', $model->id)
            ->where('uploadable_type', '=', get_class($model))
            ->where('type', '=', $type)
            ->get();
    }

    public function copyTo(Model $targetModel)
    {
        $clone = $this->replicate();

        $clonedFile = $this->file->getCopy();

        $clone->upload_id = $clonedFile->id;
        $clone->uploadable_id = $targetModel->id;
        $clone->uploadable_type = get_class($targetModel);
        $clone->created_at = Carbon::parse($this->file->created_at);
        $clone->updated_at = Carbon::parse($this->file->updated_at);

        return $clone->save(['timestamps' => false]);
    }
}