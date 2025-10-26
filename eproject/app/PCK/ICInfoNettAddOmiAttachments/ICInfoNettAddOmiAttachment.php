<?php namespace PCK\ICInfoNettAddOmiAttachments;

use Illuminate\Database\Eloquent\Model;

class ICInfoNettAddOmiAttachment extends Model {

	protected $table = 'ic_info_nett_addition_omission_attachments';

	public function interimClaimInformation()
	{
		return $this->belongsTo('PCK\InterimClaimInformation\InterimClaimInformation');
	}

	public function file()
	{
		return $this->belongsTo('PCK\Base\Upload', 'upload_id');
	}

}