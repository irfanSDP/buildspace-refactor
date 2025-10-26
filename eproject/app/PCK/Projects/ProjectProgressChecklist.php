<?php namespace PCK\Projects;

use Illuminate\Database\Eloquent\Model;

class ProjectProgressChecklist extends Model
{
    protected $fillable = ['skip_bq_prepared_published_to_tendering','skip_tender_document_uploaded','skip_form_of_tender_edited', 'skip_rot_form_submitted', 'skip_lot_form_submitted', 'skip_calling_tender_form_submitted', 'project_id'];

    public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}
}