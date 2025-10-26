<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

use PCK\Buildspace\Project as BsProject;
use PCK\Buildspace\User as BsUser;
use PCK\Buildspace\UserProfile as BsUserProfile;
use PCK\Buildspace\ProjectRevision as BsProjectRevision;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\Buildspace\ProjectSchedule as BsProjectSchedule;

use PCK\Companies\Company as EprojectCompany;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\Files;
use PCK\Helpers\Xml;
use PCK\Projects\Project;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;
use PCK\Users\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectMainInformation extends Model {

    const STATUS_PRETENDER          = 1;
    const STATUS_TENDERING          = 2;
    const STATUS_POSTCONTRACT       = 4;
    const POST_CONTRACT_TYPE_NORMAL = 1;
    const POST_CONTRACT_TYPE_NEW    = 2;

    protected $connection = 'buildspace';

    protected $table = 'bs_project_main_information';

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $projectMainInformation)
        {
            self::createBsProject($projectMainInformation);
        });

        static::created(function(self $projectMainInformation)
        {
            self::applyUserPermission($projectMainInformation);
        });
    }

    public function getEProjectProject()
    {
        return Project::find($this->eproject_origin_id);
    }

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    /**
     * Creates a new project in Buildspace_SAML.
     *
     * @param ProjectMainInformation $projectMainInformation
     */
    protected static function createBsProject(self $projectMainInformation)
    {
        $user = \Confide::user();

        $bsUser = self::findOrNewBsUser($user);

        $bsProject = new BsProject();

        $bsProject->type       = BsProject::TYPE_ROOT; //to factor
        $bsProject->title      = $projectMainInformation->title;
        $bsProject->created_by = $bsUser->id;

        $bsProject->save();

        $projectRevision                            = new BsProjectRevision();
        $projectRevision->project_structure_id      = $bsProject->id;
        $projectRevision->current_selected_revision = true;

        $projectRevision->save();

        $projectMainInformation->status               = self::STATUS_PRETENDER;
        $projectMainInformation->unique_id            = self::generateUniqueId($bsProject);
        $projectMainInformation->project_structure_id = $bsProject->id;
        $projectMainInformation->created_by           = $bsUser->id;
    }

    /**
     * Returns the Buildspace user,
     * or creates one if they do not exist.
     *
     * @param User $user
     *
     * @return User
     */
    protected static function findOrNewBsUser(User $user)
    {
        $bsUserProfile = BsUserProfile::where('bs_sf_guard_user_profile.eproject_user_id', $user->id)
            ->where('bs_sf_guard_user_profile.deleted_at', null)
            ->first();

        if( ! $bsUserProfile )
        {
            $bsUser = new BsUser();

            $bsUser->first_name    = $user->name;
            $bsUser->username      = $user->username;
            $bsUser->email_address = $user->email;

            $bsUser->save();

            $bsUserProfile = new BsUserProfile();

            $bsUserProfile->user_id          = $bsUser->id;
            $bsUserProfile->eproject_user_id = $user->id;
            $bsUserProfile->name             = $user->name;
            $bsUserProfile->contact_num      = $user->contact_number;

            $bsUserProfile->save();
        }
        else
        {
            $bsUser = $bsUserProfile->User;
        }

        return $bsUser;
    }

    /**
     * Set the user permissions in Buildspace for the project.
     *
     * @param ProjectMainInformation $projectMainInformation
     */
    protected static function applyUserPermission(self $projectMainInformation)
    {
        $bsUser = BsUser::where('bs_sf_guard_user.id', $projectMainInformation->created_by)
            ->where('bs_sf_guard_user.is_super_admin', false)
            ->first();

        if( $bsUser )
        {
            $bsProjectUserPermission = new BsProjectUserPermission();

            $bsProjectUserPermission->project_structure_id = $projectMainInformation->project_structure_id;
            $bsProjectUserPermission->user_id              = $bsUser->id;
            $bsProjectUserPermission->project_status       = BsProjectUserPermission::STATUS_PROJECT_BUILDER;
            $bsProjectUserPermission->is_admin             = true;
            $bsProjectUserPermission->created_by           = $bsUser->id;

            $bsProjectUserPermission->save();
        }
    }

    private static function generateUniqueId(BsProject $project)
    {
        $information = array( \Config::get('buildspace.BUILDSPACE_ID'), $project->id, $project->created_at, $project->created_by );

        return \Hash::make(implode('-', $information));
    }

    /**
     * Checks if the rates file is for the project.
     *
     * @param Project      $project
     * @param UploadedFile $rates
     *
     * @throws \Exception
     */
    public static function validateRatesFile(Project $project, UploadedFile $rates)
    {
        // Unzip file.
        // We create (and subsequently delete) the temporary folder because of the issue with mkdir not working on the eProject server.
        if( ! ( $tempFolder = Files::unzip($rates) ) )
        {
            throw new ValidationException(trans('files.unzipFailed'));
        }

        // Extract file contents.
        $parsedXML = GetTenderAmountFromImportedZip::getParsedFile($tempFolder);

        // Delete temporary folder.
        Files::deleteDirectory($tempFolder);

        $uniqueId = Xml::getXMLElementAttribute($parsedXML, 'uniqueId');
        $version  = Xml::getXMLElement($parsedXML, array( 'REVISIONS', 'VERSION', 'version' ), 'int');

        if( is_null($version) )
        {
            throw new ValidationException(trans('files.outdatedRates'));
        }

        $projectMainInformation = ProjectMainInformation::where('eproject_origin_id', '=', $project->id)->first();

        // check unique id
        if( $projectMainInformation->unique_id != $uniqueId )
        {
            throw new ValidationException(trans('files.projectMismatchRates'));
        }

        //check revision version
        $projectRevisionVersion = ProjectRevision::where('project_structure_id', '=', $projectMainInformation->project_structure_id)
            ->max('version');

        if( $projectRevisionVersion != $version )
        {
            throw new ValidationException(trans('files.revisionMismatchRates') . ' [ Current Project Version: ' . $projectRevisionVersion . ', File Version: ' . $version . ' ]');
        }

        // Passed all checks
    }

    public function getAwardedEProjectCompany()
    {
        $projectStructure = $this->projectStructure;

        $res = \DB::connection('buildspace')
            ->table('bs_tender_settings')
            ->where('project_structure_id', '=', $projectStructure->id)
            ->first();

        if( is_null($res) ) return null;

        if( is_null($bsCompany = Company::where('id', '=', $res->awarded_company_id)->first()) ) return null;

        return EprojectCompany::where('reference_id', '=', $bsCompany->reference_id)->first();
    }

    public function getProjectScheduleList()
    {
        if($this->status == self::STATUS_POSTCONTRACT)
        {
            return BsProjectSchedule::where('project_structure_id', $this->project_structure_id)
            ->orderBy('created_at')
            ->get();
        }

        return [];
    }
}