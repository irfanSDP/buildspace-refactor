<?php namespace PCK\Forms;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Projects\Project;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\CompanyProject\CompanyProject;

class AssignCompaniesForm extends FormValidator {

    private $project;

    protected $rules = [];

    public function validate($formData)
    {
        parent::validate($formData);

        $this->customValidation($formData);
    }

    public function setParameters(Project $project)
    {
        $this->project = $project;
    }

    protected function customValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! $this->companiesAssignedToSingleRole($formData['group_id']) )
        {
            $errorMessages->add('unique_company', 'A Company can only be assigned to a maximum of one role.');
        }

        if( $errorMessage = $this->checkExistingVerifiers($this->project, $formData) )
        {
            $errorMessages->add('existing_verifiers', $errorMessage);
        }

        if( $this->companyAssignmentUpdated($formData) && $this->hasEvaluation() )
        {
            $errorMessages->add('has_evaluation', trans('vendorManagement.cannotReassignCompanies:vpeInProgress'));
        }

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

    /**
     * Checks that a company is only assigned to a maximum of one role.
     * Returns false if a company is being assigned to two or more roles.
     *
     * @param $groupIds
     *
     * @return bool
     */
    private function companiesAssignedToSingleRole($groupIds)
    {
        $companyIds = array();

        foreach($groupIds as $company)
        {
            if( in_array($company, $companyIds) )
            {
                return false;
            }

            if( ! empty( $company ) )
            {
                $companyIds[] = $company;
            }
        }

        return true;
    }

    /**
     * Check if there are any verifiers for latest tender.
     * Returns an error message if there is an error.
     *
     * @param Project $project
     * @param         $inputs
     *
     * @return null|string
     */
    private function checkExistingVerifiers(Project $project, $inputs)
    {
        $allVerifiers = $project->latestTender->openTenderVerifiers->merge($project->latestTender->technicalEvaluationVerifiers);

        foreach($allVerifiers as $verifier)
        {
            // There could be more than one foster company, so we check for all of them.
            $allCompanies = new Collection(array( $verifier->getAssignedCompany($project) ));
            $allCompanies->merge($verifier->fosterCompanies);

            foreach($allCompanies as $verifierCompany)
            {
                $verifierContractGroup = $verifierCompany->getContractGroup($project);

                if (array_key_exists($verifierContractGroup->id, $inputs['group_id'])) {
                    $groupIds = $inputs['group_id'][$verifierContractGroup->id];

                    if (! is_array($groupIds)) {
                        $groupIds = (array) $groupIds;
                    }

                    if (! in_array($verifierCompany->id, $groupIds)) {
                        return 'There are still pending verifiers in the company (' . $verifierCompany->name . ') as ' . $verifierContractGroup->groupName;
                    }
                }
            }
        }

        return null;
    }

    protected function hasEvaluation()
    {
        // vm.Todo moduleEnabled check
        return VendorPerformanceEvaluation::where('project_id', '=', $this->project->id)
            ->where('status_id', '=', VendorPerformanceEvaluation::STATUS_IN_PROGRESS)
            ->exists();
    }

    protected function companyAssignmentUpdated($formData)
    {
        $currentAssignedCompanies = CompanyProject::where('project_id', '=', $this->project->id)
            ->lists('company_id', 'contract_group_id');

        return $formData['group_id'] != $currentAssignedCompanies;
    }

}