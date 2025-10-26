<?php

/**
 * ProjectCodeSettings form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class ProjectCodeSettingsForm extends BaseProjectCodeSettingsForm
{
  public function configure()
  {
    parent::configure();
    
    unset($this['project_structure_id']);
    unset($this['eproject_subsidiary_id']);
    unset($this['subsidiary_code']);
    unset($this['proportion']);
    unset($this['created_at']);
    unset($this['updated_at']);
    unset($this['deleted_at']);
    unset($this['created_by']);
    unset($this['updated_by']);
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {    
    $subsidiaryIds = explode(',', $taintedValues['allSubsidiaryIds']);

    foreach($subsidiaryIds as $id)
    {
      $this->setWidget('eproject_sub_id_' . $id, new sfWidgetFormInputText());
      $this->setWidget('identifier_' . $id, new sfWidgetFormInputText());
      $this->setWidget('proportion_' . $id, new sfWidgetFormInput());

      $this->setValidator('proportion_' . $id, new sfValidatorString(
        array(
          'required' =>false,
        )
      ));

      $this->setValidator('eproject_sub_id_' . $id, new sfValidatorString(
        array(
          'required' => true,
        ),
        array(
          'required' => 'Subsidiary must be selected',
        )
      ));

      $this->setValidator('identifier_' . $id, new sfValidatorString(
        array(
          'min_length' => 1,
          'max_length' => 100,
          'required'   => true,
        ),
        array(
          'min_length' => 'Identifier\'s length  must be at least %min_length% character.',
          'max_length' => 'Identifier\'s length  must be no more than %max_length% characters.',
          'required'   => 'Identifier is required.',
        )
      ));
    }

    $this->setWidget('projectId', new sfWidgetFormInputText());

    $this->setValidator('projectId', new sfValidatorString(
      array(
        'required' => true,
      ),
      array(
        'required' => 'Project ID is required',
      )
    ));

    $this->setWidget('allSubsidiaryIds', new sfWidgetFormInputText());

    $this->setValidator('allSubsidiaryIds', new sfValidatorString(
      array(
        'required' => true,
      ),
      array(
        'required' => 'Subsidiary ID is required',
      )
    ));

    parent::bind($taintedValues, $taintedFiles);
  }

  public function doSave($conn = null)
  {
    $values = $this->getValues();
    $project = Doctrine_Core::getTable('ProjectStructure')->find($values['projectId']);
    $allSubsidiaryIds = explode(',', $values['allSubsidiaryIds']);

    foreach($allSubsidiaryIds as $id)
    {
      $projectCodeSettings = ProjectCodeSettings::getProjectCodeSettingRecord($project, $id);
      $projectCodeSettings->eproject_subsidiary_id = $values['eproject_sub_id_' . $id];
      $projectCodeSettings->project_structure_id = $project->id;
      $projectCodeSettings->subsidiary_code = $values['identifier_' . $id];
      $projectCodeSettings->save();
    }
  }
}
