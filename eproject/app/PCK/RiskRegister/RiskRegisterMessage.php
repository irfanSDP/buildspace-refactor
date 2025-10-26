<?php namespace PCK\RiskRegister;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\Base\DirectableTrait;
use PCK\Base\ModuleAttachmentTrait;
use PCK\DocumentControlObject\DocumentControlMessageObject;
use PCK\Users\User;
use PCK\Verifier\Verifier;

class RiskRegisterMessage extends DocumentControlMessageObject {

    const TYPE_RISK    = 1;
    const TYPE_COMMENT = 2;

    const RATING_LOW         = 1;
    const RATING_LOW_TEXT    = 'Low';
    const RATING_MEDIUM      = 2;
    const RATING_MEDIUM_TEXT = 'Medium';
    const RATING_HIGH        = 4;
    const RATING_HIGH_TEXT   = 'High';

    const STATUS_OPEN        = 1;
    const STATUS_OPEN_TEXT   = 'Open';
    const STATUS_CLOSED      = 2;
    const STATUS_CLOSED_TEXT = 'Closed';

    use ModuleAttachmentTrait, DirectableTrait, SoftDeletingTrait, VerifierProcessTrait;

    public function documentControlObject()
    {
        return $this->riskRegister();
    }

    public function riskRegister()
    {
        return $this->belongsTo('PCK\RiskRegister\RiskRegister', 'document_control_object_id');
    }

    public static function getArbitraryRatings()
    {
        return array(
            self::RATING_LOW    => self::RATING_LOW_TEXT,
            self::RATING_MEDIUM => self::RATING_MEDIUM_TEXT,
            self::RATING_HIGH   => self::RATING_HIGH_TEXT,
        );
    }

    public static function getStatusList()
    {
        return array(
            self::STATUS_OPEN   => self::STATUS_OPEN_TEXT,
            self::STATUS_CLOSED => self::STATUS_CLOSED_TEXT,
        );
    }

    public static function getRatingText($rating)
    {
        switch($rating)
        {
            case self::RATING_LOW:
                return self::RATING_LOW_TEXT;
            case self::RATING_MEDIUM:
                return self::RATING_MEDIUM_TEXT;
            case self::RATING_HIGH:
                return self::RATING_HIGH_TEXT;
            default:
                throw new \Exception('Invalid rating');
        }
    }

    public static function getStatusText($status)
    {
        switch($status)
        {
            case self::STATUS_OPEN:
                return self::STATUS_OPEN_TEXT;
            case self::STATUS_CLOSED:
                return self::STATUS_CLOSED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function isRisk()
    {
        return $this->type == self::TYPE_RISK;
    }

    public function isComment()
    {
        return $this->type == self::TYPE_COMMENT;
    }

    public function canReviseRejected(User $user)
    {
        if( ! $this->isRisk() ) return false;
        if( ! Verifier::isRejected($this) ) return false;
        if( $user->id != $this->composer->id ) return false;

        return true;
    }

    public function canUpdateComment(User $user)
    {
        if( ! $this->isComment() ) return false;
        if( ! Verifier::isRejected($this) ) return false;
        if( $user->id != $this->composer->id ) return false;

        return true;
    }

}