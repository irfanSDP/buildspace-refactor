<?php namespace PCK\Projects;

use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\Buildspace\Region as BsRegion;
use PCK\Buildspace\Currency as BsCurrency;
use PCK\Buildspace\SubRegion as BsSubRegion;
use PCK\Buildspace\WorkCategory as BsWorkCategory;
use PCK\Buildspace\ProjectMainInformation as BsProjectMainInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use PCK\Companies\Company;
use PCK\Helpers\ModelOperations;
use PCK\Users\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait BsProjectTrait {

    public function getBsProjectMainInformation()
    {
        return BsProjectMainInformation::where('eproject_origin_id', '=', $this->id)->first();
    }

    /**
     * Creates a new project in the BuildSpace database with the project details.
     */
    protected function syncProjectToBuildspace()
    {
        $country = $this->Country;
        $state   = $this->State;

        $region = BsRegion::whereRaw("LOWER(country) = '" . strtolower(trim($country->country)) . "'")
            ->first();

        if( ! $region )
        {
            $region                = new BsRegion();
            $region->iso           = $country->iso;
            $region->iso3          = $country->iso3;
            $region->fips          = $country->fips;
            $region->country       = trim($country->country);
            $region->continent     = $country->continent;
            $region->currency_code = $country->currency_code;
            $region->currency_name = $country->currency_name;
            $region->phone_prefix  = $country->phone_prefix;
            $region->postal_code   = $country->postal_code;
            $region->languages     = $country->languages;
            $region->geonameid     = $country->geonameid;

            $region->save();
        }

        $subRegion = BsSubRegion::whereRaw("LOWER(name) = '" . mb_strtolower(trim($state->name)) . "'")
            ->where('region_id', $region->id)
            ->first();

        if( ! $subRegion )
        {
            $subRegion            = new BsSubRegion();
            $subRegion->name      = trim($state->name);
            $subRegion->timezone  = $state->timezone;
            $subRegion->region_id = $region->id;

            $subRegion->save();
        }

        $currency = BsCurrency::whereRaw("LOWER(currency_code) = '" . strtolower(trim($region->currency_code)) . "'")
            ->first();

        if( ! $currency )
        {
            $currency                = new BsCurrency();
            $currency->currency_name = $country->currency_name;
            $currency->currency_code = $country->currency_code;

            $currency->save();
        }

        $workCategory = BsWorkCategory::whereRaw("LOWER(name) = '" . strtolower(trim($this->workCategory->name)) . "'")
            ->whereNull('deleted_at')
            ->first();

        if( ! $workCategory )
        {
            $workCategory       = new BsWorkCategory();
            $workCategory->name = trim($this->workCategory->name);

            $workCategory->save();
        }

        $bsProject = new BsProjectMainInformation();

        $bsProject->eproject_origin_id = $this->id;
        $bsProject->title              = $this->title;
        $bsProject->site_address       = $this->address;
        $bsProject->client             = '-';
        $bsProject->work_category_id   = $workCategory->id;
        $bsProject->start_date         = date('Y-m-d');
        $bsProject->region_id          = $region->id;
        $bsProject->subregion_id       = $subRegion->id;
        $bsProject->currency_id        = $currency->id;

        $bsProject->save();
    }

    /**
     * Deletes the project from the BuildSpace database.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function deleteFromBuildspace()
    {
        \Log::info('Deleting BuildSpace project with eproject id: ' . $this->id);

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        try
        {
            $response = $client->post('eproject_api/deleteProject', array(
                'form_params' => array(
                    'eproject_project_id' => $this->id
                )
            ));

            \Log::info('Deleted BuildSpace project with eproject id: ' . $this->id);

            $response = json_decode($response->getBody());

            if( ! $response->success )
            {
                \Log::error("Unable to delete from BuildSpace Project \"{$this->title}\" (id: {$this->id}) => {$response->errorMsg}");
            }

            return $response->success;
        }
        catch(ServerException $e)
        {
            \Log::info("Unable to delete BuildSpace project with eproject id: {$this->id} => {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Removes all project permission for the project.
     *
     * @param $projectStatus
     */
    public function revokeAllBsProjectPermissions($projectStatus)
    {
        $bsProjectStructureId = $this->getBsProjectMainInformation()->project_structure_id;

        $bsProjectUserPermissions = BsProjectUserPermission::where('project_structure_id', '=', $bsProjectStructureId)
            ->where('project_status', '=', $projectStatus)
            ->get();

        ModelOperations::deleteWithTrigger($bsProjectUserPermissions);
    }

    /**
     * Removes the users permission to the project.
     *
     * @param User $user
     * @param      $projectStatus
     *
     * @return bool
     */
    public function revokeBsProjectPermission(User $user, $projectStatus)
    {
        if( ! ( $bsUser = $user->getBsUser() ) ) return false;

        $bsProjectStructureId = $this->getBsProjectMainInformation()->project_structure_id;

        $bsProjectUserPermissions = BsProjectUserPermission::where('project_structure_id', '=', $bsProjectStructureId)
            ->where('project_status', '=', $projectStatus)
            ->where('user_id', '=', $bsUser->id)
            ->get();

        return ModelOperations::deleteWithTrigger($bsProjectUserPermissions);
    }

    /**
     * Grants project permission to all users of a company.
     *
     * @param Company $company
     * @param         $projectStatus
     */
    public function grantBsProjectPermissionToCompanyUsers(Company $company, $projectStatus)
    {
        foreach($company->getActiveUsers() as $user)
        {
            $this->grantBsProjectPermissionToUser($user, $projectStatus);
        }
    }

    /**
     * Grants project permission to a user.
     *
     * @param User $user
     * @param      $projectStatus
     *
     * @return bool
     */
    public function grantBsProjectPermissionToUser(User $user, $projectStatus)
    {
        if( ! ( $bsUser = $user->getBsUser() ) ) return false;

        $currentUser   = \Confide::user();
        $currentBsUser = $currentUser->getBsUser();

        $bsProjectStructureId = $this->getBsProjectMainInformation()->project_structure_id;

        $bsProjectUserPermission = BsProjectUserPermission::where('project_structure_id', '=', $bsProjectStructureId)
            ->where('user_id', '=', $bsUser->id)
            ->where('project_status', '=', $projectStatus)
            ->first();

        if( ! $bsProjectUserPermission )
        {
            $bsProjectUserPermission                       = new BsProjectUserPermission;
            $bsProjectUserPermission->project_structure_id = $bsProjectStructureId;
            $bsProjectUserPermission->user_id              = $bsUser->id;
            $bsProjectUserPermission->project_status       = $projectStatus;
            $bsProjectUserPermission->created_by           = $currentBsUser->id;
        }

        $bsProjectUserPermission->is_admin   = true;
        $bsProjectUserPermission->updated_by = $currentBsUser->id;

        return $bsProjectUserPermission->save();
    }

    // only grants permission to company admins
    private function grantBsProjectPermissionToUsersBsGroup()
    {
        $currentUser             = \Confide::user();
        $currentUserBuildSpaceId = $currentUser->getBsUser()->id;
        $bsProjectStructureId    = $this->getBsProjectMainInformation()->project_structure_id;

        try
        {
            $bsUsers = $currentUser->company->getBsCompany()->companyGroup->group->users;
        }
        catch(\ErrorException $e)
        {
            return; // At any point if the relation does not exist, we skip this step.
        }

        foreach($bsUsers as $bsUser)
        {
            if( $currentUserBuildSpaceId == $bsUser->id ) continue; // We ignore the current user, it already has the permission by default.

            if( ! $bsUser->Profile->getEProjectUser()->isActive() ) continue; // ignore inactive users

            if( ! $currentUser->company->isCompanyAdmin($bsUser->Profile->getEProjectUser()) ) continue; // ignore non-admin users

            $bsProjectPermission                       = new BsProjectUserPermission;
            $bsProjectPermission->project_structure_id = $bsProjectStructureId;
            $bsProjectPermission->user_id              = $bsUser->id;
            $bsProjectPermission->project_status       = BsProjectUserPermission::STATUS_PROJECT_BUILDER;
            $bsProjectPermission->is_admin             = true;
            $bsProjectPermission->created_by           = $currentUserBuildSpaceId;

            $bsProjectPermission->save();
        }
    }

    public function importProjectToBuildspace(UploadedFile $file)
    {
        return $this->importSubPackageFile($file);
    }

    private function importSubPackageFile(UploadedFile $file)
    {
        try
        {
            $client = new Client([
                'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
                'base_uri' => getenv('BUILDSPACE_URL'),
            ]);

            $request = $client->post('eproject_api/uploadEbqFile', array(
                'multipart' => [
                    [
                        'name'     => 'uploadedfile',
                        'filename' => $file->getClientOriginalName(),
                        'contents' => fopen($file->getPathname(), 'r'),
                    ],
                    [
                        'name'     => 'eproject_project_id',
                        'contents' => $this->id,
                    ],
                    [
                        'name'     => 'bs_user_id',
                        'contents' => \Confide::user()->getBsUser()->id,
                    ],
                ]
            ));

            $response = json_decode($request->getBody()->getContents(), true);

            if( $success = $response['success'] )
            {
                \Log::info("Imported Sub Package to BuildSpace (Project id: {$this->id})");
            }
            else
            {
                \Log::error($response['errorMsg']);
            }

            return $success;
        }
        catch(\Exception $e)
        {
            \Log::error('Import SubPackage to BuildSpace failed. Message -> ' . $e->getMessage());

            return false;
        }
    }

}