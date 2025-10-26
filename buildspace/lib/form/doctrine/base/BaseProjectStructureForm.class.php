<?php

/**
 * ProjectStructure form base class.
 *
 * @method ProjectStructure getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectStructureForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                             => new sfWidgetFormInputHidden(),
      'title'                          => new sfWidgetFormTextarea(),
      'type'                           => new sfWidgetFormInputText(),
      'priority'                       => new sfWidgetFormInputText(),
      'tender_origin_id'               => new sfWidgetFormTextarea(),
      'bill_refreshed'                 => new sfWidgetFormInputCheckbox(),
      'project_revision_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectRevision'), 'add_empty' => true)),
      'created_at'                     => new sfWidgetFormDateTime(),
      'updated_at'                     => new sfWidgetFormDateTime(),
      'created_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                     => new sfWidgetFormDateTime(),
      'root_id'                        => new sfWidgetFormInputText(),
      'lft'                            => new sfWidgetFormInputText(),
      'rgt'                            => new sfWidgetFormInputText(),
      'level'                          => new sfWidgetFormInputText(),
      'project_groups_list'            => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'tendering_groups_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'post_contract_groups_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
      'project_management_groups_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup')),
    ));

    $this->setValidators(array(
      'id'                             => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'                          => new sfValidatorString(),
      'type'                           => new sfValidatorInteger(),
      'priority'                       => new sfValidatorInteger(array('required' => false)),
      'tender_origin_id'               => new sfValidatorString(array('required' => false)),
      'bill_refreshed'                 => new sfValidatorBoolean(array('required' => false)),
      'project_revision_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectRevision'), 'column' => 'id', 'required' => false)),
      'created_at'                     => new sfValidatorDateTime(),
      'updated_at'                     => new sfValidatorDateTime(),
      'created_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                     => new sfValidatorDateTime(array('required' => false)),
      'root_id'                        => new sfValidatorInteger(array('required' => false)),
      'lft'                            => new sfValidatorInteger(array('required' => false)),
      'rgt'                            => new sfValidatorInteger(array('required' => false)),
      'level'                          => new sfValidatorInteger(array('required' => false)),
      'project_groups_list'            => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'tendering_groups_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'post_contract_groups_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
      'project_management_groups_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardGroup', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_structure[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectStructure';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['project_groups_list']))
    {
      $this->setDefault('project_groups_list', $this->object->ProjectGroups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['tendering_groups_list']))
    {
      $this->setDefault('tendering_groups_list', $this->object->TenderingGroups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['post_contract_groups_list']))
    {
      $this->setDefault('post_contract_groups_list', $this->object->PostContractGroups->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['project_management_groups_list']))
    {
      $this->setDefault('project_management_groups_list', $this->object->ProjectManagementGroups->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateProjectGroupsList($values);
    $this->updateTenderingGroupsList($values);
    $this->updatePostContractGroupsList($values);
    $this->updateProjectManagementGroupsList($values);

    parent::doUpdateObject($values);
  }

  public function updateProjectGroupsList($values)
  {
    if (!isset($this->widgetSchema['project_groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('project_groups_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->ProjectGroups->getPrimaryKeys();
    $values = $values['project_groups_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('ProjectGroups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('ProjectGroups', array_values($link));
    }
  }

  public function updateTenderingGroupsList($values)
  {
    if (!isset($this->widgetSchema['tendering_groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('tendering_groups_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->TenderingGroups->getPrimaryKeys();
    $values = $values['tendering_groups_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('TenderingGroups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('TenderingGroups', array_values($link));
    }
  }

  public function updatePostContractGroupsList($values)
  {
    if (!isset($this->widgetSchema['post_contract_groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('post_contract_groups_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->PostContractGroups->getPrimaryKeys();
    $values = $values['post_contract_groups_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('PostContractGroups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('PostContractGroups', array_values($link));
    }
  }

  public function updateProjectManagementGroupsList($values)
  {
    if (!isset($this->widgetSchema['project_management_groups_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('project_management_groups_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->ProjectManagementGroups->getPrimaryKeys();
    $values = $values['project_management_groups_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('ProjectManagementGroups', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('ProjectManagementGroups', array_values($link));
    }
  }

}
