<?php

/**
 * ProjectMainInformation form base class.
 *
 * @method ProjectMainInformation getObject() Returns the current form's model object
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
abstract class BaseProjectMainInformationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'eproject_origin_id'   => new sfWidgetFormInputText(),
      'title'                => new sfWidgetFormTextarea(),
      'description'          => new sfWidgetFormTextarea(),
      'project_structure_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'add_empty' => false)),
      'region_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'add_empty' => true)),
      'subregion_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Subregions'), 'add_empty' => true)),
      'site_address'         => new sfWidgetFormInputText(),
      'status'               => new sfWidgetFormInputText(),
      'currency_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'add_empty' => true)),
      'client'               => new sfWidgetFormInputText(),
      'tender_type_id'       => new sfWidgetFormInputText(),
      'work_category_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('WorkCategory'), 'add_empty' => true)),
      'start_date'           => new sfWidgetFormDate(),
      'published_at'         => new sfWidgetFormDateTime(),
      'unique_id'            => new sfWidgetFormTextarea(),
      'created_at'           => new sfWidgetFormDateTime(),
      'updated_at'           => new sfWidgetFormDateTime(),
      'created_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'add_empty' => true)),
      'updated_by'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'add_empty' => true)),
      'deleted_at'           => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'eproject_origin_id'   => new sfValidatorInteger(array('required' => false)),
      'title'                => new sfValidatorString(),
      'description'          => new sfValidatorString(array('required' => false)),
      'project_structure_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectStructure'), 'column' => 'id')),
      'region_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Regions'), 'column' => 'id', 'required' => false)),
      'subregion_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Subregions'), 'column' => 'id', 'required' => false)),
      'site_address'         => new sfValidatorPass(),
      'status'               => new sfValidatorInteger(array('required' => false)),
      'currency_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Currency'), 'column' => 'id', 'required' => false)),
      'client'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'tender_type_id'       => new sfValidatorInteger(array('required' => false)),
      'work_category_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('WorkCategory'), 'column' => 'id', 'required' => false)),
      'start_date'           => new sfValidatorDate(array('required' => false)),
      'published_at'         => new sfValidatorDateTime(array('required' => false)),
      'unique_id'            => new sfValidatorString(array('required' => false)),
      'created_at'           => new sfValidatorDateTime(),
      'updated_at'           => new sfValidatorDateTime(),
      'created_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Creator'), 'column' => 'id', 'required' => false)),
      'updated_by'           => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Updator'), 'column' => 'id', 'required' => false)),
      'deleted_at'           => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_main_information[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectMainInformation';
  }

}
