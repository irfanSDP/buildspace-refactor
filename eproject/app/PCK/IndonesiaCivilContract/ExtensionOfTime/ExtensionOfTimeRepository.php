<?php namespace PCK\IndonesiaCivilContract\ExtensionOfTime;

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use PCK\ClauseItems\AttachedClauseItem;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;
use PCK\ProjectModulePermission\ProjectModulePermission;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Base\BaseModuleRepository;

class ExtensionOfTimeRepository extends BaseModuleRepository {

    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function all(Project $project)
    {
        return self::getAll($project);
    }

    public static function getAll(Project $project)
    {
        $user = \Confide::user();

        $records = ExtensionOfTime::where('project_id', '=', $project->id)
            ->orderBy('id', 'desc')
            ->get();

        return $records->filter(function($item) use ($user)
        {
            return $item->isVisible($user);
        });
    }

    public static function getCount(Project $project)
    {
        return self::getAll($project)->count();
    }

    public function findWithMessages(Project $project, $eotId)
    {
        return ExtensionOfTime::with('attachedClauses', 'attachments.file')
            ->where('id', '=', $eotId)
            ->where('project_id', '=', $project->id)
            ->first();
    }

    public function add(Project $project, array $inputs)
    {
        $user = \Confide::user();

        $eot = new ExtensionOfTime(array(
            'project_id'                     => $project->id,
            'user_id'                        => $user->id,
            'indonesia_civil_contract_ai_id' => ( $inputs['indonesia_civil_contract_ai_id'] > 0 ) ? $inputs['indonesia_civil_contract_ai_id'] : null,
            'reference'                      => $inputs['reference'],
            'subject'                        => $inputs['subject'],
            'details'                        => $inputs['details'],
            'status'                         => ExtensionOfTime::STATUS_DRAFT,
            'days'                           => $inputs['days'],
        ));

        if( isset( $inputs['issue'] ) )
        {
            $eot->status     = ExtensionOfTime::STATUS_SUBMITTED;
            $eot->created_at = Carbon::now();
        }

        $eot->save();

        $eot->earlyWarnings()->sync($inputs['early_warnings'] ?? array());

        AttachedClauseItem::syncClauses($eot, $inputs['selected_clauses'] ?? array());

        $this->saveAttachments($eot, $inputs);

        if( $eot->status == ExtensionOfTime::STATUS_SUBMITTED )
        {
            $users = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EXTENSION_OF_TIME)->toArray();

            $this->sendEmailNotificationByUsers($project, $eot, $users, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
            $this->sendSystemNotificationByUsers($project, $eot, $users, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
        }

        return $eot;
    }

    public function update(User $user, ExtensionOfTime $eot, array $inputs)
    {
        $eot->reference                      = $inputs['reference'];
        $eot->subject                        = $inputs['subject'];
        $eot->details                        = $inputs['details'];
        $eot->days                           = $inputs['days'];
        $eot->indonesia_civil_contract_ai_id = ( $inputs['indonesia_civil_contract_ai_id'] > 0 ) ? $inputs['indonesia_civil_contract_ai_id'] : null;
        $eot->user_id                        = $user->id;

        if( isset( $inputs['issue'] ) )
        {
            $eot->status     = ExtensionOfTime::STATUS_SUBMITTED;
            $eot->created_at = Carbon::now();
        }

        $eot->save();

        $eot->earlyWarnings()->sync($inputs['early_warnings'] ?? array());

        AttachedClauseItem::syncClauses($eot, $inputs['selected_clauses'] ?? array());
        $this->saveAttachments($eot, $inputs);

        if( $eot->status == ExtensionOfTime::STATUS_SUBMITTED )
        {
            $users = ProjectModulePermission::getAssigned($eot->project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_LOSS_AND_EXPENSES)->toArray();

            $this->sendEmailNotificationByUsers($eot->project, $eot, $users, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
            $this->sendSystemNotificationByUsers($eot->project, $eot, $users, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
        }

        return $eot;
    }

    public function delete(ExtensionOfTime $eot)
    {
        if( $eot->status != ExtensionOfTime::STATUS_DRAFT )
        {
            throw new \InvalidArgumentException('Only EOTs that are in Draft can be deleted.');
        }

        return $eot->delete();
    }

    public function submitResponse(ExtensionOfTime $extensionOfTime, User $user, $inputs)
    {
        $response = new ContractualClaimResponse(array(
            'user_id'  => $user->id,
            'subject'  => $inputs['subject'],
            'content'  => $inputs['content'],
            'sequence' => $extensionOfTime->getNextResponseSequenceNumber(),
            'type'     => $inputs['type'],
        ));

        if( $inputs['type'] == ContractualClaimResponse::TYPE_GRANT )
        {
            $extensionOfTime->status  = ExtensionOfTime::STATUS_GRANTED;
            $response->proposed_value = $inputs['proposed_value'];
        }
        elseif( $inputs['type'] == ContractualClaimResponse::TYPE_AGREE_ON_PROPOSED_VALUE )
        {
            $extensionOfTime->status = ExtensionOfTime::STATUS_APPROVED;
        }
        elseif( $inputs['type'] == ContractualClaimResponse::TYPE_REJECT_PROPOSED_VALUE )
        {
            $extensionOfTime->status = ExtensionOfTime::STATUS_REJECTED;
        }

        if( $extensionOfTime->isDirty() ) $extensionOfTime->save();

        $response->object()->associate($extensionOfTime);

        $success = $response->save();

        $this->saveAttachments($response, $inputs);

        $this->sendResponseNotifications($extensionOfTime->project, $extensionOfTime);

        return $success;
    }

    protected function sendResponseNotifications(Project $project, ExtensionOfTime $eot)
    {
        if( $eot->contractorsTurn() )
        {
            $recipients = $project->getSelectedContractor()->getActiveUsers()->toArray() ?? array();
        }
        else
        {
            $recipients = ProjectModulePermission::getAssigned($project, ProjectModulePermission::MODULE_ID_INDONESIA_CIVIL_CONTRACT_EXTENSION_OF_TIME)->toArray();
        }

        $this->sendEmailNotificationByUsers($project, $eot, $recipients, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
        $this->sendSystemNotificationByUsers($project, $eot, $recipients, 'extension_of_time', 'indonesiaCivilContract.extensionOfTime.show');
    }

}