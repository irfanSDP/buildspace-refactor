<?php namespace PCK\FormBuilder\Elements;

interface Elementable
{
    public static function getClassIdentifier();

    public static function createNewElement($inputs);

    public  function updateElement($inputs);

    public function getElementDetails();

    public function deleteRelatedModels();
}

