<?php

class StockInDeliveryOrderUploadForm extends BaseForm {

    public function configure()
    {
        $this->disableCSRFProtection();

        $this->setValidator("fileUpload", new sfValidatorFile(
            array(
                "max_size" => 10000000,
                "path"     => stockInActions::getFullUploadDirForDeliveryOrder(),
                "required" => false,
            )
        ));

        $this->widgetSchema->setNameFormat('stock_in_delivery_order[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }

}