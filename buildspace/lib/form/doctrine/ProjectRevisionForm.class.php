<?php

/**
 * ProjectRevision form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectRevisionForm extends BaseProjectRevisionForm
{
    public function configure()
    {
        unset($this['created_at'], $this['deleted_at'], $this['updated_at']);

        if ( ! $this->isNew() AND ! $this->getOption('type') )
        {
            unset($this['current_selected_revision']);
        }

        if($this->getObject()->isNew())
        {
            $this->validatorSchema->setPostValidator(new sfValidatorCallback(array('callback' => array($this, 'lockCheck'))));
            $this->validatorSchema->setPostValidator(new sfValidatorCallback(array('callback' => array($this, 'uniqueVersionCheck'))));
        }

        $this->validatorSchema->setPostValidator(new sfValidatorCallback(array('callback' => array($this, 'validateTenderAlternative'))));
    }

    public function lockCheck(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('r.id')->from('ProjectRevision r');
        $query->where('r.locked_status = ?', false);
        $query->andWhere('r.project_structure_id = ?', $values['project_structure_id']);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'Previous addendums must be locked before a new one can be added.');
            throw new sfValidatorErrorSchema($validator, array('locked_status' => $sfError));
        }

        return $values;
    }

    public function uniqueVersionCheck(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('r.id')->from('ProjectRevision r');
        $query->where('r.version = ?', $values['version']);
        $query->andWhere('r.project_structure_id = ?', $values['project_structure_id']);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already an addendum for this revision.');
            throw new sfValidatorErrorSchema($validator, array('version' => $sfError));
        }

        return $values;
    }

    public function validateTenderAlternative(sfValidatorCallback $validator, array $values)
    {
        $project = ProjectStructureTable::getInstance()->find($values['project_structure_id']);

        $untagBillIds = $project->getUntagTenderAlternativeBillIds();

        $untagBillIds = $project->getUntagTenderAlternativeBillIds();
        
        $countUntagBill = count($untagBillIds);

        if($countUntagBill > 0)
        {
            $sfError = new sfValidatorError($validator, 'There are bills that are still not tagged to any Tender Alternative.');
            throw new sfValidatorErrorSchema($validator, array('version' => $sfError));
        }

        return $values;
    }

    public function doSave($con=null)
    {
        if ( $this->object->isNew() )
        {
            $project = ProjectStructureTable::getInstance()->find($this->getValue('project_structure_id'));

            $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();
            
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(r.version), 0)
            FROM " . ProjectRevisionTable::getInstance()->getTableName() . " r
            WHERE r.project_structure_id = ".$project->id."
            AND r.deleted_at IS NULL");
            
            $stmt->execute();
            
            $maxRevision = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            if(array_key_exists('version', $this->values))
            {
                unset($this->values['version']);//unset version came from form so we can reset it to the latest version as below
            }

            $latestRevision = $maxRevision+1;

            if($latestRevision > 0)
            {
                unset($this->values['revision']);
                $this->object->revision = 'Addendum '.$latestRevision;
            }

            $this->object->version = $latestRevision;
        }

        parent::doSave($con);
    }
}