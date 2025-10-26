<?php namespace PCK\Statuses;

interface FormStatus {

    const STATUS_DRAFT                = 1;
    const STATUS_SUBMITTED            = 2;
    const STATUS_PROCESSING           = 32;
    const STATUS_PENDING_VERIFICATION = 4;
    const STATUS_COMPLETED            = 8;
    const STATUS_REJECTED             = 16;
    const STATUS_EXPIRED              = 64;
}