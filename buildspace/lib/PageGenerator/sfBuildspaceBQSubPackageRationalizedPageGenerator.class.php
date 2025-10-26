<?php

class sfBuildspaceBQSubPackageRationalizedPageGenerator extends sfBuildspaceBQRationalizedPageGenerator
{
    public function updateBillReferences()
    {
        //Do nothing
    }

    public function calculateBQItemDescription(Array $billItem)
    {
        $billRef                 = $this->generateBillRefString($billItem, $this->bill->BillLayoutSetting->page_no_prefix);
        $descriptionBillRef      = (strlen($billRef)) ? '<b>('.$billRef.') - </b>' : '';
        $billItem['description'] = $descriptionBillRef.$billItem['description'];

        return parent::calculateBQItemDescription($billItem);
    }
}