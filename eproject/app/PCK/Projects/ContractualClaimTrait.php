<?php namespace PCK\Projects;

trait ContractualClaimTrait {

    // Pam 2006
    public function architectInstructions()
    {
        return $this->hasMany('PCK\ArchitectInstructions\ArchitectInstruction')->orderBy('id', 'desc');
    }

    public function extensionOfTimes()
    {
        return $this->hasMany('PCK\ExtensionOfTimes\ExtensionOfTime')->orderBy('id', 'desc');
    }

    public function additionalExpenses()
    {
        return $this->hasMany('PCK\AdditionalExpenses\AdditionalExpense')->orderBy('id', 'desc');
    }

    public function lossOrAndExpenses()
    {
        return $this->hasMany('PCK\LossOrAndExpenses\LossOrAndExpense')->orderBy('id', 'desc');
    }

    public function engineerInstructions()
    {
        return $this->hasMany('PCK\EngineerInstructions\EngineerInstruction')->orderBy('id', 'desc');
    }

    public function interimClaims()
    {
        return $this->hasMany('PCK\InterimClaims\InterimClaim')->orderBy('id', 'desc');
    }

    // Indonesia Civil Contract
    public function indonesiaCivilContractArchitectInstructions()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction')->orderBy('id', 'desc');
    }

    public function indonesiaCivilContractExtensionOfTimes()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime')->orderBy('id', 'desc');
    }

    public function indonesiaCivilContractLossOrAndExpenses()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\LossAndExpense\LossAndExpense')->orderBy('id', 'desc');
    }

    public function indonesiaCivilContractEarlyWarnings()
    {
        return $this->hasMany('PCK\IndonesiaCivilContract\EarlyWarning\EarlyWarning')->orderBy('id', 'desc');
    }

}