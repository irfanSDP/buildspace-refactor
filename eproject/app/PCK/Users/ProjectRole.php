<?php namespace PCK\Users;

use PCK\Companies\Company;
use PCK\CompanyProject\CompanyProject;
use PCK\Projects\Project;

trait ProjectRole {

    /**
     * Returns the user's company (direct or foster) that is assigned to the project.
     * The user's direct company takes precedence.
     * Returns false if none of the user's companies are assigned to the project.
     *
     * @param Project $project
     *
     * @param null    $timestamp
     *
     * @return null|Company
     */
    public function getAssignedCompany(Project $project, $timestamp = null)
    {
        if($this->hasCompany())
        {
            if( $timestamp && $company = $this->getHistoricAssignedCompany($project, $timestamp) )
            {
                return $company;
            }

            if( $company = $this->getDirectAssignedCompany($project) )
            {
                return $company;
            }

            if( $company = $this->getTenderingCompany($project) )
            {
                return $company;
            }
        }

        return null;
    }

    protected function getDirectAssignedCompany(Project $project)
    {
        $relation = CompanyProject::where('project_id', '=', $project->id)
            ->where('company_id', '=', $this->company_id)
            ->first();

        if( $relation ) return $this->company;

        return $this->getDirectAssignedFosterCompany($project);
    }

    protected function getDirectAssignedFosterCompany(Project $project)
    {
        $relations = CompanyProject::where('project_id', '=', $project->id)->get();

        foreach($relations as $relation)
        {
            if( ! in_array($relation->company_id, $this->getFosterCompanyIds()) ) continue;

            return Company::find($relation->company_id);
        }

        return null;
    }

    protected function getTenderingCompany(Project $project)
    {
        if( ! $project->latestTender ) return null;

        $selectedContractors = array();

        if( $info = $project->latestTender->getTenderStageInformation() ) $selectedContractors = $info->selectedContractors;

        foreach($selectedContractors as $contractor)
        {
            if( $this->company->id == $contractor->id ) return $this->company;

            if( in_array($contractor->id, $this->getFosterCompanyIds()) ) return Company::find($contractor->id);
        }

        return null;
    }

    protected function getHistoricAssignedCompany(Project $project, $timestamp)
    {
        if( $company = $this->getHistoricDirectAssignedCompany($project, $timestamp) ) return $company;

        if( $company = $this->getHistoricDirectAssignedFosterCompany($project, $timestamp) ) return $company;

        return null;
    }

    protected function getHistoricDirectAssignedCompany(Project $project, $timestamp)
    {
        $company = $this->getHistoricCompany($timestamp);

        if( ! $company ) return null;

        if( $project->isCompanyAssignedAt($company, $timestamp) ) return $company;

        return null;
    }

    protected function getHistoricDirectAssignedFosterCompany(Project $project, $timestamp)
    {
        $fosterCompanies = $this->getHistoricFosterCompanies($timestamp);

        foreach($fosterCompanies as $company)
        {
            if( $project->isCompanyAssignedAt($company, $timestamp) ) return $company;
        }

        return null;
    }

    /**
     * Returns true if the user is in the same assigned company now and then.
     *
     * @param Project $project
     * @param         $timestamp
     *
     * @return bool
     */
    public function stillInSameAssignedCompany(Project $project, $timestamp)
    {
        $currentCompany  = $this->getAssignedCompany($project);
        $historicCompany = $this->getAssignedCompany($project, $timestamp);

        return ( $historicCompany->id ?? null ) == ( $currentCompany->id ?? null );
    }

    /**
     * Returns true if the user's company has the specified role in the project.
     *
     * @param Project   $project
     * @param int|array $role
     *
     * @return bool
     */
    public function hasCompanyProjectRole(Project $project, $role)
    {
        // The SuperAdmin should not be assigned to any project.
        if( $this->isSuperAdmin() ) return false;

        if( ! $assignedCompany = $this->getAssignedCompany($project) ) return false;

        return $assignedCompany->hasProjectRole($project, $role);
    }

    /**
     * Returns true if user is assigned to project.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function assignedToProject(Project $project)
    {
        $result = \DB::table('contract_group_project_users')
            ->where('project_id', '=', $project->id)
            ->where('user_id', '=', $this->id)
            ->first();

        return ( $result ? true : false );
    }

    /**
     * Returns true if user is an editor in the project.
     *
     * @param Project $project
     *
     * @return mixed
     */
    public function isEditor(Project $project)
    {
        $result = \DB::table('contract_group_project_users')
            ->where('project_id', '=', $project->id)
            ->where('user_id', '=', $this->id)
            ->first();

        return $result->is_contract_group_project_owner ?? false;
    }

}