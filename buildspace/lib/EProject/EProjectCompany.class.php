<?php

class EProjectCompany extends BaseEProjectCompany
{
    public function getBsCompany()
    {
        return Doctrine_Query::create()
            ->from('Company c')
            ->where('c.reference_id = ?',$this->reference_id)
            ->fetchOne();
    }
}