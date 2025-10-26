<?php

class EProjectProjectTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectProject');
    }

    public static function getByEProjectOriginId($eprojectOriginId)
    {
        return DoctrineQuery::create()->select('ep.parent_project_id')->from('EProjectProject ep')
            ->where('ep.id = ?', $eprojectOriginId)
            ->fetchOne();
    }

    public static function getVisibleTendererRatesProjectIds()
    {
        $pdo = Doctrine_Manager::getInstance()->getConnection('eproject_conn')->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT(p.id)
            FROM projects p
            JOIN tenders t ON t.project_id = p.id
            LEFT JOIN (
                SELECT p.id
                FROM projects p
                JOIN tenders t on t.project_id = p.id
                GROUP BY p.id
                HAVING COUNT(t.*) = 1
            ) single_tender_projects on single_tender_projects.id = p.id
            WHERE (
                t.open_tender_status = " . EProjectTender::OPEN_TENDER_STATUS_OPENED . "
                OR (
                    single_tender_projects.id IS NOT NULL
                    AND p.status_id <> " . EProjectProject::STATUS_TYPE_CALLING_TENDER . "
                    AND p.status_id <> " . EProjectProject::STATUS_TYPE_CLOSED_TENDER . "
                )
            )
            AND p.deleted_at IS NULL");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}