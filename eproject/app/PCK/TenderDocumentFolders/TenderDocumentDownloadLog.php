<?php namespace PCK\TenderDocumentFolders;

use Illuminate\Database\Eloquent\Model;

class TenderDocumentDownloadLog extends Model {

	protected $table = 'tender_document_download_logs';

	protected static function boot()
    {
        parent::boot();
	}

	public static function deleteDownloadLogEntryByFileId($fileId) {
		self::where('tender_document_id', $fileId)
			->delete();
	}
}