<?php namespace PCK\ProjectReport;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Statuses\FormStatus;
use PCK\Projects\Project;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\Users\User;

class ProjectReport extends Model implements Verifiable, FormStatus
{
    use SoftDeletingTrait;

    protected $table = 'project_reports';

    protected static function boot()
    {
        parent::boot();

        static::created(function(self $model)
        {
            // root_id represents the lineage
            if($model->isOriginalRevision() && $model->isDraft())
            {
                $model->root_id = $model->id;
                $model->save();
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function root()
    {
        return $this->belongsTo(self::class, 'root_id');
    }

    public function origin()
    {
        return $this->belongsTo(self::class, 'origin_id');
    }

    public function columns()
    {
        return $this->hasMany(ProjectReportColumn::class, 'project_report_id');
    }

    public function projectReportTypeMapping()
    {
        return $this->belongsTo(ProjectReportTypeMapping::class, 'project_report_type_mapping_id');
    }

    public function actionLogs()
    {
        return $this->hasMany(ActionLog::class, 'project_report_id');
    }

    public function isTemplate()
    {
        return is_null($this->project_id);
    }

    public function isDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

    public function isPendingForApproval()
    {
        return $this->status == self::STATUS_PENDING_VERIFICATION;
    }

    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function isOriginalRevision()
    {
        return ($this->revision == 0);
    }

    public function isRevisedReport()
    {
        return ($this->revision > 0);
    }

    public function getRevisionText()
    {
        return $this->revision === 0 ? trans('projectReport.original') : $this->revision;
    }

    public static function getStatusText($status = null)
    {
        $mapping = [
            self::STATUS_DRAFT                => trans('forms.draft'),
            self::STATUS_PENDING_VERIFICATION => trans('forms.pendingForApproval'),
            self::STATUS_COMPLETED            => trans('forms.completed'),
        ];

        return is_null($status) ? $mapping : $mapping[$status];
    }

    public static function hasProjectReports($templateId)
    {
        $count = 0;
        $firstRecord = self::where('origin_id', $templateId)->withTrashed()->first();
        if ($firstRecord) {
            if (is_null($firstRecord->deleted_at)) {
                $count++;
            }
            $count += self::where('root_id', $firstRecord->id)->whereNull('deleted_at')->count() > 0;
        }
        return $count > 0;
    }

    public static function latestApprovedProjectReport(Project $project, ProjectReportTypeMapping $mapping)
    {
        return self::where('project_id', $project->id)
                ->where('project_report_type_mapping_id', $mapping->id)
                ->where('status', self::STATUS_COMPLETED)
                ->orderBy('revision', 'DESC')
                ->first();
    }

    public static function getLatestProjectReport(Project $project, ProjectReportTypeMapping $mapping)
    {
        return self::where('project_id', $project->id)
                ->where('project_report_type_mapping_id', $mapping->id)
                ->orderBy('revision', 'DESC')
                ->first();
    }

    public function getLatestClonedTemplateInSeries()
    {
        $query = "SELECT t.*
                  FROM project_reports r
                  INNER JOIN project_reports t ON t.id = r.origin_id 
                  WHERE r.project_report_type_mapping_id = " . $this->project_report_type_mapping_id . "
                  AND r.root_id = " . $this->root_id . "
                  AND t.project_id IS NULL
                  AND r.deleted_at IS NULL
                  AND t.deleted_at IS NULL
                  ORDER BY t.revision DESC
                  LIMIT 1";
        
        $result = DB::select(DB::raw($query));

        if(count($result) === 0) return null;

        return self::find($result[0]->id);
    }

    public function getOnApprovedView()
    {
        return 'projectReport.approved';
    }

    public function getOnRejectedView()
    {
        return 'projectReport.rejected';
    }

    public function getOnPendingView()
    {
        return 'projectReport.pending';
    }

    public function getShowRoute()
    {
        return route('projectReport.show', [$this->project->id, $this->project_report_type_mapping_id]);
    }

    public function getRoute()
    {
        return route('projectReport.verify', [$this->project->id, $this->project_report_type_mapping_id]);
    }

    public function getPostApprovalRoute()
    {
        return route('projects.show', [$this->project->id]);
    }

    public function getViewData($locale)
    {
        $viewData = [
            'senderName'			  => \Confide::user()->name,
			'project_title' 		  => $this->project->title,
            'project_report_type'     => $this->projectReportTypeMapping->projectReportType->title,
            'project_report_title'    => $this->title,
            'project_report_revision' => $this->isOriginalRevision() ? trans('projectReport.original') : $this->revision,
            'recipientLocale'         => $locale,
        ];
        
        if( ! Verifier::isApproved($this) )
        {
            $viewData['toRoute'] = $this->getRoute();
        }

        return $viewData;
    }

    public function getOnApprovedNotifyList()
    {
        $users = array();

        $submitter = User::find($this->submitted_by);

        $users[] = $submitter;

        $approvedVerifiers = Verifier::getAssignedVerifierRecords($this);

        foreach($approvedVerifiers as $approvedVerifier)
        {
            $users[] = $approvedVerifier->verifier;
        }

        return $users;
    }

    public function getOnRejectedNotifyList()
    {
        $users = array();

        $submitter = User::find($this->submitted_by);

        $users[] = $submitter;

        $approvedVerifiers = Verifier::getAssignedVerifierRecords($this);

        foreach($approvedVerifiers as $approvedVerifier)
        {
            $users[] = $approvedVerifier->verifier;
        }

        return $users;
    }

    public function getOnApprovedFunction()
    {

    }

    public function getOnRejectedFunction()
    {

    }

    public function onReview()
    {
        if(Verifier::isApproved($this))
        {
            $this->status        = self::STATUS_COMPLETED;
            $this->approved_date = Carbon::now();
            $this->save();

            ProjectReportColumn::persistVerifiedValues($this);
        }

        if(Verifier::isRejected($this))
        {
            $this->status = self::STATUS_DRAFT;
            $this->save();
        }
    }

    public function getEmailSubject($locale)
    {
        return trans('projectReport.projectReportNotification', [], 'messages', $locale);
    }

    public function getSubmitterId()
    {
        return $this->submitted_by;
    }

    public function getModuleName()
    {
        return trans('modules.projectReport');
    }

    public function getObjectDescription()
    {
        return $this->title . '(' . trans('projectReport.revision') . ' ' . $this->getRevisionText() . ')';
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getAllReportsInLine($order = 'ASC', $includeSelf = true)
    {
        $query = self::where('project_id' , $this->project->id)
                    ->where('project_report_type_mapping_id', $this->project_report_type_mapping_id);

        if(!$includeSelf)
        {
            $query->where('id', '!=', $this->id);
        }

        return $query->orderBy('revision', $order)->get();
    }
}