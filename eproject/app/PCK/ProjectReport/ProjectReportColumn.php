<?php namespace PCK\ProjectReport;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Helpers\NumberHelper;
use PCK\Projects\Project;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\Project as ProjectStructure;

class ProjectReportColumn extends Model
{
    protected $table = 'project_report_columns';

    const COLUMN_CUSTOM                         = 1;    // Text
    const COLUMN_GROUP                          = 2;
    const COLUMN_SYSTEM_COMPANY_NAME            = 3;
    const COLUMN_SYSTEM_PROJECT_TITLE           = 4;
    const COLUMN_SYSTEM_PROJECT_STATUS          = 5;
    const COLUMN_SYSTEM_PROJECT_CONTRACT_SUM    = 6;
    const COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE = 7;
    const COLUMN_SYSTEM_PROJECT_BILL_TOTAL      = 8;
    const COLUMN_SYSTEM_PROJECT_VO_TOTAL        = 9;

    const COLUMN_DATE                           = 10;
    const COLUMN_NUMBER                         = 11;
    const COLUMN_WORK_CATEGORY                  = 12;
    CONST COLUMN_SUBSIDIARY                     = 13;

    CONST COLUMN_PROJECT_PROGRESS               = 14;

    const COLUMN_NAME_PREFIX = 'columns';

