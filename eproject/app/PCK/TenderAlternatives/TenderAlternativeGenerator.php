<?php namespace PCK\TenderAlternatives;

use PCK\Helpers\NumberToTextConverter;
use PCK\Helpers\StringOperations;
use PCK\Tenders\Tender;
use PCK\Tenders\SubmitTenderRate;
use PCK\TenderAlternatives\TenderAlternativeOne;
use PCK\TenderAlternatives\TenderAlternativeFive;
use PCK\Tenders\CompanyTender;

class TenderAlternativeGenerator {

    private $tender;

    private $tenderRate;

    const BLANK_INPUT                   = '______';
    const CURRENCY_NAME_PLACEHOLDER     = '[currency name]';
    const TAX_NAME_PLACEHOLDER          = '[tax name]';
    const EMPTY_PLACEHOLDER             = '-';
    const DEVELOPER_TENDER_ALTERNATIVE  = 0;
    const CONTRACTOR_TENDER_ALTERNATIVE = 1;

    const PROJECT_PERIOD_BY_DEV_TAG = 'projectPeriodByDeveloper';
    const PROJECT_PERIOD_METRIC_TAG = 'projectPeriodMetric';
    const PROJECT_MONTHS_BY_CONTRACTOR_TAG = 'projectMonthsByContractor';
    const PROJECT_INCENTIVE_TAG = 'projectIncentive';
    const CONTRACTOR_INCENTIVE_TAG = 'contractorIncentive';
    const CURRENCY_NAME_TAG = 'currencyName';
    const TAX_PERCENTAGE_TAG = 'taxPercentage';
    const TAX_NAME_TAG = 'taxName';

    public function __construct(Tender $tender, SubmitTenderRate $tenderRate = null)
    {
        $this->tender     = $tender;
        $this->tenderRate = $tenderRate;
    }

    public function getTags($withPrefix = false)
    {
        $prefix = $withPrefix?'@':'';

        return array(
            'projectPeriodByDeveloper'  => $prefix.self::PROJECT_PERIOD_BY_DEV_TAG,
            'projectPeriodMetric'       => $prefix.self::PROJECT_PERIOD_METRIC_TAG,
            'projectMonthsByContractor' => $prefix.self::PROJECT_MONTHS_BY_CONTRACTOR_TAG,
            'projectIncentive'          => $prefix.self::PROJECT_INCENTIVE_TAG,
            'contractorIncentive'       => $prefix.self::CONTRACTOR_INCENTIVE_TAG,
            'currencyName'              => $prefix.self::CURRENCY_NAME_TAG,
            'taxPercentage'             => $prefix.self::TAX_PERCENTAGE_TAG,
            'taxName'                   => $prefix.self::TAX_NAME_TAG,
        );
    }

    /**
     * Generates a tender alternative with a blank description (i.e. without any predetermined values).
     *
     * @param $tenderAlternativeClass
     *
     * @return string
     */
    public function generateWithBlankDescription($tenderAlternativeClass, $tenderAlternativeCustomDescription)
    {
        $tenderAlternativeClass = \App::make($tenderAlternativeClass);

        $data = $this->getTags(true);

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();

        $data['includeTax'] = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;

        return \View::make('tender_alternatives.main', array('viewName' => $tenderAlternativeClass->getViewName(), 'viewData' => $data, 'tenderAlternativeCustomDescription' => $tenderAlternativeCustomDescription))->render();
    }

