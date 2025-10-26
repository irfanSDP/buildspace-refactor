<?php namespace PCK\Conversations;

trait PurposeOfIssueTypeTrait {

    public function getPurposeOfIssuedAttribute($value)
    {
        return self::getPurposeOfIssuedText($value);
    }

    public static function getPurposeOfIssuedText($value)
    {
        switch($value)
        {
            case PurposeOfIssueType::FOR_COMMENT:
                $text = PurposeOfIssueType::FOR_COMMENT_TEXT;
                break;

            case PurposeOfIssueType::REQUEST_FOR_INFORMATION:
                $text = PurposeOfIssueType::REQUEST_FOR_INFORMATION_TEXT;
                break;

            case PurposeOfIssueType::FOR_INFORMATION_ONLY:
                $text = PurposeOfIssueType::FOR_INFORMATION_ONLY_TEXT;
                break;

            default:
                $text = PurposeOfIssueType::NONE_TEXT;
                break;
        }

        return $text;
    }

    public static function getSelectDropDownListing()
    {
        $data[ PurposeOfIssueType::NONE ]                    = PurposeOfIssueType::NONE_TEXT;
        $data[ PurposeOfIssueType::FOR_COMMENT ]             = PurposeOfIssueType::FOR_COMMENT_TEXT;
        $data[ PurposeOfIssueType::REQUEST_FOR_INFORMATION ] = PurposeOfIssueType::REQUEST_FOR_INFORMATION_TEXT;
        $data[ PurposeOfIssueType::FOR_INFORMATION_ONLY ]    = PurposeOfIssueType::FOR_INFORMATION_ONLY_TEXT;

        return $data;
    }

} 