    const PROJECT_PROGRESS_AHEAD = 'ahead';
    const PROJECT_PROGRESS_ONTRACK = 'ontrack';
    const PROJECT_PROGRESS_DELAY = 'delay';
    const PROJECT_PROGRESS_DELAY_2 = 'delay2';
    const PROJECT_PROGRESS_COMPLETED = 'completed';

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model)
        {
            if($model->isColumnGroup() && $model->children()->count() > 0)
            {
                foreach($model->children as $subColumn)
                {
                    $subColumn->delete();
                }
            }
        });

        static::deleted(function(self $model)
        {
            self::updatePriority($model);
        });
    }

    public function projectReport()
    {
        return $this->belongsTo(ProjectReport::class, 'project_report_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function parentsList()
    {
        $parents = array();
        $parent = $this->parent;
        while(! is_null($parent))
        {
            $parents[] = $parent;
            $parent = $parent->parent;
        }

        return array_reverse($parents);
    }

    public static function scopeWithProjectReportQueryForCharts($query, $data)
    {
        $mappingId = $data['mapping_id'];
        $subsidiaryIds = $data['subsidiaries'];

        // Sanitize the $subsidiaryIds array to ensure it contains only valid integers
        $subsidiaryIds = array_filter($subsidiaryIds, function($value) {
            return is_numeric($value) && (int)$value == $value;
        });

        $query->whereHas('projectReport', function($query) use ($mappingId, $subsidiaryIds) {
            $query->where('project_report_type_mapping_id', $mappingId)
                ->whereNull('deleted_at')
                ->whereNotNull('approved_date')
                ->where('status', ProjectReport::STATUS_COMPLETED)
                ->whereHas('project', function($query) use ($subsidiaryIds) {
                    $query->whereIn('subsidiary_id', $subsidiaryIds)
                        ->whereNull('deleted_at');
                });
        });
    }

    public static function scopeWithProjectReportQuery($query, $data)
    {
        $mappingId = $data['mapping_id'];

        $query->whereHas('projectReport', function($query) use ($mappingId) {
            $query->where('project_report_type_mapping_id', $mappingId)
                ->whereNull('deleted_at')
                ->whereNotNull('approved_date')
                ->where('status', ProjectReport::STATUS_COMPLETED)
                ->whereHas('project', function($query) {
                    $query->whereNull('deleted_at');
                });
        });
    }

    public static function isCustomColumnList()
    {
        return array(
            self::COLUMN_CUSTOM,
            self::COLUMN_DATE,
            self::COLUMN_NUMBER,
            self::COLUMN_PROJECT_PROGRESS,
        );
    }

    public function isCustomColumn()
    {
        return in_array($this->type, $this->isCustomColumnList());
    }

    public static function isSystemColumnList()
    {
        return array(
            self::COLUMN_SYSTEM_COMPANY_NAME,
            self::COLUMN_SYSTEM_PROJECT_TITLE,
            self::COLUMN_SYSTEM_PROJECT_STATUS,
            self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM,
            self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE,
            self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL,
            self::COLUMN_SYSTEM_PROJECT_VO_TOTAL,
            self::COLUMN_WORK_CATEGORY,
            self::COLUMN_SUBSIDIARY
        );
    }

    public function isSystemColumn()
    {
        return in_array($this->type, $this->isSystemColumnList());
    }

    public function isNumberColumnList()
    {
        return array(
            self::COLUMN_NUMBER,
        );
    }

    public function isNumberColumn()
    {
        return in_array($this->type, $this->isNumberColumnList());
    }

    public static function isSingleEntryColumnList()
    {
        return array(
            self::COLUMN_CUSTOM,
            self::COLUMN_DATE,
            self::COLUMN_NUMBER,
        );
    }

    public function isSingleEntryColumn()
    {
        return in_array($this->type, $this->isSingleEntryColumnList());
    }

    public function isColumnGroup()
    {
        return $this->type == self::COLUMN_GROUP;
    }

    public function isSubColumn()
    {
        return !is_null($this->parent_id);
    }

    public function getUniqueColumnNameAttribute()
    {
        $groupStr = $this->isSubColumn() ? "group_{$this->parent->priority}_" : '';

        return self::COLUMN_NAME_PREFIX  . "_{$groupStr}{$this->type}_{$this->priority}_{$this->depth}";
    }

    public static function generateUniqueIdentifier($identifier)
    {
        return self::COLUMN_NAME_PREFIX . '_' . $identifier;
    }

    public static function getMaxDepth(ProjectReport $projectReport)
    {
        $record = self::where('project_report_id', $projectReport->id)->orderBy('depth', 'DESC')->first();

        return is_null($record) ? 0 : $record->depth + 1;
    }

    public static function getSingleEntryCount($referenceId, $mappingId, $projectId)
    {
        return self::where('reference_id', $referenceId)
            ->whereHas('projectReport', function($query) use ($mappingId, $projectId) {
                $query->where('project_report_type_mapping_id', $mappingId)
                    ->where('project_id', $projectId)
                    ->whereNotNull('approved_date')
                    ->whereNull('deleted_at');
            })
            ->whereNotNull('content')
            ->count();
    }

    public static function getProjectProgressLabel($projectProgress)
    {
        switch($projectProgress)
        {
            case self::PROJECT_PROGRESS_AHEAD:
                $label = trans('projectReport.projectProgressAhead');
                break;

            case self::PROJECT_PROGRESS_ONTRACK:
                $label = trans('projectReport.projectProgressOntrack');
                break;

            case self::PROJECT_PROGRESS_DELAY:
                $label = trans('projectReport.projectProgressDelay');
                break;

            case self::PROJECT_PROGRESS_DELAY_2:
                $label = trans('projectReport.projectProgressDelay2');
                break;

            case self::PROJECT_PROGRESS_COMPLETED:
                $label = trans('projectReport.projectProgressCompleted');
                break;

            default:
                throw new \Exception('Invalid status');
        }

        return $label;
    }

    public function getColumnTitle()
    {
        $descriptions = [
            self::COLUMN_CUSTOM                         => $this->title,
            self::COLUMN_GROUP                          => $this->title,
            self::COLUMN_SYSTEM_COMPANY_NAME            => trans('companies.name'),
            self::COLUMN_SYSTEM_PROJECT_TITLE           => trans('projects.projectTitle'),
            self::COLUMN_SYSTEM_PROJECT_STATUS          => trans('projects.projectStatus'),
            self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM    => trans('projects.contractSum'),
            self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE => trans('finance.totalWorkDone'),
            self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL      => trans('finance.billTotal'),
            self::COLUMN_SYSTEM_PROJECT_VO_TOTAL        => trans('finance.totalVoAmount'),
            self::COLUMN_DATE                           => $this->title,
            self::COLUMN_NUMBER                         => $this->title,
            self::COLUMN_WORK_CATEGORY                  => trans('projectReport.columnWorkCategory'),
            self::COLUMN_SUBSIDIARY                     => trans('projectReport.columnSubsidiary'),
            self::COLUMN_PROJECT_PROGRESS               => trans('projectReport.columnProjectProgress'),
        ];

        return $descriptions[$this->type];
    }

    public function getColumnTypeLabel()
    {
        $descriptions = [
            self::COLUMN_CUSTOM                         => trans('projectReport.customColumn'),
            self::COLUMN_GROUP                          => trans('projectReport.columnGroup'),
            self::COLUMN_SYSTEM_COMPANY_NAME            => trans('companies.name'),
            self::COLUMN_SYSTEM_PROJECT_TITLE           => trans('projects.projectTitle'),
            self::COLUMN_SYSTEM_PROJECT_STATUS          => trans('projects.projectStatus'),
            self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM    => trans('projects.contractSum'),
            self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE => trans('finance.totalWorkDone'),
            self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL      => trans('finance.billTotal'),
            self::COLUMN_SYSTEM_PROJECT_VO_TOTAL        => trans('finance.totalVoAmount'),
            self::COLUMN_DATE                           => trans('projectReport.columnDate'),
            self::COLUMN_NUMBER                         => trans('projectReport.columnNumber'),
            self::COLUMN_WORK_CATEGORY                  => trans('projectReport.columnWorkCategory'),
            self::COLUMN_SUBSIDIARY                     => trans('projectReport.columnSubsidiary'),
            self::COLUMN_PROJECT_PROGRESS               => trans('projectReport.columnProjectProgress'),
        ];

        return $descriptions[$this->type];
    }

    public function getColumnContent($options = array('number_comma_separated' => false))
    {
        if($this->projectReport->isTemplate()) return null;

        $content = '';
        $thousandSeparator = $options['number_comma_separated'] ? ',' : '';

        switch($this->type)
        {
            case self::COLUMN_CUSTOM: // Custom column (Text)
                $content = $this->content;
                break;

            case self::COLUMN_SYSTEM_COMPANY_NAME:
                $buCompany = $this->projectReport->project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();
                $content   = $buCompany->name;
                break;

            case self::COLUMN_SYSTEM_PROJECT_TITLE: // Project title
                $content = $this->projectReport->project->title;
                break;

            case self::COLUMN_SYSTEM_PROJECT_STATUS:
                $content = $this->projectReport->isCompleted() ? Project::getStatusText(trim($this->content)) : Project::getStatusText($this->projectReport->project->status_id);
                break;

            case self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM:  // Contract sum
                if($this->projectReport->isCompleted())
                {
                    $content = NumberHelper::formatNumber($this->content, 2, $thousandSeparator);
                }
                else
                {
                    $content = self::getContractSum($this, $thousandSeparator);
                }
                break;

            case self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE:   // Total work done
                if($this->projectReport->isCompleted())
                {
                    $content = NumberHelper::formatNumber($this->content, 2, $thousandSeparator);
                }
                else
                {
                    $content = self::getTotalWorkDone($this, $thousandSeparator);
                }
                break;

            case self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL:    // Bill total
                if($this->projectReport->isCompleted())
                {
                    $content = NumberHelper::formatNumber($this->content, 2, $thousandSeparator);
                }
                else
                {
                    $content = self::getBillTotal($this, $thousandSeparator);
                }
                break;

            case self::COLUMN_SYSTEM_PROJECT_VO_TOTAL:
                if($this->projectReport->isCompleted())
                {
                    $content = NumberHelper::formatNumber($this->content, 2, $thousandSeparator);
                }
                else
                {
                    $content = self::getVoTotal($this, $thousandSeparator);
                }
                break;

            case self::COLUMN_DATE: // Date
                if (! empty($this->content)) {
                    $content = $this->content;
                }
                break;

            case self::COLUMN_NUMBER:   // Number
                if (! empty($this->content)) {
                    $content = NumberHelper::formatNumber($this->content, 2, $thousandSeparator);
                }
                break;

            case self::COLUMN_PROJECT_PROGRESS: // Project progress
                if (! empty($this->content)) {
                    switch ($this->content) {
                        case 'ahead':
                            $content = trans('projectReport.projectProgressAhead');
                            break;

                        case 'ontrack':
                            $content = trans('projectReport.projectProgressOntrack');
                            break;

                        case 'delay':
                            $content = trans('projectReport.projectProgressDelay');
                            break;

                        case 'delay2':
                            $content = trans('projectReport.projectProgressDelay2');
                            break;

                        case 'completed':
                            $content = trans('projectReport.projectProgressCompleted');
                            break;

                        default:
                            $content = $this->content;
                    }
                }
                break;

            case self::COLUMN_WORK_CATEGORY:    // Work category
                $content = $this->projectReport->project->workCategory->name;
                break;

            case self::COLUMN_SUBSIDIARY:   // Subsidiary
                $subsidiary = $this->projectReport->project->subsidiary;
                $rootSubsidiary = $subsidiary->getTopParentSubsidiary('root');
                $content = $rootSubsidiary->name;
                break;

            default:
                $content = null;
        }

        return trim($content);
    }

    public static function getNextFreePriority(ProjectReport $projectReport, $parentId = null)
    {
        $query = self::where('project_report_id', $projectReport->id);

        if(is_null($parentId))
        {
            $query->whereNull('parent_id');
        }
        else
        {
            $query->where('parent_id', $parentId);
        }

        $record = $query->orderBy('priority', 'DESC')->first();

        if(is_null($record)) return 0;

        return ($record->priority + 1);
    }

    public static function updatePriority(self $removedRecord)
    {
        $parentIdQuery = $removedRecord->isSubColumn() ? ' AND parent_id = ' . $removedRecord->parent_id . ' ' : '';

        $query = DB::raw('UPDATE ' . (new self)->getTable() . ' SET priority = (priority - 1) WHERE project_report_id = ' . $removedRecord->projectReport->id . ' ' . $parentIdQuery . ' AND priority > ' . $removedRecord->priority . ';');

        DB::update($query);
    }

    public static function swap(self $draggedColumn, self $swappedColumn)
    {
        $draggedColumnPriority = $draggedColumn->priority;
        $swappedColumnPriority = $swappedColumn->priority;

        $draggedColumn->priority = $swappedColumnPriority;
        $draggedColumn->save();

        $swappedColumn->priority = $draggedColumnPriority;
        $swappedColumn->save();
    }

    public static function newRecordFromSource(ProjectReport $projectReport, self $sourceColumn, $cloneRef, $parentId = null)
    {
        $content = null;

        if ($sourceColumn->isCustomColumn()) {
            $mapping = $projectReport->projectReportTypeMapping;

            if ($mapping) {
                if ($mapping->latest_rev) { // Latest revision type
                    $content = $sourceColumn->content;
                } else {    // Multiple record type
                    if ($sourceColumn->single_entry) {
                        if (self::getSingleEntryCount($sourceColumn->reference_id, $mapping->id, $projectReport->project_id) > 0) {
                            $content = $sourceColumn->content;
                        }
                    }
                }
            }
        }

        $newRecord                    = new self;
        $newRecord->project_report_id = $projectReport->id;
        $newRecord->title             = $sourceColumn->title;
        $newRecord->content           = $content;
        $newRecord->type              = $sourceColumn->type;
        $newRecord->single_entry      = $sourceColumn->single_entry;
        $newRecord->hidden            = $sourceColumn->hidden;
        $newRecord->priority          = self::getNextFreePriority($projectReport, $parentId);
        $newRecord->depth             = $sourceColumn->depth;
        $newRecord->save();

        if ($cloneRef) {  // Clone reference ID
            $newRecord->reference_id = $sourceColumn->reference_id;
        } else {    // Don't clone, but generate new reference ID
            $newRecord->reference_id = $newRecord->id;
        }
        $newRecord->save();

        $newRecord = self::find($newRecord->id);

        if($sourceColumn->isColumnGroup() && $sourceColumn->children()->count() > 0)
        {
            foreach($sourceColumn->children()->orderBy('priority', 'ASC')->get() as $subColumn)
            {
                $newSubColumn            = self::newRecordFromSource($projectReport, $subColumn, $cloneRef, $newRecord->id);
                $newSubColumn->parent_id = $newRecord->id;
                $newSubColumn->save();
            }

            $newRecord->load('children');
        }

        return self::find($newRecord->id);
    }

    public static function clone(ProjectReport $source, ProjectReport $destination, $cloneRef=true)
    {
        $sourceParentColumns = self::where('project_report_id', $source->id)->whereNull('parent_id')->orderBy('priority', 'ASC')->get();

        foreach($sourceParentColumns as $sourceColumn)
        {
            self::newRecordFromSource($destination, $sourceColumn, $cloneRef);
        }

        return true;
    }

    public static function persistVerifiedValues(ProjectReport $projectReport)
    {
        $types = [
            self::COLUMN_SYSTEM_PROJECT_STATUS,
            self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM,
            self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE,
            self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL,
            self::COLUMN_SYSTEM_PROJECT_VO_TOTAL,
        ];

        $thousandSeparator = '';

        $columns = self::where('project_report_id', $projectReport->id)
                    ->whereIn('type', $types)
                    ->orderBy('priority', 'ASC')
                    ->get();

        foreach($columns as $column)
        {
            $content = null;

            switch($column->type)
            {
                case self::COLUMN_SYSTEM_PROJECT_STATUS:
                    $content = $column->projectReport->project->status_id;
                    break;
                case self::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE:
                    $content = self::getTotalWorkDone($column, $thousandSeparator);
                    break;
                case self::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM:
                    $content = self::getContractSum($column, $thousandSeparator);
                    break;
                case self::COLUMN_SYSTEM_PROJECT_BILL_TOTAL:
                    $content = self::getBillTotal($column, $thousandSeparator);
                    break;
                case self::COLUMN_SYSTEM_PROJECT_VO_TOTAL:
                    $content = self::getVoTotal($column, $thousandSeparator);
                    break;
                default:
                    // Do nothing
            }

            $column->content = trim($content);
            $column->save();
        }
    }

    protected static function getProjectStructure($column) {
        return $column->projectReport->project->getBsProjectMainInformation()->projectStructure;
    }

    protected static function getFinalApprovedClaimCertificate($bsProjectStructure) {
        return $bsProjectStructure->getApprovedClaimCertificates()->first();
    }

    protected static function calculateTotal($bsProjectStructure, $key, $thousandSeparator) {
        $finalApprovedClaimCertificate = self::getFinalApprovedClaimCertificate($bsProjectStructure);

        if (! is_null($finalApprovedClaimCertificate)) {
            $claimCertificateInfo = ClaimCertificate::getClaimCertInfo([$finalApprovedClaimCertificate->id]);
            return NumberHelper::formatNumber($claimCertificateInfo[$finalApprovedClaimCertificate->id][$key], 2, $thousandSeparator);
        }

        return null;
    }

    public static function getBillTotal($column, $thousandSeparator = ',') {
        $bsProjectStructure = self::getProjectStructure($column);

        $total = self::calculateTotal($bsProjectStructure, 'billTotal', $thousandSeparator);
        if (is_null($total)) {
            $overallTotal = ProjectStructure::getOverallTotalByProjects([$bsProjectStructure->id]);
            if (! empty($overallTotal)) {
                if (isset($overallTotal[$bsProjectStructure->id])) {
                    $total = NumberHelper::formatNumber($overallTotal[$bsProjectStructure->id], 2, $thousandSeparator);
                }
            }
        }
        return $total;
    }

    public static function getContractSum($column, $thousandSeparator = ',') {
        $bsProjectStructure = self::getProjectStructure($column);
        $total = self::calculateTotal($bsProjectStructure, 'contractSum', $thousandSeparator);
        return $total !== null ? $total : self::getBillTotal($column, $thousandSeparator);
    }

    public static function getTotalWorkDone($column, $thousandSeparator = ',') {
        $bsProjectStructure = self::getProjectStructure($column);
        return self::calculateTotal($bsProjectStructure, 'totalWorkDone', $thousandSeparator);
    }

    public static function getVoTotal($column, $thousandSeparator = ',') {
        $bsProjectStructure = self::getProjectStructure($column);
        return self::calculateTotal($bsProjectStructure, 'voTotal', $thousandSeparator);
    }
}