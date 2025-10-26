<?php 

namespace PCK\EBiddings;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\EBiddingCommittees\EBiddingCommittee;
use PCK\Helpers\DateTime;
use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;

class EBidding extends Model implements Verifiable{

    use BiddingSessionTrait;

    const SET_BUDGET_NO = 0;
    const SET_BUDGET_YES = 1;

    const STATUS_REJECT = 3;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_OPEN = 1;
    const STATUS_APPROVED = 4;

    const E_BIDDING_MODULE_NAME = 'E-Bidding';

	protected $table = 'e_biddings';

    protected $fillable = [
        'project_id',
        'status',
        'preview_start_time',
        'reminder_preview_start_time',
        'bidding_start_time',
        'reminder_bidding_start_time',
        'duration_hours',
        'duration_minutes',
        'duration_seconds',
        'start_overtime',           // Minutes
        'start_overtime_seconds',   // Seconds
        'overtime_period',  // Minutes
        'overtime_seconds', // Seconds
        'e_bidding_mode_id',
        'set_budget',
        'show_budget_to_bidder',
        'budget',
        'bid_decrement_percent',
        'decrement_percent',
        'bid_decrement_value',
        'decrement_value',
        'min_bid_amount_diff',
        'enable_custom_bid_value',
        'enable_no_tie_bid',
        'enable_zones',
        'hide_other_bidder_info',
        'created_by',
        'duration_extended',    // Minutes
        'extended_seconds',     // Seconds
        'lowest_tender_amount',
        'lowest_bid_amount',    // Null until bidding ends
        'bidding_end_time',     // Null until bidding ends
    ];

    public function tender()
	{
		return $this->belongsTo('PCK\Tenders\Tender');
	}
    
    public function project()
	{
		return $this->belongsTo('PCK\Projects\Project','project_id');
	}
    
    /*public function project_id()
    {
        return $this->belongsTo('PCK\Projects\Project');
    }*/

    public function created_by()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function eBiddingMode()
    {
        return $this->belongsTo('PCK\EBiddings\EBiddingMode', 'e_bidding_mode_id');
    }

    public function eBiddingZones()
    {
        return $this->hasMany('PCK\EBiddings\EBiddingZone', 'e_bidding_id');
    }

    public static function setBudget()
    {
       $setBudget[self::SET_BUDGET_NO] = 'NO';
       $setBudget[self::SET_BUDGET_YES] = 'YES';

       return $setBudget;
    }

    public static function getStatusText($status)
    {
        $statusText = '';

        switch($status)
        {
            case self::STATUS_REJECT:
                $statusText = "Rejected";
                break;
            case self::STATUS_PENDING_FOR_APPROVAL:
                $statusText = "Pending For Approval";
                break;
            case self::STATUS_OPEN:
                $statusText = "Open";
                break;
            case self::STATUS_APPROVED:
                $statusText = "Approved";
                break;
            default: break;
        }

        return $statusText;
    }

	public function getOnApprovedView()
    {
        return 'eBidding.approved';
    }

    public function getOnRejectedView()
    {
        return 'eBidding.rejected';
    }

    public function getOnPendingView()
    {
        return 'eBidding.pending';
    }

