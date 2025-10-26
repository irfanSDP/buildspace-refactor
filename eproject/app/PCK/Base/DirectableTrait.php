<?php namespace PCK\Base;

trait DirectableTrait {

    public function directedTo()
    {
        return $this->morphMany('PCK\DirectedTo\DirectedTo', 'object');
    }

}