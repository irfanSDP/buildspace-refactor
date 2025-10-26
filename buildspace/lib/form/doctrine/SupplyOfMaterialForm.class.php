<?php

/**
 * SupplyOfMaterial form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SupplyOfMaterialForm extends BaseSupplyOfMaterialForm
{
    public function configure()
    {
        unset($this['project_structure_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->parent = $this->getOption('parent');
        $this->projectStatus = null;

        if($this->parent)
        {
            $this->projectStatus =  $this->getProjectStatus($this->parent->root_id);
        }

        if($this->projectStatus && ($this->projectStatus == ProjectMainInformation::STATUS_PRETENDER))
        {
            $this->setValidator('title', new sfValidatorString(array(
                    'required' => true,
                    'max_length' => 200), array(
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
            unset($this['title'], $this['description'], $this['unit_type']);
        }
    }

    private function getProjectStatus($rootId)
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

        if(($this->projectStatus) && $this->projectStatus == ProjectMainInformation::STATUS_PRETENDER)
        {
            if($isNew)
            {
                $billStructure = new ProjectStructure();
                $billStructure->title = $values['title'];

                $billStructure->type = ProjectStructure::TYPE_SUPPLY_OF_MATERIAL_BILL;

                if($this->parent->node->isRoot() or $this->parent->type == ProjectStructure::TYPE_LEVEL)
                {
                    $billStructure->node->insertAsFirstChildOf($this->parent);
                }
                else
                {
                    $billStructure->node->insertAsNextSiblingOf($this->parent);
                }

                $billStructure->refresh();

                $this->object->project_structure_id = $billStructure->id;
            }
            else
            {
                $this->object->ProjectStructure->title = $values['title'];
                $this->object->ProjectStructure->save($con);
            }
        }

        parent::doSave($con);
    }
}
