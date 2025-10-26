<?php

class MyCompanyLogoUploadForm extends BasemyCompanyProfileForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->useFields(array('company_logo'));

        $this->validatorSchema['company_logo'] = new sfValidatorFile(array('required' => true, 'mime_types' => 'web_images'), array('required'=>'Company logo is required'));
    }

    public function doSave($con = null)
    {
        $upload = $this->getValue('company_logo');

        if ($upload)
        {
            $profileUploadDir = sfConfig::get("sf_web_dir").'/images/company_logo';
            $safeFileName     = sha1($upload->getOriginalName().microtime().mt_rand()).$upload->getExtension($upload->getOriginalExtension());

            if ( ! is_dir($profileUploadDir))
            {
                mkdir($profileUploadDir, 0777, true);
            }

            $upload->save($profileUploadDir.DIRECTORY_SEPARATOR.$safeFileName);

            // Create the thumbnail
            $thumbnail = new sfThumbnail(96, 96);
            $thumbnail->loadFile($upload->getTempName());
            $thumbnail->save($profileUploadDir.DIRECTORY_SEPARATOR.$safeFileName);
        }

        // $delete = $this->getValue('company_logo_delete');

        // if($delete)
        // {
        //     $fileName = $this->getObject()->getProfileImage();
        //     $filePath = sfConfig::get('sf_company_logo_dir').DIRECTORY_SEPARATOR.sha1($this->object->getName().$this->object->getId()).DIRECTORY_SEPARATOR.$fileName;
        //     @unlink($filePath);
        //     $this->getObject()->setProfileImage(null);
        // }

        parent::doSave($con);
    }

    public function updateObject($values = null)
    {
        $object = parent::updateObject($values);

        /* reset the fullpath. we only need to store the filename*/
        $object->setCompanyLogo(str_replace(sfConfig::get("sf_web_dir").'/images/company_logo'.DIRECTORY_SEPARATOR,'', $object->getCompanyLogo()));

        return $object;
    }
}