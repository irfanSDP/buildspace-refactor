<?php namespace PCK\RequestForVariation;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use PCK\Users\User;

class RequestForVariationActionLog extends Model {

    protected $table = 'request_for_variation_action_logs';

    const ACTION_TYPE_SUBMITTED_NEW_RFV = 1;

    const ACTION_TYPE_FILLED_OMISSION_ADDITION = 2;

    const ACTION_TYPE_APPROVED_OMISSION_ADDITION = 3;

    const ACTION_TYPE_REJECTED_OMISSION_ADDITION = 4;

    const ACTION_TYPE_SUBMITTED_FOR_APPROVAL = 5;

    const ACTION_TYPE_RFV_APPROVED = 6;

    const ACTION_TYPE_RFV_REJECTED = 7;

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public function requestForVariation()
    {
        return $this->belongsTo('PCK\RequestForVariation\RequestForVariation');
    }

    public function getFormattedActionLog()
    {
        $project = $this->requestForVariation->project;

        $creatorTxt = $this->user->name;

        $creatorTxt .= ' <span style="display:inline;padding: .2em .6em .3em;font-size:75%;font-weight:700;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius: .25em;" class="bg-color-teal">';
        if($this->user->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::PROJECT_OWNER))
        {
            $creatorTxt .= $project->subsidiary->name;
        }
        else
        {
            $creatorTxt .= ($company = $this->user->getAssignedCompany($project)) ? $company->name : "-";
        }
        $creatorTxt .= "</span>";

        switch($this->action_type)
        {
            case self::ACTION_TYPE_SUBMITTED_NEW_RFV:
                return [
                    'formattedLog'      => 'RFV created by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format'))
                ];
            case self::ACTION_TYPE_FILLED_OMISSION_ADDITION:
                return [
                    'formattedLog'      => 'Estimate Cost submitted by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format'))
                ];
            case self::ACTION_TYPE_APPROVED_OMISSION_ADDITION:
                return [
                    'formattedLog'      => 'Estimate Cost verified by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format')),
                    'remarks'           => $this->remarks
                ];
            case self::ACTION_TYPE_REJECTED_OMISSION_ADDITION:
                return [
                    'formattedLog'      => 'Estimate Cost rejected by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format')),
                    'remarks'           => $this->remarks
                ];
            case self::ACTION_TYPE_SUBMITTED_FOR_APPROVAL:
                $verifier = ($this->verifier) ? User::find($this->verifier) : null;

                $verifierTxt = ($verifier) ? '(Assigned '.$verifier->name.' as verifier)' : null;
                return [
                    'formattedLog'      => 'Pending for Approval '.$verifierTxt.' created by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format')),
                    'remarks'           => $this->remarks
                ];
            case self::ACTION_TYPE_RFV_APPROVED:
                return [
                    'formattedLog'      => 'Approved by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format')),
                    'remarks'           => $this->remarks
                ];
            case self::ACTION_TYPE_RFV_REJECTED:
                return [
                    'formattedLog'      => 'Rejected by ' . $creatorTxt . ' at ',
                    'formattedDateTime' => Carbon::parse($this->updated_at)->format(\Config::get('dates.full_format')),
                    'remarks'           => $this->remarks
                ];
            default:
                // nothing here
        }
    }
}