    /**
     * Generates all Tender Alternatives with blank descriptions.
     *
     * @param array $tenderAlternativeClasses
     *
     * @return array
     */
    public function generateAllWithBlankDescriptions($tenderAlternativeClasses = array())
    {
        $data = array();

        if( ! isset( $tenderAlternativeClasses ) )
        {
            $tenderAlternativeClasses = TenderAlternativeList::$list;
        }

        foreach($tenderAlternativeClasses as $tenderAlternativeClass)
        {
            $tenderAlternativeClass = \App::make($tenderAlternativeClass);

            $description = \View::make($tenderAlternativeClass->getViewName(), array(
                'projectPeriodByDeveloper'  => self::BLANK_INPUT,
                'projectPeriodMetric'       => self::BLANK_INPUT,
                'projectMonthsByContractor' => self::BLANK_INPUT,
                'projectIncentive'          => self::BLANK_INPUT,
                'contractorIncentive'       => self::BLANK_INPUT,
                'currencyName'              => self::CURRENCY_NAME_PLACEHOLDER,
                'includeTax'                => true,
                'taxPercentage'             => self::BLANK_INPUT,
                'taxName'                   => self::TAX_NAME_PLACEHOLDER,
            ))->render();

            //to follow implementation in self::generateAllBeforeContractorInput() since this might be be called using the same view layout
            $tenderAlternatives[0] = [
                'description' => $description,
                'amount'      => 0,
            ];

            $data[] = $tenderAlternatives;
        }

        return $data;
    }

    /**
     * Generates a Tender Alternative with a description with details of the tender, but without details from the contractor.
     *
     * @param $tenderAlternativeClass
     *
     * @return string
     */
    public function generateBeforeContractorInput($tenderAlternativeClass)
    {
        $tenderAlternativeClass = \App::make($tenderAlternativeClass);

        $projectPeriodByDeveloper = self::BLANK_INPUT;
        $projectPeriodMetric      = self::BLANK_INPUT;
        $projectIncentive         = self::BLANK_INPUT;

        if( isset( $this->tender->listOfTendererInformation ) )
        {
            $projectPeriodByDeveloper = $this->tender->listOfTendererInformation->completion_period;
            $projectPeriodMetric      = $this->tender->project->completion_period_metric;
            $projectIncentive         = $this->tender->listOfTendererInformation->project_incentive_percentage;
        }

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();
        $includeTax = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;
        $taxPercentage = is_null($bsProjectSummaryGeneralSettings) ? self::BLANK_INPUT : number_format($bsProjectSummaryGeneralSettings->tax_percentage, 2, '.', ',');
        $taxName = is_null($bsProjectSummaryGeneralSettings) ? self::TAX_NAME_PLACEHOLDER : $bsProjectSummaryGeneralSettings->tax_name;

        return \View::make($tenderAlternativeClass->getViewName(), array(
            'projectPeriodByDeveloper'  => $projectPeriodByDeveloper,
            'projectPeriodMetric'       => $projectPeriodMetric,
            'projectMonthsByContractor' => self::BLANK_INPUT,
            'projectIncentive'          => $projectIncentive,
            'contractorIncentive'       => self::BLANK_INPUT,
            'currencyName'              => $this->tender->project->country->currency_name,
            'includeTax'                => $includeTax,
            'taxPercentage'             => $taxPercentage,
            'taxName'                   => $taxName,
        ))->render();
    }

