<?php namespace PCK\Verifier;

use Illuminate\Database\Eloquent\Model;

abstract class BaseVerifier extends Model {

    public function getProject(){}

    public function getRoute(){}

    public function getObjectDescription(){}

    public function getModuleName(){}

}