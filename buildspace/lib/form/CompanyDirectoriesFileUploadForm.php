<?php

class CompanyDirectoriesFileUploadForm extends BaseForm {

    public function configure()
    {
        $this->disableLocalCSRFProtection();

        $this->setValidator("fileUpload", new sfValidatorFile(
            array(
                "max_size" => 10000000,
                "path"     => sfConfig::get("sf_upload_dir_company_directories"),
                "required" => false,
            )
        ));

        $this->widgetSchema->setNameFormat('company_other_information[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }
}