    /**
     * Generates all Tender Alternatives with descriptions with details of the tender, but without details from the contractor.
     *
     * @param null $tenderAlternativeClasses
     *
     * @return array
     */
    public function generateAllBeforeContractorInput($tenderAlternativeClasses = null)
    {
        return $this->generateAllBeforeContractorInput2($tenderAlternativeClasses);
        /*$data = [];

        $projectPeriodByDeveloper = self::BLANK_INPUT;
        $projectPeriodMetric      = self::BLANK_INPUT;
        $projectIncentive         = self::BLANK_INPUT;

        if( isset( $this->tender->listOfTendererInformation ) || ( $this->tender->listOfTendererInformation != null ) )
        {
            $projectPeriodByDeveloper = $this->tender->listOfTendererInformation->completion_period;
            $projectPeriodMetric      = $this->tender->project->completion_period_metric;
            $projectIncentive         = $this->tender->listOfTendererInformation->project_incentive_percentage;
        }

        if( ! isset( $tenderAlternativeClasses ) )
        {
            $tenderAlternativeClasses = TenderAlternativeList::$list;
        }

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();
        $includeTax = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;
        $taxPercentage = is_null($bsProjectSummaryGeneralSettings) ? self::BLANK_INPUT : number_format($bsProjectSummaryGeneralSettings->tax_percentage, 2, '.', ',');
        $taxName = is_null($bsProjectSummaryGeneralSettings) ? self::TAX_NAME_PLACEHOLDER : $bsProjectSummaryGeneralSettings->tax_name;

        foreach($tenderAlternativeClasses as $tenderAlternativeClass)
        {
            $tenderAlternativeClassName = $tenderAlternativeClass;

            $tenderAlternativeClass = \App::make($tenderAlternativeClass);

            $contractorInputValues = $this->prepareContractorInputValues();

            $alternatives = [];
            foreach($contractorInputValues as $contractorValue)
            {
                $description = \View::make($tenderAlternativeClass->getViewName(), array(
                    'projectPeriodByDeveloper'  => $projectPeriodByDeveloper,
                    'projectPeriodMetric'       => $projectPeriodMetric,
                    'projectMonthsByContractor' => self::BLANK_INPUT,
                    'projectIncentive'          => $projectIncentive,
                    'contractorIncentive'       => self::BLANK_INPUT,
                    'currencyName'              => $this->tender->project->country->currency_name,
                    'includeTax'                => $includeTax,
                    'taxPercentage'             => $taxPercentage,
                    'taxName'                   => $taxName,
                ))->render();

                $alternatives[] = [
                    'tender_alternative_id'         => $contractorValue['tender_alternative_id'],
                    'tender_alternative_title'      => $contractorValue['title'],
                    'tender_alternative_is_awarded' => $contractorValue['is_awarded'],
                    'description'                   => !empty($contractorValue['title']) ? '<strong>'.$contractorValue['title'].' :</strong> '.$description : $description,
                    'amount'                        => 0,
                    'period'                        => $this->getPeriodByTenderAlternative($tenderAlternativeClassName, $projectPeriodByDeveloper, 0),
                    'amountInText'                  => StringOperations::wrapToArray("-"),
                ];
            }

            $data[] = $alternatives;
        }

        return $data;*/
    }

    /**
     * Generates all Tender Alternatives with descriptions with details of the tender, but without details from the contractor.
     *
     * @param null $tenderAlternatives
     *
     * @return array
     */
    public function generateAllBeforeContractorInput2($tenderAlternatives = null)
    {
        $data = [];

        $projectPeriodByDeveloper = self::BLANK_INPUT;
        $projectPeriodMetric      = self::BLANK_INPUT;
        $projectIncentive         = self::BLANK_INPUT;

        if( isset( $this->tender->listOfTendererInformation ) || ( $this->tender->listOfTendererInformation != null ) )
        {
            $projectPeriodByDeveloper = $this->tender->listOfTendererInformation->completion_period;
            $projectPeriodMetric      = $this->tender->project->completion_period_metric;
            $projectIncentive         = $this->tender->listOfTendererInformation->project_incentive_percentage;
        }

        /*if( ! isset( $tenderAlternatives ) )
        {
            $tenderAlternatives = TenderAlternativeList::$list;
        }*/

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();
        $includeTax = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;
        $taxPercentage = is_null($bsProjectSummaryGeneralSettings) ? self::BLANK_INPUT : number_format($bsProjectSummaryGeneralSettings->tax_percentage, 2, '.', ',');
        $taxName = is_null($bsProjectSummaryGeneralSettings) ? self::TAX_NAME_PLACEHOLDER : $bsProjectSummaryGeneralSettings->tax_name;

        $viewData = $this->getTags(true);
        $viewData['includeTax'] = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;

        $taData = array(
            'projectPeriodByDeveloper'  => $projectPeriodByDeveloper,
            'projectPeriodMetric'       => $projectPeriodMetric,
            'projectMonthsByContractor' => self::BLANK_INPUT,
            'projectIncentive'          => $projectIncentive,
            'contractorIncentive'       => self::BLANK_INPUT,
            'currencyName'              => $this->tender->project->country->currency_name,
            'includeTax'                => $includeTax,
            'taxPercentage'             => $taxPercentage,
            'taxName'                   => $taxName
        );

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderAlternativeClassName = $tenderAlternative->tender_alternative_class_name;

            $tenderAlternativeClass = \App::make($tenderAlternativeClassName);

            $contractorInputValues = $this->prepareContractorInputValues();

            $alternatives = [];
            foreach($contractorInputValues as $contractorValue)
            {
                $description = \View::make('tender_alternatives.main', array(
                    'viewName' => $tenderAlternativeClass->getViewName(),
                    'viewData' => $viewData,
                    'tenderAlternativeCustomDescription' => $tenderAlternative->custom_description
                ))->render();

                foreach ($taData as $key => $value) {
                    $description = str_replace("@" . $key, $value, $description); // Camelcase
                    $description = str_replace("@" . strtoupper($key), $value, $description); // Uppercase
                    $description = str_replace("@" . strtolower($key), $value, $description); // Lowercase
                }

                $alternatives[] = [
                    'tender_alternative_id'         => $contractorValue['tender_alternative_id'],
                    'tender_alternative_title'      => $contractorValue['title'],
                    'tender_alternative_is_awarded' => $contractorValue['is_awarded'],
                    'description'                   => !empty($contractorValue['title']) ? '<strong>'.$contractorValue['title'].' :</strong> '.$description : $description,
                    'amount'                        => 0,
                    'period'                        => $this->getPeriodByTenderAlternative($tenderAlternativeClassName, $projectPeriodByDeveloper, 0),
                    'amountInText'                  => StringOperations::wrapToArray("-"),
                ];
            }

            $data[] = $alternatives;
        }

