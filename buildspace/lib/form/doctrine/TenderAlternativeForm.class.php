<?php

/**
 * TenderAlternative form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class TenderAlternativeForm extends BaseTenderAlternativeForm
{
    public function configure()
    {
        parent::configure();

        unset($this['tender_origin_id'], $this['project_revision_id'], $this['deleted_at_project_revision_id'], $this['project_revision_deleted_at'], $this['is_awarded'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->setValidator('title', new sfValidatorString(array(
            'required' => true,
            'max_length' => 250), array(
            'required' => 'Title is required',
            'max_length' => 'Title is too long (%max_length% max)')
        ));
    }

    public function doSave($con=null)
    {
        $isNew = $this->object->isNew();

        parent::doSave($con);

        if($isNew)
        {
            $revision = $this->object->ProjectStructure->getLatestProjectRevision();

            $this->object->project_revision_id = $revision->id;
            $this->object->save($con);

            $this->object->refresh();

            $project = $this->object->ProjectStructure;

            $pdo = $this->object->getTable()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT COUNT(ta.id)
            FROM ".TenderAlternativeTable::getInstance()->getTableName()." ta
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON ta.project_structure_id = p.id
            JOIN ".ProjectMainInformationTable::getInstance()->getTableName()." i ON i.project_structure_id = p.id
            JOIN ".TenderSettingTable::getInstance()->getTableName()." s ON i.project_structure_id = s.project_structure_id
            WHERE p.id = ".$project->id." AND i.status IN (".ProjectMainInformation::STATUS_PRETENDER.", ".ProjectMainInformation::STATUS_TENDERING.")
            AND ta.is_awarded IS FALSE AND s.awarded_company_id IS NOT NULL
            AND ta.deleted_at IS NULL AND ta.project_revision_deleted_at IS NULL AND s.deleted_at IS NULL");

            $stmt->execute();

            $count = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            if($count)
            {
                /* We reset awarded contractor if the is not awarded tender alternative.
                 * This is because project with tender alternative must have awarded contractor AND awarded tender alternative set.
                 * This issue might happened because of the awarded contractor has been selected during tendering without any tender alternative.
                 */
                $tenderSetting = $project->TenderSetting;
                if($tenderSetting)
                {
                    $tenderSetting->awarded_company_id = null;
                    $tenderSetting->original_tender_value = 0;
                    
                    $tenderSetting->save($con);
                }
            }
        }
    }
}
