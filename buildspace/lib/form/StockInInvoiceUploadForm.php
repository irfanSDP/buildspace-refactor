<?php 

class StockInInvoiceUploadForm extends BaseForm {

    public function configure()
    {
        $this->disableCSRFProtection();

        $this->setValidator("fileUpload", new sfValidatorFile(
            array(
                "max_size" => 10000000,
                "path"     => stockInActions::getFullUploadDirForInvoice(),
                "required" => false,
            )
        ));

        $this->widgetSchema->setNameFormat('stock_in_invoice[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

}