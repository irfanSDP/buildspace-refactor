<?php namespace PCK\Projects;

use PCK\Contracts\Contract;

trait ContractTypesProjectDetailRelationTrait {

    public function pam2006Detail()
    {
        return $this->hasOne('PCK\ProjectDetails\PAM2006ProjectDetail');
    }

    public function contractIs($type)
    {
        return $this->contract->type == $type;
    }

    public function indonesiaCivilContractInformation()
    {
        return $this->hasOne('PCK\ProjectDetails\IndonesiaCivilContractInformation');
    }

    public function postContractInformation()
    {
        if( $this->contractIs(Contract::TYPE_PAM2006) )
        {
            return $this->pam2006Detail();
        }

        if( $this->contractIs(Contract::TYPE_INDONESIA_CIVIL_CONTRACT) )
        {
            return $this->indonesiaCivilContractInformation();
        }

        return null;
    }

}