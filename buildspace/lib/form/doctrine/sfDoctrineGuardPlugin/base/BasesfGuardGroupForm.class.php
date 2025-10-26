<?php

/**
 * sfGuardGroup form base class.
 *
 * @method sfGuardGroup getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasesfGuardGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                               => new sfWidgetFormInputHidden(),
      'name'                             => new sfWidgetFormInputText(),
      'description'                      => new sfWidgetFormTextarea(),
      'is_super_admin'                   => new sfWidgetFormInputCheckbox(),
      'created_at'                       => new sfWidgetFormDateTime(),
      'updated_at'                       => new sfWidgetFormDateTime(),
      'users_list'                       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser')),
      'permissions_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardPermission')),
      'menus_list'                       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Menu')),
      'projects_list'                    => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'tendering_projects_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'post_contract_projects_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'project_management_projects_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
    ));

    $this->setValidators(array(
      'id'                               => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'                             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'                      => new sfValidatorString(array('max_length' => 1000, 'required' => false)),
      'is_super_admin'                   => new sfValidatorBoolean(array('required' => false)),
      'created_at'                       => new sfValidatorDateTime(),
      'updated_at'                       => new sfValidatorDateTime(),
      'users_list'                       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser', 'required' => false)),
      'permissions_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardPermission', 'required' => false)),
      'menus_list'                       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Menu', 'required' => false)),
      'projects_list'                    => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'tendering_projects_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'post_contract_projects_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'project_management_projects_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'sfGuardGroup', 'column' => array('name')))
    );

    $this->widgetSchema->setNameFormat('sf_guard_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'sfGuardGroup';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['users_list']))
    {
      $this->setDefault('users_list', $this->object->Users->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['permissions_list']))
    {
      $this->setDefault('permissions_list', $this->object->Permissions->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['menus_list']))
    {
      $this->setDefault('menus_list', $this->object->Menus->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['projects_list']))
    {
      $this->setDefault('projects_list', $this->object->Projects->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['tendering_projects_list']))
    {
      $this->setDefault('tendering_projects_list', $this->object->TenderingProjects->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['post_contract_projects_list']))
    {
      $this->setDefault('post_contract_projects_list', $this->object->PostContractProjects->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['project_management_projects_list']))
    {
      $this->setDefault('project_management_projects_list', $this->object->ProjectManagementProjects->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateUsersList($values);
    $this->updatePermissionsList($values);
    $this->updateMenusList($values);
    $this->updateProjectsList($values);
    $this->updateTenderingProjectsList($values);
    $this->updatePostContractProjectsList($values);
    $this->updateProjectManagementProjectsList($values);

    parent::doUpdateObject($values);
  }

  public function updateUsersList($values)
  {
    if (!isset($this->widgetSchema['users_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('users_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Users->getPrimaryKeys();
    $values = $values['users_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Users', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Users', array_values($link));
    }
  }

  public function updatePermissionsList($values)
  {
    if (!isset($this->widgetSchema['permissions_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('permissions_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Permissions->getPrimaryKeys();
    $values = $values['permissions_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Permissions', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Permissions', array_values($link));
    }
  }

  public function updateMenusList($values)
  {
    if (!isset($this->widgetSchema['menus_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('menus_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Menus->getPrimaryKeys();
    $values = $values['menus_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Menus', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Menus', array_values($link));
    }
  }

  public function updateProjectsList($values)
  {
    if (!isset($this->widgetSchema['projects_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('projects_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->Projects->getPrimaryKeys();
    $values = $values['projects_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Projects', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Projects', array_values($link));
    }
  }

  public function updateTenderingProjectsList($values)
  {
    if (!isset($this->widgetSchema['tendering_projects_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('tendering_projects_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->TenderingProjects->getPrimaryKeys();
    $values = $values['tendering_projects_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('TenderingProjects', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('TenderingProjects', array_values($link));
    }
  }

  public function updatePostContractProjectsList($values)
  {
    if (!isset($this->widgetSchema['post_contract_projects_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('post_contract_projects_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->PostContractProjects->getPrimaryKeys();
    $values = $values['post_contract_projects_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('PostContractProjects', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('PostContractProjects', array_values($link));
    }
  }

  public function updateProjectManagementProjectsList($values)
  {
    if (!isset($this->widgetSchema['project_management_projects_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('project_management_projects_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->ProjectManagementProjects->getPrimaryKeys();
    $values = $values['project_management_projects_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('ProjectManagementProjects', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('ProjectManagementProjects', array_values($link));
    }
  }

}
