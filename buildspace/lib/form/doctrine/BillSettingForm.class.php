<?php

/**
 * BillSetting form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class BillSettingForm extends BaseBillSettingForm
{
    public function configure()
    {
        unset($this['project_structure_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->parent = $this->getOption('parent');
        $this->statusId = null;
        $this->billType = $this->getOption('billType');

        if($this->parent)
        {
            $this->statusId = $this->getProjectStatus($this->parent->root_id);
        }

        if($this->statusId && ($this->statusId == ProjectMainInformation::STATUS_PRETENDER))
        {
            $this->setValidator('title', new sfValidatorString(array(
                    'required' => true,
                    'max_length' => 150), array(
                    'required' => 'Title is required',
                    'max_length' => 'Title is too long (%max_length% max)')
            ));

            $this->validatorSchema->setPostValidator(
                new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
            );
        }
        else
        {
            //Unset if project not in pretender status

            // disabled for now
            // if not pretender status, need to check for active addendum
            // unset($this['title'], $this['description'], $this['build_up_quantity_rounding_type']);
        }
    }

    public function getProjectStatus($rootId)
    {
        $project = DoctrineQuery::create()
            ->select('p.id, m.status')
            ->from('ProjectStructure p')
            ->leftJoin('p.MainInformation m')
            ->where('p.id = ?', $rootId)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->fetchOne();

        return $project['MainInformation']['status'];
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s');
        $query->where('LOWER(s.title) = ?', strtolower($values['title']));
        $query->andWhere('s.root_id = ?', $this->parent->root_id);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another Bill with that title.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('title' => $sfError));
            }
            else
            {
                $structure = $query->fetchOne();
                if($this->object->ProjectStructure->id != $structure->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('title' => $sfError));
                }
            }
        }
        return $values;
    }

    public function doSave($con=null)
    {
        $values = $this->getValues();

        $isNew = $this->object->isNew();
        $lastBuildUpRateRoundingSetting = $this->object->build_up_rate_rounding_type;
        $lastBuildUpQuantityRoundingSetting = $this->object->build_up_quantity_rounding_type;

        if(($this->statusId) && $this->statusId == ProjectMainInformation::STATUS_PRETENDER)
        {
            if($isNew)
            {
                $billStructure = new ProjectStructure();
                $billStructure->title = $values['title'];

                $billStructure->type = ProjectStructure::TYPE_BILL;

                if($this->parent->node->isRoot() or $this->parent->type == ProjectStructure::TYPE_LEVEL)
                {
                    $billStructure->node->insertAsFirstChildOf($this->parent);
                }
                else
                {
                    $billStructure->node->insertAsNextSiblingOf($this->parent);
                }

                $billStructure->refresh();

                $billStructure->BillType->type = $this->billType;
                $billStructure->BillType->status = BillType::STATUS_OPEN;
                $billStructure->BillType->save();

                $this->object->project_structure_id = $billStructure->id;

                $projectId = $billStructure->getRoot()->id;

                $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($projectId);

                if($currentlyEditingProjectRevision)
                {
                    $this->object->ProjectStructure->project_revision_id = $currentlyEditingProjectRevision->id;
                }
            }
            else
            {
                $this->object->ProjectStructure->title = $values['title'];
                $this->object->ProjectStructure->save($con);
            }
        }

        if(($this->statusId) && $this->statusId == ProjectMainInformation::STATUS_TENDERING)
        {
            $projectId = null;

            if($isNew)
            {
                $billStructure = new ProjectStructure();
                $billStructure->title = $values['title'];

                $billStructure->type = ProjectStructure::TYPE_BILL;

                if($this->parent->node->isRoot() or $this->parent->type == ProjectStructure::TYPE_LEVEL)
                {
                    $billStructure->node->insertAsFirstChildOf($this->parent);
                }
                else
                {
                    $billStructure->node->insertAsNextSiblingOf($this->parent);
                }

                $billStructure->refresh();

                $billStructure->BillType->type = $this->billType;
                $billStructure->BillType->status = BillType::STATUS_OPEN;
                $billStructure->BillType->save();

                $this->object->project_structure_id = $billStructure->id;

                $projectId = $billStructure->getRoot()->id;
            }
            else
            {
                $this->object->ProjectStructure->title = $values['title'];
                $this->object->ProjectStructure->save($con);

                $projectId = $this->object->ProjectStructure->getRoot()->id;
            }

            $currentlyEditingProjectRevision = ProjectRevisionTable::getCurrentlyEditingProjectRevisionFromBillId($projectId);

            if($currentlyEditingProjectRevision)
            {
                $this->object->ProjectStructure->project_revision_id = $currentlyEditingProjectRevision->id;
            }
        }

        parent::doSave($con);

        //Workaround to update BuildUpRoundingSetting
        //if this object is not new and new setting != old setting
        //update and recalculate buildUpQuantity
        if(!$isNew && ($lastBuildUpQuantityRoundingSetting != $this->object->build_up_quantity_rounding_type))
        {
            $billColumnSettings = $this->object->ProjectStructure->BillColumnSettings->toArray();
            $billMarkupSetting = $this->object->ProjectStructure->BillMarkupSetting->toArray();

            BillSettingTable::updateAllBuildUpQuantityRoundingbyBillId($this->object->project_structure_id, $billColumnSettings, $this->object->build_up_quantity_rounding_type, $billMarkupSetting);
        }

        if(!$isNew && ($lastBuildUpRateRoundingSetting != $this->object->build_up_rate_rounding_type)){
            //Workaround to update BuildUpRoundingSetting
            //if this object is not new and new setting != old setting
            //update and recalculate buildUpRate
            $billColumnSettings = $this->object->ProjectStructure->BillColumnSettings->toArray();
            $billMarkupSetting = $this->object->ProjectStructure->BillMarkupSetting->toArray();

            BillSettingTable::updateAllBuildUpRateRoundingByBillId($this->object->project_structure_id, $billColumnSettings, $this->object->build_up_rate_rounding_type, $billMarkupSetting);
        }
    }
}
