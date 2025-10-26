<?php

class EProjectAccountCodeSetting extends BaseEProjectAccountCodeSetting
{
    const STATUS_OPEN = 1;
    const STATUS_PENDING_FOR_APPROVAL = 2;
    const STATUS_APPROVED = 4;

    public function isApproved()
    {
        return $this->status == self::STATUS_APPROVED;
    }
}

?>