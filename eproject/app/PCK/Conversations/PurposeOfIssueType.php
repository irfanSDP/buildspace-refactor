<?php namespace PCK\Conversations;

interface PurposeOfIssueType {

    const NONE      = -1;
    const NONE_TEXT = 'None';

    const FOR_COMMENT      = 1;
    const FOR_COMMENT_TEXT = 'For Comment';

    const REQUEST_FOR_INFORMATION      = 2;
    const REQUEST_FOR_INFORMATION_TEXT = 'Request For Information';

    const FOR_INFORMATION_ONLY      = 4;
    const FOR_INFORMATION_ONLY_TEXT = 'For Information Only';

}