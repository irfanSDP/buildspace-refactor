<?php

/**
 * NewPostContractFormInformation form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class NewPostContractFormInformationForm extends BaseNewPostContractFormInformationForm
{
    protected $project;
    protected $estimationRate;
    protected $withNotListedItems;

    public function configure()
    {
        parent::configure();

        $this->setWidget('works_1',new sfWidgetFormInputText());
        $this->setWidget('works_2',new sfWidgetFormInputText());

        $this->setWidget('labour_rate_hours',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_skilled_normal',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_skilled_ot',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_semi_skilled_normal',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_semi_skilled_ot',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_labour_normal',new sfWidgetFormInputText());
        $this->setWidget('labour_rate_labour_ot',new sfWidgetFormInputText());
        $this->setWidget('e_tender_waiver', new sfWidgetFormInputText());
        $this->setWidget('e_auction_waiver', new sfWidgetFormInputText());
        $this->setWidget('eTenderWaiverOption', new sfWidgetFormInputText());
        $this->setWidget('eAuctionWaiverOption', new sfWidgetFormInputText());
        $this->setWidget('eTenderWaiverUserDefinedOption', new sfWidgetFormInputText());
        $this->setWidget('eAuctionWaiverUserDefinedOption', new sfWidgetFormInputText());

        $this->setValidators(array(
            'project_structure_id'            => new sfValidatorInteger(
                array(
                    'required' => true,
                )
            ),
            'type'                            => new sfValidatorInteger(
                array(
                    'required' => true,
                    'trim'     => true,
                ),
                array(
                    'required' => 'Type is required',
                    'invalid'  => 'Type must be integer',
                )
            ),
            'form_number'                     => new sfValidatorInteger(
                array(
                    'required' => true,
                    'trim'     => true,
                    'min'      => 1,
                ),
                array(
                    'required' => 'Number is required',
                    'invalid'  => 'Number must be integer',
                    'min'      => 'Number must be greater than 0',
                )
            ),
            'reference'                       => new sfValidatorString(
                array(
                    'required'   => true,
                ),
                array(
                    'required' => 'This field is required'
                )
            ),
            'contract_period_from'            => new sfValidatorDate(
                array(
                    'required' => true,
                ),
                array(
                    'required'   => 'Contract Period starting date is required',
                    'bad_format' => 'The format is wrong.'
                )
            ),
            'contract_period_to'              => new sfValidatorDate(
                array(
                    'required' => true,
                ),
                array(
                    'required'   => 'Contract Period ending date is required',
                    'bad_format' => 'The format is wrong.'
                )
            ),
            'awarded_date'            => new sfValidatorDate(
                array(
                    'required' => true,
                ),
                array(
                    'required'   => 'Awarded date is required',
                    'bad_format' => 'The format is wrong.'
                )
            ),
            'pre_defined_location_code_id'    => new sfValidatorInteger(
                array(
                    'required' => true,
                ),
                array(
                    'required' => 'Trade must be selected',
                )
            ),
            'creditor_code'                   => new sfValidatorString(
                array(
                    'required'   => false,
                    'max_length' => 100
                ),
                array(
                    'max_length' => 'Creditor code is too long(%max_length% characters max)'
                )
            ),
            'remarks'                         => new sfValidatorString(
                array(
                    'required' => false
                )
            ),
            'retention'                       => new sfValidatorNumber(
                array(
                    'min' => 0,
                    'max' => 100,
                ),
                array(
                    'min' => 'Minimum retention is 0 %',
                    'max' => 'Maximum retention is 100 %',
                )
            ),
            'max_retention_sum'               => new sfValidatorNumber(
                array(
                    'min' => 0,
                    'max' => 100,
                ),
                array(
                    'min' => 'Minimum retention is 0 %',
                    'max' => 'Maximum retention is 100 %',
                )
            ),
            'works_1'                         => new sfValidatorInteger(
                array(
                    'required' => false,
                )
            ),
            'works_2'                         => new sfValidatorInteger(
                array(
                    'required' => false,
                )
            ),
            'labour_rate_hours'               => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_skilled_normal'      => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_skilled_ot'          => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_semi_skilled_normal' => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_semi_skilled_ot'     => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_labour_normal'       => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'labour_rate_labour_ot'           => new sfValidatorNumber(
                array(
                    'required' => true,
                    'min'      => 0,
                ),
                array(
                    'required' => 'This field is required',
                    'min'      => 'The minimum value is %min%',
                )
            ),
            'e_tender_waiver'            => new sfValidatorBoolean(
                array(
                    'required' => false,
                )
            ),
            'e_auction_waiver'            => new sfValidatorBoolean(
                array(
                    'required' => false,
                )
            ),
            'eTenderWaiverOption'            => new sfValidatorInteger(
                array(
                    'required' => false,
                )
            ),
            'eAuctionWaiverOption'            => new sfValidatorInteger(
                array(
                    'required' => false,
                )
            ),
            'eTenderWaiverUserDefinedOption' => new sfValidatorString(
                array(
                    'required' => false,
                )
            ),
            'eAuctionWaiverUserDefinedOption' => new sfValidatorString(
                array(
                    'required' => false,
                )
            ),
        ));

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'customValidation')))
        );
    }

    public function setParameters($project, $estimationRate, $withNotListedItems)
    {
        $this->project = $project;
        $this->estimationRate = $estimationRate;
        $this->withNotListedItems = $withNotListedItems;
    }

    protected function isEProjectInPostContract()
    {
        return $this->project->MainInformation->EProjectProject && ($this->project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_POST_CONTRACT || $this->project->MainInformation->EProjectProject->status_id == EProjectProject::STATUS_TYPE_COMPLETED) && $this->project->MainInformation->EProjectProject->getLatestTender();
    }

    public function bind(array $taintedValues =null, array $taintedFiles= null)
    {
        if($this->isEProjectInPostContract())
        {
            unset($this['contract_period_to']);
            unset($this['contract_period_from']);
            unset($this['pre_defined_location_code_id']);
            unset($this['labour_rate_hours']);
            unset($this['labour_rate_skilled_normal']);
            unset($this['labour_rate_skilled_ot']);
            unset($this['labour_rate_semi_skilled_normal']);
            unset($this['labour_rate_semi_skilled_ot']);
            unset($this['labour_rate_labour_normal']);
            unset($this['labour_rate_labour_ot']);
            unset($this['e_tender_waiver']);
            unset($this['e_auction_waiver']);
            unset($this['eTenderWaiverOption']);
            unset($this['eAuctionWaiverOption']);
            unset($this['eTenderWaiverUserDefinedOption']);
            unset($this['eAuctionWaiverUserDefinedOption']);
        }

        parent::bind($taintedValues, $taintedFiles);
    }

    public static function getSiblingProjectFormNumbers(ProjectStructure $project, $formType)
    {
        $formNumbers = array();

        foreach(ProjectStructureTable::getSiblingProjects($project) as $siblingProject)
        {
            $newPostContractFormInformation = $siblingProject->NewPostContractFormInformation;

            if( $newPostContractFormInformation->exists() && ( $newPostContractFormInformation->type == $formType ) ) $formNumbers[] = $newPostContractFormInformation->form_number;
        }

        return $formNumbers;
    }

    public function validateFormNumberUniqueness(sfValidatorCallback $validator, array $values)
    {
        $project = Doctrine_Core::getTable('ProjectStructure')->find($values['project_structure_id']);

        if(is_null($project->MainInformation->eproject_origin_id)) return null;

        if(in_array($values['form_number'], self::getSiblingProjectFormNumbers($project, $values['type'])))
        {
            $sfError = new sfValidatorError($validator, 'There is already another record with this form number.');

            if($this->object->isNew())
            {
                return $sfError;
            }
            else
            {
                $eProjectProject = EProjectProjectTable::getByEProjectOriginId($project->MainInformation->eproject_origin_id);

                if($this->object->id != $eProjectProject->BuildspaceProjectMainInfo->ProjectStructure->NewPostContractFormInformation->id)
                {
                    return $sfError;
                }
            }
        }

        return null;
    }

    public function validateReferenceUniqueness(sfValidatorCallback $validator, array $values)
    {
        $pdo = NewPostContractFormInformationTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT COUNT(*)
            FROM " . NewPostContractFormInformationTable::getInstance()->getTableName() . " i
            WHERE project_structure_id != :projectId
            AND reference = :reference;");

        $stmt->execute(array('projectId' => $values['project_structure_id'], 'reference' => $values['reference']));

        $count = $stmt->fetch(PDO::FETCH_COLUMN);

        if($count > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another record with this reference.');

            return $sfError;
        }

        return null;
    }

    public function validateWorks(sfValidatorCallback $validator, array $values, $workType)
    {
        if(empty($values['works_' . $workType])) return null;

        $query = DoctrineQuery::create()->select('w.i, w.form_number')->from('SubPackageWorks w');
        $query->where('w.id = ?', $values['works_' . $workType]);
        $query->andWhere('w.type = ?', $workType);

        if($query->count() < 1)
        {
            return new sfValidatorError($validator, 'The Sub Package works is invalid.');
        }

        return null;
    }

    public function validateETenderWaiver($validator, array $values)
    {
        $eTenderWaiverIsChecked = ($values['e_tender_waiver'] === true);
        $eTenderWaiverOptionIsSelected = ! is_null($values['eTenderWaiverOption']);
        $optionOthersIsSelected = ($values['eTenderWaiverOption'] == NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS);
        $eTenderWaiverUserDefinedOptionIsFilled = (strlen($values['eTenderWaiverUserDefinedOption']) > 0);

        if($eTenderWaiverIsChecked)
        {
            if($eTenderWaiverOptionIsSelected)
            {
                if($optionOthersIsSelected)
                {
                    if( ! $eTenderWaiverUserDefinedOptionIsFilled )
                    {
                        return new sfValidatorError($validator, 'Please specify the option.');
                    }
                }
            }
            else
            {
                return new sfValidatorError($validator, 'Please select an option.');
            }
        }

        return null;

    }

    public function validateEAuctionWaiver($validator, array $values)
    {
        $eAuctionWaiverIsChecked = ($values['e_auction_waiver'] === true);
        $eAuctionWaiverOptionIsSelected = ! is_null($values['eAuctionWaiverOption']);
        $optionOthersIsSelected = ($values['eAuctionWaiverOption'] == NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS);
        $eAuctionWaiverUserDefinedOptionIsFilled = (strlen($values['eAuctionWaiverUserDefinedOption']) > 0);

        if($eAuctionWaiverIsChecked)
        {
            if($eAuctionWaiverOptionIsSelected)
            {
                if($optionOthersIsSelected)
                {
                    if( ! $eAuctionWaiverUserDefinedOptionIsFilled )
                    {
                        return new sfValidatorError($validator, 'Please specify the option.');
                    }
                }
            }
            else
            {
                return new sfValidatorError($validator, 'Please select an option.');
            }
        }

        return null;
    }

    public function customValidation(sfValidatorCallback $validator, array $values)
    {
        $errors = array();

        if($formNumberError = $this->validateFormNumberUniqueness($validator, $values)) $errors['form_number'] = $formNumberError;
        if($referenceError = $this->validateReferenceUniqueness($validator, $values)) $errors['reference'] = $referenceError;
        if($works1Error = $this->validateWorks($validator, $values, SubPackageWorks::TYPE_1)) $errors['works_1'] = $works1Error;
        if($works2Error = $this->validateWorks($validator, $values, SubPackageWorks::TYPE_2)) $errors['works_2'] = $works2Error;
        if($eTenderWaiverError = $this->validateETenderWaiver($validator, $values)) $errors['e_tender_waiver'] = $eTenderWaiverError;
        if($eAuctionWaiverError = $this->validateEAuctionWaiver($validator, $values)) $errors['e_auction_waiver'] = $eAuctionWaiverError;

        if(count($errors) > 0) throw new sfValidatorErrorSchema($validator, $errors);

        return $values;
    }

    public function doSave($conn = null)
    {
        if($this->isEProjectInPostContract())
        {
            $eProjectPostContractInfo = $this->project->MainInformation->EProjectProject->getPostContractInfo();
            $this->object->contract_period_from = $eProjectPostContractInfo->commencement_date;
            $this->object->contract_period_to = $eProjectPostContractInfo->completion_date;
            $this->object->pre_defined_location_code_id = $eProjectPostContractInfo->pre_defined_location_code_id;
        }

        $values = $this->getValues();

        parent::doSave($conn);

        $object = $this->object;

        // Trigger signable and timestamp column updates, as it only happens when the object is dirty. This value will be overwritten.
        $eTenderWaiverIsChecked = ($values['e_tender_waiver'] === true);
        $eAuctionWaiverIsChecked = ($values['e_auction_waiver'] === true);

        $object->e_tender_waiver_option_type = $eTenderWaiverIsChecked ? $values['eTenderWaiverOption'] : null;
        $object->e_auction_waiver_option_type = $eAuctionWaiverIsChecked ? $values['eAuctionWaiverOption'] : null;
        $object->updated_by = 0;
        $object->save();

        $updatedOtherWaiverOptions = [];

        if($object->e_tender_waiver_option_type== NewPostContractFormInformation::E_TENDER_WAIVER_OPTION_OTHERS)
        {
            WaiverUserDefinedOption::addOrUpdateEntry($this->project, $values['eTenderWaiverOption'], $values['eTenderWaiverUserDefinedOption']);
            array_push($updatedOtherWaiverOptions, $values['eTenderWaiverOption']);
        }
 
        if($object->e_auction_waiver_option_type == NewPostContractFormInformation::E_AUCTION_WAIVER_OPTION_OTHERS)
        {
            WaiverUserDefinedOption::addOrUpdateEntry($this->project, $values['eAuctionWaiverOption'], $values['eAuctionWaiverUserDefinedOption']);
            array_push($updatedOtherWaiverOptions, $values['eAuctionWaiverOption']);
        }

        WaiverUserDefinedOption::deleteEntry($this->project, $updatedOtherWaiverOptions);

        $object->refresh();

        $works = array();

        $works[SubPackageWorks::TYPE_1] = $values['works_1'];
        $works[SubPackageWorks::TYPE_2] = $values['works_2'];

        NewPostContractFormInformationTable::saveSubPackageWorks($object->id, $works);

        $project = $this->project;

        if($project->MainInformation->EProjectProject && !$this->isEProjectInPostContract())
        {
            $EProject = $project->MainInformation->EProjectProject;
            $contract = $EProject->getPostContractInfo();

            if($contract)
            {
                $contract->contract_sum = ProjectStructureTable::getContractSumByProjectId($project->id, $this->estimationRate, $this->withNotListedItems);
                $contract->pre_defined_location_code_id = $item->pre_defined_location_code_id;

                $contract->save();
            }

            EProjectProjectLabourRateTable::updateProjectLabourRates($project, array(
                'normal_working_hours' => $values['labour_rate_hours'],
                'skilled' => array(
                    'normal_rate_per_hour' => $values['labour_rate_skilled_normal'],
                    'ot_rate_per_hour' => $values['labour_rate_skilled_ot'],
                ),
                'semi_skilled' => array(
                    'normal_rate_per_hour' => $values['labour_rate_semi_skilled_normal'],
                    'ot_rate_per_hour' => $values['labour_rate_semi_skilled_ot'],
                ),
                'labour' => array(
                    'normal_rate_per_hour' => $values['labour_rate_labour_normal'],
                    'ot_rate_per_hour' => $values['labour_rate_labour_ot'],
                ),
                'trade' => $values['pre_defined_location_code_id'],
                'contractor' => $project->getSelectedContractor()->getEProjectCompany()->id
            ));
        }
    }
}