    public function getModuleName()
    {
        return trans('modules.eBidding');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function getObjectDescription()
    {
        return $this->instruction;
    }

    public function getRoute(){
        return route('projects.e_bidding.index', [$this->project->id]);
    }

    public function getOnApprovedNotifyList()
    {
        $committees = EBiddingCommittee::where('project_id',$this->project->id)->where('is_committee',true)->get();

        $ebidding = EBidding::where('project_id',$this->project->id)->first();
        $objectId = $ebidding->id;
        $verifiers = Verifier::where('object_id',$objectId)->where('object_type', get_class($ebidding))->get();

        $users = new Collection();
        foreach($committees as $record)
        {
            $users->add($record->user);
        }

        foreach($verifiers as $record)
        {
            $users->add($record->verifier);
        }

        return $users;
    }

    public static function getPendingEBidding(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingEBiddings = [];
        $proceed          = false;

        $records = Verifier::where('verifier_id', $user->id)->where('object_type', EBidding::class)->get();

        foreach($records as $record)
        {
            $ebidding = EBidding::find($record->object_id);
            $proceed  = $includeFutureTasks ? Verifier::isAVerifierInline($user, $ebidding) : Verifier::isCurrentVerifier($user, $ebidding);

            if($ebidding && $proceed)
            {
                $previousVerifierRecord = Verifier::getPreviousVerifierRecord($ebidding);
                $now                    = Carbon::now();
                $then                   = $previousVerifierRecord ? Carbon::parse($previousVerifierRecord->verified_at) : Carbon::parse($ebidding->updated_at);
                $project                = $ebidding->project;
                $routeString            = 'projects.e_bidding.index';

                array_push($pendingEBiddings, [
                    'project_reference'        => $project->reference,
                    'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                    'project_id'               => $project->id,
                    'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                    'company_id'               => $project->business_unit_id,
                    'project_title'            => $project->title,
                    'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                    'module'                   => EBidding::E_BIDDING_MODULE_NAME,
                    'days_pending'             => $then->diffInDays($now),
                    'is_future_task'           => !(Verifier::isCurrentVerifier($user, $ebidding)),
                    'route'                    => route($routeString, [$project->id]),
                ]);
            }
        }

        return $pendingEBiddings;
    }


    public function getOnRejectedNotifyList()
    {
        $committees = EBiddingCommittee::where('project_id',$this->project->id)->where('is_committee',true)->get();

        $ebidding = EBidding::where('project_id',$this->project->id)->first();
        $objectId = $ebidding->id;
        $verifiers = Verifier::where('object_id',$objectId)->where('object_type', get_class($ebidding))->get();

        $users = new Collection();
        foreach($committees as $record)
        {
            $users->add($record->user);
        }

        foreach($verifiers as $record)
        {
            $users->add($record->verifier);
        }

        return $users;
    }

    public function getOnApprovedFunction(){}

    public function getOnRejectedFunction(){}

    public function isLocked()
    {
        return in_array($this->status, [self:: STATUS_PENDING_FOR_APPROVAL, self::STATUS_APPROVED]);
    }

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }

    public function onReview()
    {
        if(Verifier::isApproved($this))
        {
            $this->status = self::STATUS_APPROVED;
            $this->save();
        }

        if(Verifier::isRejected($this))
        {
            $this->status = self::STATUS_OPEN;
            $this->save();
        }
    }

    public function getEmailSubject($locale)
    {
        return trans('eBidding.eBiddingNotification', [], 'messages', $locale);
    }

    public function getViewData($locale)
    {
        $viewData = [
            'senderName'			=> \Confide::user()->name,
			'project_title' 		=> $this->project->title,
            'recipientLocale'       => $locale,
        ];
        
        if( ! Verifier::isApproved($this) )
        {
            $viewData['toRoute'] = $this->getRoute();
        }

        return $viewData;
    }

    public function getSubmitterId(){}

