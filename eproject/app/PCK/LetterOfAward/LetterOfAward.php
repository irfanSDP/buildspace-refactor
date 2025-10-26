<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;
use PCK\Verifier\Verifiable;
use PCK\LetterOfAward\LetterOfAwardUserPermission;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class LetterOfAward extends Model implements Verifiable {
    
    protected $table = 'letter_of_awards';

    const DEFAULT_NAME = 'Default';

    const EDITABLE = 1;
    const PENDING_FOR_VERIFICATION = 2;
    const APPROVED = 4;

    const LETTER_OF_AWARD_MODULE_NAME = 'Letter of Award';

    public function project()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }

    public function contractDetail()
    {
        return $this->hasOne('PCK\LetterOfAward\LetterOfAwardContractDetail');
    }

    public function signatory()
    {
        return $this->hasOne('PCK\LetterOfAward\LetterOfAwardSignatory');
    }

    public function clauses()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardClause')->orderBy('sequence_number', 'asc');
    }

    public function printSetting()
    {
        return $this->hasOne('PCK\LetterOfAward\LetterOfAwardPrintSetting');
    }

    public function logs()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardLog')->orderBy('updated_at', 'asc');
    }

    public function submitter()
    {
        return $this->belongsTo('PCK\Users\User', 'submitted_for_approval_by');
    }

    public function canUserEditLetterOfAward(User $user) {
        if($this->status != self::EDITABLE) {
            return false;
        }

        $record = $this->project->letterOfAwardUserPermissions->filter(function($object) use ($user) {
            return (($object->user_id == $user->id) && ($object->module_identifier == LetterOfAwardUserPermission::EDITOR));
        })->first();

        return !is_null($record);
    }

    public function canUserCommentLetterOfAward(User $user) {
        if($this->status != self::EDITABLE) {
            return false;
        }

        $record = $this->project->letterOfAwardUserPermissions->filter(function($object) use ($user) {
            return (($object->user_id == $user->id) && ($object->module_identifier == LetterOfAwardUserPermission::REVIEWER));
        })->first();

        return !is_null($record);
    }

    public function canUserSubmitForApproval(User $user) {
        if($this->status != self::EDITABLE) {
            return false;
        }

        $userPermissions = $user->getLetterOfAwardUserPermissionsByProject($this->project, true);

        if(!$userPermissions->isEmpty()) {
            $permissioModules = array_column($userPermissions->toArray(), 'module_identifier');
            
            return in_array(LetterOfAwardUserPermission::REVIEWER, $permissioModules);
        }

        return false;
    }

    public function canApproveOrRejectApproval(User $user) {
        if($this->status != self::PENDING_FOR_VERIFICATION) {
            return false;
        }

        return Verifier::isCurrentVerifier($user, $this);
    }

    public function getOnApprovedView() {
        return 'letterOfAward.approved';
    }

    public function getOnRejectedView() {
        return 'letterOfAward.rejected';
    }

    public function getOnPendingView() {
        return 'letterOfAward.pending';
    }

    public function getRoute() {
        return route('letterOfAward.index', [$this->project->id]);
    }

    public function getViewData($locale) {
        return [
			'senderName'			=> \Confide::user()->name,
			'project_title' 		=> $this->project->title,
            'toRoute'				=> $this->getRoute(),
            'recipientLocale'       => $locale,
		];
    }

    public function getOnApprovedNotifyList() {
        $allUsers = [];
        $records = LetterOfAwardUserPermission::distinct()->select('user_id')->where('project_id', $this->project->id)->get();
        
        foreach($records as $record) {
            array_push($allUsers, $record->user);
        }

        return $allUsers;
    }

    public function getOnRejectedNotifyList()
    {
        $users = array();

        $user = User::find($this->submitted_for_approval_by);

        if( $user->stillInSameAssignedCompany($this->project, $this->created_at) )
        {
            $users[] = $user;
        }

        return $users;
    }

    public function getOnApprovedFunction() {
        return function() {
			$this->status = LetterOfAward::APPROVED;
			$this->save();
		};
    }

    public function getOnRejectedFunction() {
        return function() {
			$this->status = LetterOfAward::EDITABLE;
			$this->save();
		};
    }

    public function onReview() {

    }

    public function getEmailSubject($locale)
    {
        return trans('letterOfAward.letterOfAwardNotification', [], 'messages', $locale);
    }

    public function getSubmitterId()
    {
        return $this->submitted_for_approval_by;
    }

    public function getModuleName()
    {
        return trans('modules.letterOfAward');
    }
}

