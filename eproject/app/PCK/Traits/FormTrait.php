<?php namespace PCK\Traits;

use PCK\Statuses\FormStatus;

trait FormTrait
{
    public static function getStatusText($status)
    {
        switch($status)
        {
            case FormStatus::STATUS_DRAFT:
                $text = trans('forms.draft');
                break;
            case FormStatus::STATUS_SUBMITTED:
                $text = trans('forms.submitted');
                break;
            case FormStatus::STATUS_PROCESSING:
                $text = trans('forms.processing');
                break;
            case FormStatus::STATUS_PENDING_VERIFICATION:
                $text = trans('forms.pendingForApproval');
                break;
            case FormStatus::STATUS_COMPLETED:
                $text = trans('forms.completed');
                break;
            case FormStatus::STATUS_REJECTED:
                $text = trans('forms.rejected');
                break;
            case FormStatus::STATUS_EXPIRED:
                $text = trans('forms.expired');
                break;
            default:
                throw new \Exception("Invalid type");
        }

        return $text;
    }

    public function isDraft($statusField = 'status_id')
    {
        return $this->{$statusField} == FormStatus::STATUS_DRAFT;
    }

    public function isSubmitted($statusField = 'status_id')
    {
        return $this->{$statusField} == FormStatus::STATUS_SUBMITTED;
    }

    public function isPendingVerification($statusField = 'status_id')
    {
        return $this->{$statusField} == FormStatus::STATUS_PENDING_VERIFICATION;
    }

    public function isCompleted($statusField = 'status_id')
    {
        return $this->{$statusField} == FormStatus::STATUS_COMPLETED;
    }

    public function isRejected($statusField = 'status_id')
    {
        return $this->{$statusField} == FormStatus::STATUS_REJECTED;
    }
}
