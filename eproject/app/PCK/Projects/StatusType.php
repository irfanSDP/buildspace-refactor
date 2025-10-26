<?php namespace PCK\Projects;

interface StatusType {

    const STATUS_TYPE_DESIGN      = 1;
    const STATUS_TYPE_DESIGN_TEXT = 'Design';

    const STATUS_TYPE_POST_CONTRACT      = 4;
    const STATUS_TYPE_POST_CONTRACT_TEXT = 'Post Contract';

    const STATUS_TYPE_COMPLETED      = 8;
    const STATUS_TYPE_COMPLETED_TEXT = 'Completed';

    const STATUS_TYPE_RECOMMENDATION_OF_TENDERER      = 16;
    const STATUS_TYPE_RECOMMENDATION_OF_TENDERER_TEXT = 'Rec. of Tenderer';

    const STATUS_TYPE_LIST_OF_TENDERER      = 32;
    const STATUS_TYPE_LIST_OF_TENDERER_TEXT = 'List of Tenderer';

    const STATUS_TYPE_CALLING_TENDER      = 64;
    const STATUS_TYPE_CALLING_TENDER_TEXT = 'Calling Tender';

    const STATUS_TYPE_CLOSED_TENDER      = 128;
    const STATUS_TYPE_CLOSED_TENDER_TEXT = 'Closed Tender';

    const STATUS_TYPE_E_BIDDING     = 256;
    const STATUS_TYPE_E_BIDDING_TEXT = 'E-Bidding';

}