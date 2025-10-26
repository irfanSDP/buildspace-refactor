<?php namespace PCK\Base;

trait ModuleAttachmentTrait {

    public function attachments()
    {
        return $this->morphMany('PCK\ModuleUploadedFiles\ModuleUploadedFile', 'uploadable')->whereNull('type')->orderBy('id');
    }

    public function getAttachmentDetails()
    {
        return Upload::whereIn('id', $this->attachments()->lists('upload_id'))->orderBy('id', 'desc')->get();
    }

    public function copyAttachmentsTo($targetModel)
    {
        \PCK\Helpers\ModuleAttachment::copyAttachments($this, $targetModel);
    }

}