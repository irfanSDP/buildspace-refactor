<?php namespace PCK\Base;

trait VerifiableTrait {

    public function verifiedBy()
    {
        return $this->morphMany('PCK\Verifier\Verifier', 'object');
    }

}