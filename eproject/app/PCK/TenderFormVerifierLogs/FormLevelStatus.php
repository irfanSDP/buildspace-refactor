<?php namespace PCK\TenderFormVerifierLogs;

interface FormLevelStatus {

    const IN_PROGRESS      = 1;
    const IN_PROGRESS_TEXT = 'In Progress';

    const SUBMISSION      = 2;
    const SUBMISSION_TEXT = 'Submission';

    const NEED_VALIDATION      = 4;
    const NEED_VALIDATION_TEXT = 'Need Validation';

    const USER_VERIFICATION_IN_PROGRESS      = 8;
    const USER_VERIFICATION_IN_PROGRESS_TEXT = 'Request Verification';

    const USER_VERIFICATION_REJECTED      = 16;
    const USER_VERIFICATION_REJECTED_TEXT = 'Rejected';

    const USER_VERIFICATION_CONFIRMED      = 32;
    const USER_VERIFICATION_CONFIRMED_TEXT = 'Approved';

    const EXTEND_DATE_VALIDATION_IN_PROGRESS      = 64;
    const EXTEND_DATE_VALIDATION_IN_PROGRESS_TEXT = 'Request Extend Deadline';

    const EXTEND_DATE_VALIDATION_ALLOWED = 128;

    const REASSIGNED      = 256;
    const REASSIGNED_TEXT = 'Reassigned';

}