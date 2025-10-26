<?php

/**
 * Company form base class.
 *
 * @method Company getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseCompanyForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'reference_id'               => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('EProjectCompany'), 'add_empty' => false)),
      'registration_no'            => new sfWidgetFormInputText(),
      'name'                       => new sfWidgetFormInputText(),
      'shortname'                  => new sfWidgetFormInputText(),
      'contact_person_name'        => new sfWidgetFormInputText(),
      'contact_person_email'       => new sfWidgetFormInputText(),
      'contact_person_direct_line' => new sfWidgetFormInputText(),
      'contact_person_mobile'      => new sfWidgetFormInputText(),
      'address'                    => new sfWidgetFormInputText(),
      'about'                      => new sfWidgetFormInputText(),
      'region_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'add_empty' => false)),
      'sub_region_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'add_empty' => false)),
      'phone_number'               => new sfWidgetFormInputText(),
      'fax_number'                 => new sfWidgetFormInputText(),
      'website'                    => new sfWidgetFormTextarea(),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'created_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'                 => new sfWidgetFormDateTime(),
      'sub_packages_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'SubPackage')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'reference_id'               => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('EProjectCompany'), 'column' => 'reference_id')),
      'registration_no'            => new sfValidatorPass(),
      'name'                       => new sfValidatorPass(),
      'shortname'                  => new sfValidatorPass(array('required' => false)),
      'contact_person_name'        => new sfValidatorPass(array('required' => false)),
      'contact_person_email'       => new sfValidatorPass(array('required' => false)),
      'contact_person_direct_line' => new sfValidatorPass(array('required' => false)),
      'contact_person_mobile'      => new sfValidatorPass(array('required' => false)),
      'address'                    => new sfValidatorPass(),
      'about'                      => new sfValidatorPass(array('required' => false)),
      'region_id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Region'), 'column' => 'id')),
      'sub_region_id'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SubRegion'), 'column' => 'id')),
      'phone_number'               => new sfValidatorPass(),
      'fax_number'                 => new sfValidatorPass(),
      'website'                    => new sfValidatorString(array('required' => false)),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
      'created_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'                 => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'                 => new sfValidatorDateTime(array('required' => false)),
      'sub_packages_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'SubPackage', 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorAnd(array(
        new sfValidatorDoctrineUnique(array('model' => 'Company', 'column' => array('reference_id'))),
        new sfValidatorDoctrineUnique(array('model' => 'Company', 'column' => array('registration_no'))),
        new sfValidatorDoctrineUnique(array('model' => 'Company', 'column' => array('name', 'deleted_at'))),
      ))
    );

    $this->widgetSchema->setNameFormat('company[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Company';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['sub_packages_list']))
    {
      $this->setDefault('sub_packages_list', $this->object->SubPackages->getPrimaryKeys());
    }

  }

  protected function doUpdateObject($values)
  {
    $this->updateSubPackagesList($values);

    parent::doUpdateObject($values);
  }

  public function updateSubPackagesList($values)
  {
    if (!isset($this->widgetSchema['sub_packages_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (!array_key_exists('sub_packages_list', $values))
    {
      // no values for this widget
      return;
    }

    $existing = $this->object->SubPackages->getPrimaryKeys();
    $values = $values['sub_packages_list'];
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('SubPackages', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('SubPackages', array_values($link));
    }
  }

}
