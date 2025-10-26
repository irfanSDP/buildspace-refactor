<?php
namespace PCK\ContractorQuestionnaire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\ContractorQuestionnaire\Question;

class Questionnaire extends Model
{
    protected $table = 'contractor_questionnaires';

    protected $fillable = ['project_id', 'company_id', 'status', 'published_date', 'unpublished_date'];

    const STATUS_UNPUBLISHED = 1;
    const STATUS_PUBLISHED = 2;

    const STATUS_UNPUBLISHED_TEXT = 'Published';
    const STATUS_PUBLISHED_TEXT = 'Unpublished';

    protected static function boot()
    {
        parent::boot();
    }
    
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'contractor_questionnaire_id')->orderBy('created_at', 'desc');
    }

    public function editable()
    {
        return $this->project->status_id !== Project::STATUS_TYPE_POST_CONTRACT;
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_UNPUBLISHED:
                return self::STATUS_UNPUBLISHED_TEXT;
            case self::STATUS_PUBLISHED:
                return self::STATUS_PUBLISHED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }
}