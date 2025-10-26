<?php namespace PCK\Projects;

use PCK\RequestForInformation\RequestForInformationMessage;
use PCK\RiskRegister\RiskRegisterMessage;

trait DocumentControlTrait {

    public function requestsForInformation()
    {
        return $this->hasMany('PCK\RequestForInformation\RequestForInformation', 'project_id')
            ->where('message_type', '=', get_class(new RequestForInformationMessage))
            ->orderBy('reference_number', 'desc');
    }

    public function getVisibleRequestsForInformation()
    {
        return $this->requestsForInformation
            ->reject(function($rfi)
            {
                return ( ! $rfi->getLastVisibleRequest() );
            });
    }

    public function riskRegisterRisks()
    {
        return $this->hasMany('PCK\RiskRegister\RiskRegister', 'project_id')
            ->where('message_type', '=', get_class(new RiskRegisterMessage))
            ->orderBy('reference_number', 'desc');
    }

    public function getVisibleRiskRegisterRisks()
    {
        return $this->riskRegisterRisks
            ->reject(function($risk)
            {
                return ( ! $risk->isVisible() );
            });
    }

    public function requestsForInspection()
    {
        return $this->hasMany('PCK\RequestForInspection\RequestForInspection')
            ->orderBy('reference_number', 'desc');
    }

}