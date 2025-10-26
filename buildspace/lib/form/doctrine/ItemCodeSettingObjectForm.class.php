<?php

/**
 * ItemCodeSettingObject form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id$
 */
class ItemCodeSettingObjectForm extends BaseItemCodeSettingObjectForm
{
  public function configure()
  {
    parent::configure();

    unset($this['project_structure_id']);
    unset($this['object_id']);
    unset($this['object_type']);
    unset($this['created_at']);
    unset($this['updated_at']);
    unset($this['created_by']);
    unset($this['updated_by']);
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    $itemCodeSettingIds = explode(',', $taintedValues['itemCodeSettingIds']);
    $itemCodeSettingObjectIds = explode(',', $taintedValues['itemCodeSettingObjectIds']);

    $this->setWidget('projectStructureId', new sfWidgetFormInputText());
    $this->setValidator('projectStructureId', new sfValidatorString(
      array(
        'required' => true,
      )
    ));

    $this->setWidget('claimCertificateId', new sfWidgetFormInputText());
    $this->setValidator('claimCertificateId', new sfValidatorString(
      array(
        'required' => true,
      )
    ));

    $this->setWidget('itemCodeSettingObjectIds', new sfWidgetFormInputText());
    $this->setValidator('itemCodeSettingObjectIds', new sfValidatorString(
      array(
        'required' => true,
      )
    ));

    $this->setWidget('itemCodeSettingIds', new sfWidgetFormInputText());
    $this->setValidator('itemCodeSettingIds', new sfValidatorString(
      array(
        'required' => true,
      )
    ));
    
    foreach($itemCodeSettingObjectIds as $objectId)
    {
      $currentClaimFieldName = 'currentClaim_' . $objectId;
      $objectTypeFieldName = 'objectType_' . $objectId;

      $this->setWidget($currentClaimFieldName, new sfWidgetFormInputText());
      $this->setValidator($currentClaimFieldName, new sfValidatorString(
        array(
          'required' => true,
        )
      ));

      $this->setWidget($objectTypeFieldName, new sfWidgetFormInputText());
      $this->setValidator($objectTypeFieldName, new sfValidatorString(
        array(
          'required' => true,
        )
      ));

      foreach($itemCodeSettingIds as $icsId)
      {
        $fieldName = 'item_code_setting_object_id_' . $objectId . '_item_code_setting_id_' . $icsId;

        $this->setWidget($fieldName, new sfWidgetFormInputText());
        $this->setValidator($fieldName, new sfValidatorString(
          array(
            'required' => true,
          )
        ));
      }
    }

    $this->mergePostValidator(new sfValidatorCallback(array('callback' => array($this, 'validateGroupObjectTotal'))));

    parent::bind($taintedValues, $taintedFiles);
  }

  public function validateGroupObjectTotal(sfValidatorCallback $validator, array $values)
  {
    $itemCodeSettingIds = explode(',', $values['itemCodeSettingIds']);
    $itemCodeSettingObjectIds = explode(',', $values['itemCodeSettingObjectIds']);

    $currentClaimsByObjectId = [];

    foreach($itemCodeSettingObjectIds as $objectId)
    {
      $currentClaimsByObjectId[$objectId] = str_replace(',', '', $values['currentClaim_' . $objectId]);
    }

    foreach($itemCodeSettingObjectIds as $objectId)
    {
      $objectGroupTotal = 0.0;

      foreach($itemCodeSettingIds as $icsId)
      {
        $fieldName = 'item_code_setting_object_id_' . $objectId . '_item_code_setting_id_' . $icsId;
        $objectGroupTotal += $values[$fieldName];
      }

      if(($objectGroupTotal . '') != $currentClaimsByObjectId[$objectId])
      {
        $sfError = new sfValidatorError($validator, 'Sum of all Item Code amounts must tally with the current claim.');
        throw new sfValidatorErrorSchema($validator, array('amount_not_tally_error' => $sfError));
      }
    }

    return $values;
  }

  public function doSave($conn = null)
  {
    $values = $this->getValues();

    $project = Doctrine_Core::getTable('ProjectStructure')->find($values['projectStructureId']);
    $claimCertificateId = $values['claimCertificateId'];
    $itemCodeSettingIds = explode(',', $values['itemCodeSettingIds']);
    $itemCodeSettingObjectIds = explode(',', $values['itemCodeSettingObjectIds']);

    foreach($itemCodeSettingObjectIds as $objectId)
    {
      $objectTypeId = $values['objectType_' . $objectId];
      $itemCodeSettingObject = ItemCodeSettingObject::find($project, $objectId, $objectTypeId);

      foreach($itemCodeSettingIds as $icsId)
      {
        $fieldName = 'item_code_setting_object_id_' . $objectId . '_item_code_setting_id_' . $icsId;
        $itemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::find($itemCodeSettingObject->id, $claimCertificateId, $icsId);

        if( !$itemCodeSettingObjectBreakdown )
        {
          $itemCodeSettingObjectBreakdown = ItemCodeSettingObjectBreakdown::create($itemCodeSettingObject->id, $claimCertificateId, $icsId, $values[$fieldName]);
        }

        $itemCodeSettingObjectBreakdown->updated_by = sfContext::getInstance()->getUser()->getGuardUser()->getId();
        $itemCodeSettingObjectBreakdown->amount = $values[$fieldName];
        $itemCodeSettingObjectBreakdown->save();
      }
    }
  }
}