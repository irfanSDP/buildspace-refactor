<?php

/**
 * ProjectLevel form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectLevelForm extends BaseProjectLevelForm
{
    protected $parent;

    public function configure()
    {
        unset($this['project_structure_id'], $this['deleted_at'], $this['created_at'], $this['updated_at']);

        $this->parent = $this->getOption('parent');

        $this->setValidator('title', new sfValidatorString(
            array('required' => true),
            array('required' => 'Title is required')
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }

    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('s.id, s.title')->from('ProjectStructure s')
            ->where('LOWER(s.title) = ?', strtolower($values['title']))
            ->andWhere('s.root_id = ?', $this->parent->root_id)
            ->andWhere('s.type = ?', ProjectStructure::TYPE_LEVEL);

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another level with that title.');

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
        if($this->object->isNew())
        {
            $levelStructure = new ProjectStructure();
            $levelStructure->title = $values['title'];
            $levelStructure->type = ProjectStructure::TYPE_LEVEL;

            if($this->parent->node->isRoot() or $this->parent->type == ProjectStructure::TYPE_LEVEL)
            {
                $levelStructure->node->insertAsFirstChildOf($this->parent);
            }
            else
            {
                $levelStructure->node->insertAsNextSiblingOf($this->parent);
            }

            $levelStructure->refresh();

            $this->object->project_structure_id = $levelStructure->id;
        }
        else
        {
            $this->object->ProjectStructure->title = $values['title'];
            $this->object->ProjectStructure->save($con);
        }

        parent::doSave($con);
    }
}