        return $data;
    }

    /**
     * Generates selected tender alternatives with all details.
     *
     * @param null $tenderAlternativeClasses
     *
     * @return array
     */
    public function generateAllAfterContractorInput($tenderAlternatives = null)
    {
        /*if( is_null($tenderAlternativeClasses) )
        {
            $tenderAlternativeClasses = TenderAlternativeList::$list;
        }*/

        $data = [];

        $projectPeriodByDeveloper   = $this->tender->listOfTendererInformation->completion_period;
        $projectPeriodMetric        = $this->tender->project->completion_period_metric;

        $projectIncentive = $this->tender->listOfTendererInformation->project_incentive_percentage;

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();

        $viewData = $this->getTags(true);
        $viewData['includeTax'] = is_null($bsProjectSummaryGeneralSettings) ? false : $bsProjectSummaryGeneralSettings->include_tax;

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderAlternativeClassName = $tenderAlternative->tender_alternative_class_name;

            $tenderAlternativeClass = \App::make($tenderAlternativeClassName);

            $contractorInputValues = $this->prepareContractorInputValues();

            $alternatives = array();
            foreach($contractorInputValues as $contractorValue)
            {
                $tenderAlternativeCalculator = $contractorValue['tenderAlternativeCalculator'];
                $projectMonthsByContractor   = $contractorValue['projectMonthsByContractor'];
                $contractorIncentive         = $contractorValue['contractorIncentive'];
                $contractorAdjustmentAmount  = $contractorValue['contractorAdjustmentAmount'];
                $hasAlternateProposal        = $contractorValue['hasAlternateProposal'];

                $totalAmount = $tenderAlternativeCalculator->getTotalAmount($tenderAlternativeClass);
                $totalAmount = $this->tender->project->country->currencySetting->getRoundedAmount($totalAmount);

                if( ( self::getTenderAlternativeType($tenderAlternativeClassName) == self::CONTRACTOR_TENDER_ALTERNATIVE ) && ( ! $hasAlternateProposal ) )
                {
                    $totalAmount = 0.0;
                }

                $amountInText = ($totalAmount == 0.0) ? '-' : NumberToTextConverter::spellCurrencyAmount($totalAmount, $this->tender->project->modified_currency_name);

                $this->formatDetails($tenderAlternativeClassName, $projectMonthsByContractor, $contractorIncentive, $projectIncentive, $totalAmount, $amountInText);

                $description = \View::make('tender_alternatives.main', array(
                    'viewName' => $tenderAlternativeClass->getViewName(),
                    'viewData' => $viewData,
                    'tenderAlternativeCustomDescription' => $tenderAlternative->custom_description
                ))->render();

                $taData = array(
                    'projectPeriodByDeveloper'  => $projectPeriodByDeveloper,
                    'projectPeriodMetric'       => $projectPeriodMetric,
                    'projectMonthsByContractor' => $projectMonthsByContractor,
                    'projectIncentive'          => $projectIncentive,
                    'contractorIncentive'       => number_format($tenderAlternativeCalculator->getAdjustmentPercentage(), 2),
                    'currencyName'              => $this->tender->project->country->currency_name,
                    'includeTax'                => $bsProjectSummaryGeneralSettings->include_tax,
                    'taxPercentage'             => number_format($bsProjectSummaryGeneralSettings->tax_percentage, 2, '.', ','),
                    'taxName'                   => $bsProjectSummaryGeneralSettings->tax_name
                );

                foreach ($taData as $key => $value) {
                    $description = str_replace("@" . $key, $value, $description); // Camelcase
                    $description = str_replace("@" . strtoupper($key), $value, $description); // Uppercase
                    $description = str_replace("@" . strtolower($key), $value, $description); // Lowercase
                }

                $alternatives[] = [
                    'tender_alternative_id'         => $contractorValue['tender_alternative_id'],
                    'tender_alternative_title'      => $contractorValue['title'],
                    'tender_alternative_is_awarded' => $contractorValue['is_awarded'],
                    'description'                   => !empty($contractorValue['title']) ? '<strong>'.$contractorValue['title'].' :</strong> '.$description : $description,
                    'amount'                        => $totalAmount,
                    'amountInText'                  => StringOperations::wrapToArray($amountInText),
                    'period'                        => $this->getPeriodByTenderAlternative($tenderAlternativeClassName, $projectPeriodByDeveloper, $projectMonthsByContractor),
                ];
            }

            $data[] = $alternatives;
        }

        return $data;
    }

    protected function prepareContractorInputValues()
    {
        $bsProjectMainInformation = $this->tender->project->getBsProjectMainInformation();

        $hasBsTenderAlternative = ($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count());

        $bsProjectSummaryGeneralSettings = $this->getBuildSpaceProjectSummaryGeneralSettings();

        $returnData = [];

        if($hasBsTenderAlternative)
        {
            $companyTender = ($this->tenderRate) ? CompanyTender::find($this->tenderRate->id) : null;
            $submittedTenderAlternatives = ($companyTender) ? $companyTender->tenderAlternatives->keyBy('tender_alternative_id')->all() : [];

            foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $key => $tenderAlternative)
            {
                $projectMonthsByContractor  = 0;
                $contractorIncentive        = 0;
                $contractorAdjustmentAmount = 0;

                $valueA = 0;
                $valueB = 0;
                $valueC = 0;
                $valueD = isset( $this->tender->listOfTendererInformation->project_incentive_percentage ) ? $this->tender->listOfTendererInformation->project_incentive_percentage : 0;
                $valueE = $contractorIncentive;
                $valueF = $contractorAdjustmentAmount;
                $valueG = ($bsProjectSummaryGeneralSettings and $bsProjectSummaryGeneralSettings->include_tax) ? $bsProjectSummaryGeneralSettings->tax_percentage : 0;

                $tenderAlternativeCalculator = \App::make('PCK\TenderAlternatives\Calculator');

                if(array_key_exists($tenderAlternative->id, $submittedTenderAlternatives))
                {
                    $submittedTenderAlternative = $submittedTenderAlternatives[$tenderAlternative->id];

                    $projectMonthsByContractor  = $submittedTenderAlternative->completion_period + 0;
                    $contractorIncentive        = $submittedTenderAlternative->contractor_adjustment_percentage;
                    $contractorAdjustmentAmount = $submittedTenderAlternative->contractor_adjustment_amount;

                    $valueA = isset( $submittedTenderAlternative->tender_amount ) ? $submittedTenderAlternative->tender_amount : 0;
                    $valueB = isset( $submittedTenderAlternative->supply_of_material_amount ) ? $submittedTenderAlternative->supply_of_material_amount : 0;
                    $valueC = isset( $submittedTenderAlternative->other_bill_type_amount_except_prime_cost_provisional ) ? $submittedTenderAlternative->other_bill_type_amount_except_prime_cost_provisional : 0;
                    $valueE = isset( $contractorIncentive ) ? $contractorIncentive : 0;
                    $valueF = isset( $contractorAdjustmentAmount ) ? $contractorAdjustmentAmount : 0;

                    unset($submittedTenderAlternatives[$tenderAlternative->id]);
                }

                $tenderAlternativeCalculator->setValueA($valueA);
                $tenderAlternativeCalculator->setValueB($valueB);
                $tenderAlternativeCalculator->setValueC($valueC);
                $tenderAlternativeCalculator->setValueD($valueD);
                $tenderAlternativeCalculator->setValueE($valueE);
                $tenderAlternativeCalculator->setValueF($valueF);
                $tenderAlternativeCalculator->setValueG($valueG);

                $hasAlternateProposal = ($this->tenderRate) ? $this->tenderRate->company->hasAlternateProposal($this->tender, $tenderAlternative->id) : false;

                $returnData[] = [
                    'tender_alternative_id'       => $tenderAlternative->id,
                    'title'                       => "(".NumberToTextConverter::numberToRoman($key+1).") ".$tenderAlternative->title,
                    'is_awarded'                  => $tenderAlternative->is_awarded,
                    'tenderAlternativeCalculator' => $tenderAlternativeCalculator,
                    'projectMonthsByContractor'   => $projectMonthsByContractor,
                    'contractorIncentive'         => $contractorIncentive,
                    'contractorAdjustmentAmount'  => $contractorAdjustmentAmount,
                    'hasAlternateProposal'        => $hasAlternateProposal
                ];
            }
        }
        else
        {
            $projectMonthsByContractor  = ($this->tenderRate) ? $this->tenderRate->completion_period + 0 : 0;
            $contractorIncentive        = ($this->tenderRate) ? $this->tenderRate->contractor_adjustment_percentage : 0;
            $contractorAdjustmentAmount = ($this->tenderRate) ? $this->tenderRate->contractor_adjustment_amount : 0;

            $tenderAlternativeCalculator = \App::make('PCK\TenderAlternatives\Calculator');

            $valueA = ($this->tenderRate) ? $this->tenderRate->tender_amount : 0;
            $valueB = ($this->tenderRate) ? $this->tenderRate->supply_of_material_amount : 0;
            $valueC = ($this->tenderRate) ? $this->tenderRate->other_bill_type_amount_except_prime_cost_provisional : 0;
            $valueD = isset( $this->tender->listOfTendererInformation->project_incentive_percentage ) ? $this->tender->listOfTendererInformation->project_incentive_percentage : 0;
            $valueE = $contractorIncentive;
            $valueF = $contractorAdjustmentAmount;
            $valueG = ($bsProjectSummaryGeneralSettings and $bsProjectSummaryGeneralSettings->include_tax) ? $bsProjectSummaryGeneralSettings->tax_percentage : 0;

            $tenderAlternativeCalculator->setValueA($valueA);
            $tenderAlternativeCalculator->setValueB($valueB);
            $tenderAlternativeCalculator->setValueC($valueC);
            $tenderAlternativeCalculator->setValueD($valueD);
            $tenderAlternativeCalculator->setValueE($valueE);
            $tenderAlternativeCalculator->setValueF($valueF);
            $tenderAlternativeCalculator->setValueG($valueG);

            $hasAlternateProposal = ($this->tenderRate) ? $this->tenderRate->company->hasAlternateProposal($this->tender) : false;

            $returnData[] = [
                'tender_alternative_id'       => -1,
                'title'                       => '',
                'is_awarded'                  => true, //tender without tender alternative is considered awarded since we only check for the selected tenderer as an awarded tenderer
                'tenderAlternativeCalculator' => $tenderAlternativeCalculator,
                'projectMonthsByContractor'   => $projectMonthsByContractor,
                'contractorIncentive'         => $contractorIncentive,
                'contractorAdjustmentAmount'  => $contractorAdjustmentAmount,
                'hasAlternateProposal'        => $hasAlternateProposal
            ];
        }

        return $returnData;
    }

    /**
     * Gets the number of months (completion period) of the tender alternative.
     *
     * @param $tenderAlternativeClassName
     * @param $periodByDeveloper
     * @param $periodByContractor
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPeriodByTenderAlternative($tenderAlternativeClassName, $periodByDeveloper, $periodByContractor)
    {
        if( self::getTenderAlternativeType($tenderAlternativeClassName) == self::DEVELOPER_TENDER_ALTERNATIVE )
        {
            $period = $periodByDeveloper;
        }
        elseif( self::getTenderAlternativeType($tenderAlternativeClassName) == self::CONTRACTOR_TENDER_ALTERNATIVE )
        {
            $period = $periodByContractor;
        }
        else
        {
            throw new \Exception("No resource with that name exists");
        }

        return $period;
    }

    /**
     * Format output depending on whether the completion period is keyed in,
     * only for contractor tender alternatives.
     *
     * @param $tenderAlternativeClassName
     * @param $projectMonthsByContractor
     * @param $contractorIncentive
     * @param $projectIncentive
     * @param $totalAmount
     * @param $amountInText
     *
     * @throws \Exception
     */
    private function formatDetails($tenderAlternativeClassName, &$projectMonthsByContractor, &$contractorIncentive, &$projectIncentive, &$totalAmount, &$amountInText)
    {
        if( self::getTenderAlternativeType($tenderAlternativeClassName) == self::DEVELOPER_TENDER_ALTERNATIVE )
        {
            return;
        }

        if( empty( $this->tenderRate->completion_period ) )
        {
            $projectMonthsByContractor = self::EMPTY_PLACEHOLDER;
            $contractorIncentive       = self::EMPTY_PLACEHOLDER;
            $projectIncentive          = self::EMPTY_PLACEHOLDER;
            $totalAmount               = 0;
            $amountInText              = self::EMPTY_PLACEHOLDER;
        }
    }

    public static function contractorTenderAlternatives()
    {
        return array(
            'PCK\TenderAlternatives\TenderAlternativeFive',
            'PCK\TenderAlternatives\TenderAlternativeTwelve',
            'PCK\TenderAlternatives\TenderAlternativeSix',
            'PCK\TenderAlternatives\TenderAlternativeSeven',
            'PCK\TenderAlternatives\TenderAlternativeEight',
            'PCK\TenderAlternatives\TenderAlternativeEleven',
        );
    }

    public static function developerTenderAlternatives()
    {
        return array(
            'PCK\TenderAlternatives\TenderAlternativeOne',
            'PCK\TenderAlternatives\TenderAlternativeTwo',
            'PCK\TenderAlternatives\TenderAlternativeThree',
            'PCK\TenderAlternatives\TenderAlternativeFour',
            'PCK\TenderAlternatives\TenderAlternativeNine',
            'PCK\TenderAlternatives\TenderAlternativeTen',
        );
    }

    /**
     * Returns the type of tender alternative.
     *
     * @param $tenderAlternativeClassName
     *
     * @return int
     * @throws \Exception
     */
    public static function getTenderAlternativeType($tenderAlternativeClassName)
    {
        if( in_array($tenderAlternativeClassName, self::developerTenderAlternatives()) )
        {
            return self::DEVELOPER_TENDER_ALTERNATIVE;
        }
        elseif( in_array($tenderAlternativeClassName, self::contractorTenderAlternatives()) )
        {
            return self::CONTRACTOR_TENDER_ALTERNATIVE;
        }
        else
        {
            throw new \Exception("No resource with that name exists");
        }
    }
    
    private function getBuildSpaceProjectSummaryGeneralSettings()
    {
        $bsProject = $this->tender->project;
        if (empty($bsProject)) {
            return null;
        }
        $bsProjectMainInformation = $bsProject->getBsProjectMainInformation();
        if (empty($bsProjectMainInformation)) {
            return null;
        }

        $bsProjectStructureId = $bsProjectMainInformation->project_structure_id;
        $query = "SELECT * FROM bs_project_summary_general_settings WHERE project_structure_id = {$bsProjectStructureId}";
        $bsProjectSummaryGeneralSettings = \DB::connection('buildspace')->select(\DB::raw($query));

        if(empty($bsProjectSummaryGeneralSettings))
        {
            return null;
        }

        return $bsProjectSummaryGeneralSettings[0];
    }
}