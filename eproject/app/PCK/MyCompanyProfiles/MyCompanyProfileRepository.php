<?php namespace PCK\MyCompanyProfiles;

use PCK\Helpers\Files;

class MyCompanyProfileRepository {

    const LOGO_FILE_DIRECTORY = '/upload/img/logo';

    private $myCompanyProfile;

    public function __construct(MyCompanyProfile $myCompanyProfile)
    {
        $this->myCompanyProfile = $myCompanyProfile;
    }

    public function find()
    {
        return $this->myCompanyProfile->findOrNew(1);
    }

    public function update(MyCompanyProfile $companyProfile, array $inputs)
    {
        $companyProfile->name = $inputs['name'];

        return $this->save($companyProfile);
    }

    private function save(MyCompanyProfile $companyProfile)
    {
        $companyProfile->save();

        return $companyProfile;
    }

    /**
     * Updates the company logo.
     *
     * @param MyCompanyProfile $companyProfile
     * @param                  $logoFile
     *
     * @return bool
     * @throws \Exception
     */
    public function updateCompanyLogo(MyCompanyProfile $companyProfile, $logoFile)
    {
        if( ! Files::isImage($logoFile) )
        {
            return false;
        }

        $existingLogoPath = public_path() . $companyProfile->company_logo_path;

        if( $companyProfile->company_logo_path && file_exists($existingLogoPath) )
        {
            Files::deleteFile($existingLogoPath);
        }

        $newLogoPath = public_path() . self::LOGO_FILE_DIRECTORY;

        // Create the folder if it does not exist, then move the file to that folder.
        Files::mkdirIfDoesNotExist($newLogoPath);
        $logoFile->move($newLogoPath, $logoFile->getClientOriginalName());

        // Update the database record
        $newFilePath = self::LOGO_FILE_DIRECTORY . '/' . $logoFile->getClientOriginalName();
        $companyProfile->company_logo_path = $newFilePath;
        $companyProfile->company_logo_filename = $logoFile->getClientOriginalName();

        return $companyProfile->save();
    }

}