    public function biddingPreviewTime()
    {
        return ! empty($this->preview_start_time) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->preview_start_time) : null;
    }

    public function biddingStartTime()
    {
        return ! empty($this->bidding_start_time) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->bidding_start_time) : null;
    }

    public function biddingEndTime($extended = true)
    {
        $biddingEnd = null;
        if ($this->biddingStartTime()) {
            $biddingEnd = clone $this->biddingStartTime(); // Clone here to avoid modifying original biddingStart
            $biddingEnd->addHours($this->duration_hours)
                ->addMinutes($this->duration_minutes)
                ->addSeconds($this->duration_seconds);

            if ($extended) {
                $biddingEnd->addMinutes($this->duration_extended)
                    ->addSeconds($this->extended_seconds);
            }
        }

        return $biddingEnd;
    }

    public function biddingStartOvertime()
    {
        $minutes = $this->start_overtime;
        $seconds = $this->start_overtime_seconds;
        $total = ($minutes * 60) + $seconds;

        // Normalize back to M:S
        $minutes = floor($total / 60);
        $seconds = $total % 60;

        return array(
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total' => $total,  // Total in seconds
        );
    }

    public function biddingOvertimePeriod()
    {
        $minutes = $this->overtime_period;
        $seconds = $this->overtime_seconds;
        $total = ($minutes * 60) + $seconds;

        // Normalize back to M:S
        $minutes = floor($total / 60);
        $seconds = $total % 60;

        return array(
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total' => $total,  // Total in seconds
        );
    }

    public function biddingExtendedTime()
    {
        $minutes = $this->duration_extended;
        $seconds = $this->extended_seconds;
        $total = ($minutes * 60) + $seconds;

        // Normalize back to M:S
        $minutes = floor($total / 60);
        $seconds = $total % 60;

        return array(
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total' => $total,  // Total in seconds
        );
    }

    public function biddingDuration($extended = true)
    {
        $hours = $this->duration_hours;
        $minutes = $this->duration_minutes;
        $seconds = $this->duration_seconds;

        if ($extended) {
            $durationExtended = $this->biddingExtendedTime();
            $minutes += $durationExtended['minutes'];
            $seconds += $durationExtended['seconds'];
        }

        // Convert everything to total seconds first
        $total = ($hours * 3600) + ($minutes * 60) + $seconds;

        // Normalise back to H:M:S
        $hours = floor($total / 3600);
        $minutes = floor(($total % 3600) / 60);
        $seconds = $total % 60;

        return array(
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'total' => $total, // Total in seconds
        );
    }

    public function biddingDurationText($fullText = false, $extended = true)
    {
        $duration = $this->biddingDuration($extended);

        return DateTime::formatDuration($duration['hours'], $duration['minutes'], $duration['seconds'], $fullText);
    }

    public function biddingPreviewStartTimeText()
    {
        $previewStart = $this->biddingPreviewTime();
        if (empty($previewStart)) {
            return '';
        }

        return $previewStart->format('j F Y g:i:s A');
    }

    public function biddingStartTimeText()
    {
        $biddingStart = $this->biddingStartTime();
        if (empty($biddingStart)) {
            return '';
        }

        return $biddingStart->format('j F Y g:i:s A');
    }

    public function biddingEndTimeText()
    {
        $biddingEnd = $this->biddingEndTime();
        if (empty($biddingEnd)) {
            return '';
        }

        return $biddingEnd->format('j F Y g:i:s A');
    }

    public function biddingHasOvertime()
    {
        $overtimeStart = $this->biddingStartOvertime();
        $overtimePeriod = $this->biddingOvertimePeriod();

        return ($overtimeStart['total'] > 0) && ($overtimePeriod['total'] > 0);
    }

    public function biddingStartOvertimeText($fullText = false)
    {
        $overtime = $this->biddingStartOvertime();

        return DateTime::formatDuration(0, $overtime['minutes'], $overtime['seconds'], $fullText);
    }

    public function biddingOvertimePeriodText($fullText = false)
    {
        $overtimePeriod = $this->biddingOvertimePeriod();

        return DateTime::formatDuration(0, $overtimePeriod['minutes'], $overtimePeriod['seconds'], $fullText);
    }

    public function biddingExtendedTimeText($fullText = false)
    {
        $extended = $this->biddingExtendedTime();

        return DateTime::formatDuration(0, $extended['minutes'], $extended['seconds'], $fullText);
    }

    public function biddingSessionStatus()
    {
        $now = Carbon::now();
        $previewStart = $this->biddingPreviewTime();
        $biddingStart = $this->biddingStartTime();
        $biddingEnd = $this->biddingEndTime();

        // Determine the session status
        if ($now->lt($previewStart)) {
            $sessionStatusClass = 'text-primary';
            $sessionStatusText = trans('eBiddingConsole.statusUpcoming');
        } elseif ($now->between($previewStart, $biddingStart)) {
            $sessionStatusClass = 'text-primary';
            $sessionStatusText = trans('eBiddingConsole.statusPreview');
        } elseif ($now->between($biddingStart, $biddingEnd)) {
            $sessionStatusClass = 'text-success';
            $sessionStatusText = trans('eBiddingConsole.statusLive');
        } elseif ($now->gt($biddingEnd)) {
            $sessionStatusClass = 'text-danger';
            $sessionStatusText = trans('eBiddingConsole.statusEnded');
        } else {
            $sessionStatusClass = 'text-muted';
            $sessionStatusText = '';
        }

        return [
            'class' => $sessionStatusClass,
            'text' => $sessionStatusText,
        ];
    }

    public function biddingSessionStatusText()
    {
        $status = $this->biddingSessionStatus();
        return $status['text'];
    }
}