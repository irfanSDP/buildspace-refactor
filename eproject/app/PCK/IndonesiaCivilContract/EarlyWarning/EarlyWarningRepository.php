<?php namespace PCK\IndonesiaCivilContract\EarlyWarning;

use Illuminate\Events\Dispatcher;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;

class EarlyWarningRepository extends BaseModuleRepository {

    protected $events;

    public function __construct(ArchitectInstruction $ai, Dispatcher $events)
    {
        $this->events = $events;
    }

    public function all(Project $project)
    {
        return self::getAll($project);
    }

    public static function getAll(Project $project)
    {
        return EarlyWarning::where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public static function getCount(Project $project)
    {
        return self::getAll($project)->count();
    }

    public function add(Project $project, array $inputs)
    {
        $user         = \Confide::user();
        $earlyWarning = EarlyWarning::create(array(
            'project_id'        => $project->id,
            'user_id'           => $user->id,
            'reference'         => $inputs['reference'],
            'impact'            => $inputs['impact'],
            'commencement_date' => $inputs['commencement_date'],
        ));

        $this->saveAttachments($earlyWarning, $inputs);

        $users = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EARLY_WARNING)->toArray();

        $this->sendEmailNotificationByUsers($project, $earlyWarning, $users, 'early_warning', 'indonesiaCivilContract.earlyWarning.show');
        $this->sendSystemNotificationByUsers($project, $earlyWarning, $users, 'early_warning', 'indonesiaCivilContract.earlyWarning.show');

        return $earlyWarning;
    }

}