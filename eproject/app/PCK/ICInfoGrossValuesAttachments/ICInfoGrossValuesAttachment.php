<?php namespace PCK\ICInfoGrossValuesAttachments;

use Illuminate\Database\Eloquent\Model;

class ICInfoGrossValuesAttachment extends Model {

	protected $table = 'ic_info_gross_values_attachments';

	public function interimClaimInformation()
	{
		return $this->belongsTo('PCK\InterimClaimInformation\InterimClaimInformation');
	}

	public function file()
	{
		return $this->belongsTo('PCK\Base\Upload', 'upload_id');
	}

}