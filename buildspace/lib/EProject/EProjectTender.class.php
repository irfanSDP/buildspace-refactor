<?php

class EProjectTender extends BaseEProjectTender
{
    CONST OPEN_TENDER_STATUS_NOT_YET_OPEN = 1;
    CONST OPEN_TENDER_STATUS_OPENED       = 2;

    public function getSelectedContractor($hydrationMode=null)
    {
        $query = EProjectCompanyTenderTable::getInstance()
            ->createQuery('c')
            ->select('c.*')
            ->where('c.tender_id = ?', $this->id)
            ->andWhere('c.selected_contractor IS TRUE')
            ->limit(1);

            if($hydrationMode)
            {
                $query->setHydrationMode($hydrationMode);
            }

        return $query->fetchOne();
    }

    public function getCallingTenderInformation()
    {
        $pdo = $this->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT info.id, info.tender_id, info.date_of_calling_tender, info.date_of_closing_tender, info.status, info.disable_tender_rates_submission
            FROM tender_calling_tender_information as info
            JOIN " . EProjectTenderTable::getInstance()->getTableName() . " AS tender ON info.tender_id = tender.id
            WHERE tender.id = ".$this->id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFormOfTenderHeader()
    {
        $pdo = $this->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT fot_h.header_text 
            FROM form_of_tenders fot INNER JOIN form_of_tender_headers fot_h 
            ON fot.id = fot_h.form_of_tender_id 
            WHERE fot.tender_id = " . $this->id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }
}
