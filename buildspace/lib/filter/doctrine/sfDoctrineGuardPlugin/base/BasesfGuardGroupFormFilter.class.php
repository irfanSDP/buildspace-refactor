<?php

/**
 * sfGuardGroup filter form base class.
 *
 * @package    buildspace
 * @subpackage filter
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BasesfGuardGroupFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                             => new sfWidgetFormFilterInput(),
      'description'                      => new sfWidgetFormFilterInput(),
      'is_super_admin'                   => new sfWidgetFormChoice(array('choices' => array('' => 'yes or no', 1 => 'yes', 0 => 'no'))),
      'created_at'                       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                       => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'users_list'                       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser')),
      'permissions_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardPermission')),
      'menus_list'                       => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Menu')),
      'projects_list'                    => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'tendering_projects_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'post_contract_projects_list'      => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
      'project_management_projects_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure')),
    ));

    $this->setValidators(array(
      'name'                             => new sfValidatorPass(array('required' => false)),
      'description'                      => new sfValidatorPass(array('required' => false)),
      'is_super_admin'                   => new sfValidatorChoice(array('required' => false, 'choices' => array('', 1, 0))),
      'created_at'                       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                       => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'users_list'                       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardUser', 'required' => false)),
      'permissions_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'sfGuardPermission', 'required' => false)),
      'menus_list'                       => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Menu', 'required' => false)),
      'projects_list'                    => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'tendering_projects_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'post_contract_projects_list'      => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
      'project_management_projects_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ProjectStructure', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_guard_group_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addUsersListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardUserGroup sfGuardUserGroup')
      ->andWhereIn('sfGuardUserGroup.user_id', $values)
    ;
  }

  public function addPermissionsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardGroupPermission sfGuardGroupPermission')
      ->andWhereIn('sfGuardGroupPermission.permission_id', $values)
    ;
  }

  public function addMenusListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardGroupMenu sfGuardGroupMenu')
      ->andWhereIn('sfGuardGroupMenu.menu_id', $values)
    ;
  }

  public function addProjectsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardProjectGroup sfGuardProjectGroup')
      ->andWhereIn('sfGuardProjectGroup.project_structure_id', $values)
    ;
  }

  public function addTenderingProjectsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardTenderingProjectGroup sfGuardTenderingProjectGroup')
      ->andWhereIn('sfGuardTenderingProjectGroup.project_structure_id', $values)
    ;
  }

  public function addPostContractProjectsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardPostContractProjectGroup sfGuardPostContractProjectGroup')
      ->andWhereIn('sfGuardPostContractProjectGroup.project_structure_id', $values)
    ;
  }

  public function addProjectManagementProjectsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query
      ->leftJoin($query->getRootAlias().'.sfGuardProjectManagementGroup sfGuardProjectManagementGroup')
      ->andWhereIn('sfGuardProjectManagementGroup.project_structure_id', $values)
    ;
  }

  public function getModelName()
  {
    return 'sfGuardGroup';
  }

  public function getFields()
  {
    return array(
      'id'                               => 'Number',
      'name'                             => 'Text',
      'description'                      => 'Text',
      'is_super_admin'                   => 'Boolean',
      'created_at'                       => 'Date',
      'updated_at'                       => 'Date',
      'users_list'                       => 'ManyKey',
      'permissions_list'                 => 'ManyKey',
      'menus_list'                       => 'ManyKey',
      'projects_list'                    => 'ManyKey',
      'tendering_projects_list'          => 'ManyKey',
      'post_contract_projects_list'      => 'ManyKey',
      'project_management_projects_list' => 'ManyKey',
    );
  }
}
