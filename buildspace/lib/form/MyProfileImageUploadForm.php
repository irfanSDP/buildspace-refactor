<?php

class MyProfileImageUploadForm extends BasesfGuardUserProfileForm
{
    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->useFields(array('profile_photo'));

        $this->validatorSchema['profile_photo'] = new sfValidatorFile(array('required' => true, 'mime_types' => 'web_images'), array('required'=>'Profile image is required'));
    }

    public function doSave($con = null)
    {
        $upload = $this->getValue('profile_photo');

        if ($upload)
        {
            $profileUploadDir = sfConfig::get("sf_web_dir").'/images/profiles';
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

        // $delete = $this->getValue('profile_photo_delete');

        // if($delete)
        // {
        //     $fileName = $this->getObject()->getProfileImage();
        //     $filePath = sfConfig::get('sf_profile_photo_dir').DIRECTORY_SEPARATOR.sha1($this->object->getName().$this->object->getId()).DIRECTORY_SEPARATOR.$fileName;
        //     @unlink($filePath);
        //     $this->getObject()->setProfileImage(null);
        // }

        parent::doSave($con);
    }

    public function updateObject($values = null)
    {
        $object = parent::updateObject($values);

        /* reset the fullpath. we only need to store the filename*/
        $object->setProfilePhoto(str_replace(sfConfig::get("sf_web_dir").'/images/profiles'.DIRECTORY_SEPARATOR,'', $object->getProfilePhoto()));

        return $object;
    }
}