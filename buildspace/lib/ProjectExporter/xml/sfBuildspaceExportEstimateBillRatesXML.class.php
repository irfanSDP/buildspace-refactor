<?php
class sfBuildspaceExportEstimateBillRatesXML extends sfBuildspaceExportBillRatesXML
{
    public function getItemTypeRef($itemId)
    {
        $typeRefs = parent::getItemTypeRef($itemId);

        foreach($typeRefs as $typeRefKey => $typeRef)
        {
            unset($typeRefs[$typeRefKey]['id']);

            foreach($typeRefs[$typeRefKey]['FormulatedColumns'] as $formulatedColumnKey => $formulatedColumn)
            {
                unset($typeRefs[$typeRefKey]['FormulatedColumns'][$formulatedColumnKey]['id']);
                unset($typeRefs[$typeRefKey]['FormulatedColumns'][$formulatedColumnKey]['relation_id']);
            }
        }

        return $typeRefs;
    }

    public function processTypeRef( $typeRefs )
    {
        $this->createTypeRefTag();

        foreach($typeRefs as $typeRef)
        {
            $typeFc = array();

            if((array_key_exists('FormulatedColumns', $typeRef)) && count($typeRef['FormulatedColumns'] > 0))
            {
                $typeFc = $typeRef['FormulatedColumns'];

                unset($typeRef['FormulatedColumns']);
            }

            if(array_key_exists('BillColumnSetting', $typeRef) && count($typeRef['BillColumnSetting']))
            {
                $billColumnSettingOriginalId = $typeRef['BillColumnSetting']['id'];

                $typeRef['bill_column_setting_id'] = $billColumnSettingOriginalId;

                unset($typeRef['BillColumnSetting']);
            }

            if(array_key_exists('BillItem', $typeRef) && count($typeRef['BillItem']))
            {
                $billItemOriginalId = $typeRef['BillItem']['id'];

                $typeRef['bill_item_id'] = $billItemOriginalId;

                unset($typeRef['BillItem']);
            }

            $this->addTypeRefChildren( $typeRef );

            $count = 0;
            
            foreach($typeFc as $fc)
            {
                $this->createQtyTag( $fc, $count );

                $count++;
            }
        }
    }
}
