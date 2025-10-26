<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Collection;
use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\TenderAlternatives\TenderAlternativeGenerator;
use PCK\TenderAlternatives\TenderAlternativeList;
use PCK\Tenders\Tender;

trait TenderAlternativeRepositoryTrait
{
    /**
     * Find Tender Alternatives by tender id.
     *
     * @param $formOfTenderId
     *
     * @return mixed
     */
    private function findTenderAlternativesByFormOfTenderId($formOfTenderId)
    {
        $query = TenderAlternative::where('form_of_tender_id', '=', $formOfTenderId);

        $query = self::addFilterTenderAlternatives($query);

        $query = self::addRawOrderByClause($query);

        return $query->get();
    }

    /**
     * Find Tender Alternatives (with show=true) by tender id.
     *
     * @param $formOfTenderId
     *
     * @return mixed
     */
    public function getIncludedTenderAlternativesByFormOfTenderId($formOfTenderId)
    {
        $query = TenderAlternative::where('form_of_tender_id', '=', $formOfTenderId)->where('show', '=', true);

        $query = self::addFilterTenderAlternatives($query);

        $query = self::addRawOrderByClause($query);

        return $query->get();
    }

    private static function addFilterTenderAlternatives($query)
    {
        return $query->whereIn('tender_alternative_class_name', array(
            'PCK\TenderAlternatives\TenderAlternativeOne',          // 1
            'PCK\TenderAlternatives\TenderAlternativeTen',          // 2
            'PCK\TenderAlternatives\TenderAlternativeNine',         // 3
            'PCK\TenderAlternatives\TenderAlternativeTwo',          // 4
            /*'PCK\TenderAlternatives\TenderAlternativeThree',*/    // 5
            /*'PCK\TenderAlternatives\TenderAlternativeFour',*/     // 6
            'PCK\TenderAlternatives\TenderAlternativeFive',         // 7
            'PCK\TenderAlternatives\TenderAlternativeTwelve',       // 8
            'PCK\TenderAlternatives\TenderAlternativeEleven',       // 9
            /*'PCK\TenderAlternatives\TenderAlternativeSix',*/      // 10
            /*'PCK\TenderAlternatives\TenderAlternativeSeven',*/    // 11
            /*'PCK\TenderAlternatives\TenderAlternativeEight',*/    // 12
        ));
    }

    private static function addRawOrderByClause($query)
    {
        return $query->orderByRaw('(
                tender_alternative_class_name=?,
                tender_alternative_class_name=?,
                tender_alternative_class_name=?,
                tender_alternative_class_name=?,
                tender_alternative_class_name=?,
                tender_alternative_class_name=?,
                tender_alternative_class_name=?) DESC', array(
            'PCK\TenderAlternatives\TenderAlternativeOne',          // 1
            'PCK\TenderAlternatives\TenderAlternativeTen',          // 2
            'PCK\TenderAlternatives\TenderAlternativeNine',         // 3
            'PCK\TenderAlternatives\TenderAlternativeTwo',          // 4
            /*'PCK\TenderAlternatives\TenderAlternativeThree',*/    // 5
            /*'PCK\TenderAlternatives\TenderAlternativeFour',*/     // 6
            'PCK\TenderAlternatives\TenderAlternativeFive',         // 7
            'PCK\TenderAlternatives\TenderAlternativeTwelve',       // 8
            'PCK\TenderAlternatives\TenderAlternativeEleven',       // 9
            /*'PCK\TenderAlternatives\TenderAlternativeSix',*/      // 10
            /*'PCK\TenderAlternatives\TenderAlternativeSeven',*/    // 11
            /*'PCK\TenderAlternatives\TenderAlternativeEight',*/    // 12
        ));
    }

    /**
     * Saves/Updates the position of tender alternatives relative to the regular clauses.
     *
     * @param       $formOfTenderId
     * @param array $arrayOfPositions
     * @param bool  $isTemplate
     */
    private function saveTenderAlternativesPositions($formOfTenderId, array $arrayOfPositions)
    {
        $this->deletePreviousTenderAlternativesPositions($formOfTenderId);

        //save new positions
        foreach($arrayOfPositions as $positionIndex)
        {
            $newPosition                    = new TenderAlternativesPosition;
            $newPosition->form_of_tender_id = $formOfTenderId;
            $newPosition->position          = $positionIndex;
            $newPosition->save();
        }
    }

    /**
     * Copies the Tender Alternative Position(s) from the template.
     *
     * @param $template
     * @param $formOfTenderId
     *
     * @return Collection
     */
    private function copyTenderAlternativesPositionsFrom($template, $formOfTenderId)
    {
        $positions = new Collection();

        foreach($template as $position)
        {
            $resource                    = $position->replicate();
            $resource->form_of_tender_id = $formOfTenderId;
            $resource->save();
            $positions->push($resource);
        }

        return $positions;
    }

    /**
     * Copies new Tender Alternatives from the template Tender Alternatives.
     *
     * @param $template
     * @param $tenderId
     *
     * @return array
     */
    private function copyTenderAlternativesFrom($template, $tenderId)
    {
        $newTenderAlternatives = array();

        foreach($template as $tenderAlternative)
        {
            $newTenderAlternative              = $tenderAlternative->replicate();
            $newTenderAlternative->tender_id   = $tenderId;
            $newTenderAlternative->is_template = false;
            $newTenderAlternative->save();

            array_push($newTenderAlternatives, $newTenderAlternative);
        }

        return $newTenderAlternatives;
    }

    /**
     * Get tags for template
     *
     * @return mixed
     */
    public function getTags($tenderId=null)
    {
        if (! is_null($tenderId)) {
            $tender = Tender::find($tenderId);
        } else {
            $tender = new Tender();
        }
        $generator = new TenderAlternativeGenerator($tender);

        return $generator->getTags();
    }

    /**
     * Get the template for all shown tender alternatives descriptions (by Tender ID)
     *
     * @return mixed
     */
    public function getTenderAlternativesDescriptionsByTenderId($tenderId)
    {
        $tender = Tender::find($tenderId);
        $tenderAlternatives = $this->findTenderAlternativesByFormOfTenderId($tender->formOfTender->id);

        $generator = new TenderAlternativeGenerator($tender, $tender->pivot);

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderAlternative->description = $generator->generateWithBlankDescription($tenderAlternative->tender_alternative_class_name, $tenderAlternative->custom_description);
        }

        return $tenderAlternatives;
    }

    /**
     * Get the template for all shown tender alternatives descriptions (by Template ID)
     *
     * @return mixed
     */
    public function getTenderAlternativesDescriptionsByTemplateId($templateId)
    {
        $tenderAlternatives = $this->findTenderAlternativesByFormOfTenderId($templateId);

        $tender = new Tender();

        $generator = new TenderAlternativeGenerator($tender, $tender->pivot);

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderAlternative->description = $generator->generateWithBlankDescription($tenderAlternative->tender_alternative_class_name, $tenderAlternative->custom_description);
        }

        return $tenderAlternatives;
    }

    /**
     * Returns an array of all tender alternatives which are set to show in the form of tender by the tender id.
     *
     * @param $tenderId
     *
     * @return array
     */
    public function getIncludedTenderAlternativesClassNamesByTenderId($tenderId)
    {
        $tender = Tender::find($tenderId);
        $tenderAlternatives = $this->getIncludedTenderAlternativesByFormOfTenderId($tender->formOfTender->id);

        $includedTAClassNames = array();

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $includedTAClassNames[] = $tenderAlternative->tender_alternative_class_name;
        }
        return $includedTAClassNames;
    }

    /**
     * Finds a Tender Alternative resource, or creates one if it does not exist.
     * Also adds the tender alternative description.
     *
     * @param $tenderId
     *
     * @return array|mixed
     */
    public function findTenderAlternativesDescriptionByTenderId($tenderId)
    {
        $tender = Tender::find($tenderId);
        $tenderAlternatives = $this->findTenderAlternativesByFormOfTenderId($tender->formOfTender->id);

        $generator = new TenderAlternativeGenerator($tender, $tender->pivot);

        foreach($tenderAlternatives as $tenderAlternative)
        {
            $tenderAlternative->description = $generator->generateBeforeContractorInput($tenderAlternative->tender_alternative_class_name);
        }

        return $tenderAlternatives;
    }

    /**
     * Get the printed version of template Tender Alternatives.
     *
     * @return array
     */
    public function getPrintTenderAlternativesTemplate($templateId)
    {
        $tender       = new Tender();
        $generator    = new TenderAlternativeGenerator($tender, $tender->pivot);

        $tenderAlternatives = $this->findTenderAlternativesByFormOfTenderId($templateId);

        $includedTenderAlternatives = $this->getIncludedTenderAlternativesClassNames($tenderAlternatives);

        return $generator->generateAllWithBlankDescriptions($includedTenderAlternatives);
    }

    /**
     * Get the printed version of the Tender Alternatives with the tender information, but without the contractor's input.
     *
     * @param $tenderId
     *
     * @return array
     */
    public function getPrintTenderAlternativesBeforeContractorInput($tenderId)
    {
        $tender    = Tender::find($tenderId);
        $generator = new TenderAlternativeGenerator($tender, $tender->pivot);

        $includedTenderAlternatives = $this->getIncludedTenderAlternativesByFormOfTenderId($tender->formOfTender->id);

        return $generator->generateAllBeforeContractorInput($includedTenderAlternatives);
    }

    /**
     * Get the printed version of the Tender Alternatives with the tender information, with the contractor's input if it exists.
     *
     * @param $projectId
     * @param $tenderId
     * @param $companyId
     *
     * @return array
     */
    public function getPrintTenderAlternativesAfterContractorInput($projectId, $tenderId, $companyId)
    {
        $project    = Project::find($projectId);
        $company    = Company::find($companyId);
        $contractor = $this->companyRepository->getTendersByCompanyAndProject($company, $project, $tenderId);
        $tender     = $contractor->tenders->first();

        if( $tender->pivot->submitted )
        {
            $includedTenderAlternatives = $this->getIncludedTenderAlternativesByFormOfTenderId($tender->formOfTender->id);

            $generator = new TenderAlternativeGenerator($tender, $tender->pivot);

            $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);
        }
        else
        {
            $tenderAlternativeData = $this->getPrintTenderAlternativesBeforeContractorInput($tender->id);
        }

        return $tenderAlternativeData;
    }

    /**
     * Updates the Tender Alternatives and logs the update.
     *
     * @param $formOfTenderId
     * @param $isTemplate
     * @param $inputArray
     */
    public function updateTenderAlternatives($formOfTenderId, $inputArray)
    {
        $tenderAlternativeClasses = TenderAlternativeList::$list;

        foreach($inputArray as $tenderAlternativeClassName => $input)
        {
            if( in_array($tenderAlternativeClassName, $tenderAlternativeClasses) )
            {
                $this->setTenderAlternativeToShow($formOfTenderId, $tenderAlternativeClassName);

                //remove from tenderAlternativeClasses
                $key = array_search($tenderAlternativeClassName, $tenderAlternativeClasses);
                unset( $tenderAlternativeClasses[ $key ] );
            }
        }

        $this->setAllTenderAlternativesToHide($formOfTenderId, $tenderAlternativeClasses);

        $this->addLogEntry($formOfTenderId);
    }

    /**
     * Updates the Tender Alternatives description
     *
     * @param $tenderAlternativeId
     * @param $description
     */
    public function updateTenderAlternativesDescription($tenderAlternativeId, $description)
    {
        $tenderAlternative       = TenderAlternative::where('id', '=', $tenderAlternativeId)->first();
        $tenderAlternative->custom_description = $description;
        $tenderAlternative->save();
    }

    /**
     * Returns an array of all tender alternatives which are set to show in the form of tender.
     *
     * @param $tenderAlternatives
     *
     * @return array
     */
    private function getIncludedTenderAlternativesClassNames($tenderAlternatives)
    {
        $includedTenderAlternatives = array();

        foreach($tenderAlternatives as $tenderAlternative)
        {
            if( $tenderAlternative->show ) array_push($includedTenderAlternatives, $tenderAlternative->tender_alternative_class_name);
        }

        return $includedTenderAlternatives;
    }

    /**
     * Sets a specific tender alternative to show in the printed form of tender.
     *
     * @param $formOfTenderId
     * @param $isTemplate
     * @param $key
     */
    private function setTenderAlternativeToShow($formOfTenderId, $key)
    {
        $tenderAlternative       = $this->findTenderAlternative($formOfTenderId, $key);
        $tenderAlternative->show = true;
        $tenderAlternative->save();
    }

    /**
     * Sets all tender alternatives in the array to not be displayed in the printed form of tender.
     *
     * @param $formOfTenderId
     * @param $tenderAlternativeClasses
     */
    private function setAllTenderAlternativesToHide($formOfTenderId, $tenderAlternativeClasses)
    {
        foreach($tenderAlternativeClasses as $tenderAlternativeClass)
        {
            $tenderAlternative = $this->findTenderAlternative($formOfTenderId, $tenderAlternativeClass);
            if (! $tenderAlternative) {
                $tenderAlternative                                = new TenderAlternative();
                $tenderAlternative->form_of_tender_id             = $formOfTenderId;
                $tenderAlternative->tender_alternative_class_name = $tenderAlternativeClass;
            }
            $tenderAlternative->show = false;
            $tenderAlternative->save();
        }
    }

    /**
     * Find a specific Tender Alternative.
     *
     * @param $formOfTenderId
     * @param $tenderAlternativeClassName
     *
     * @return mixed
     */
    private function findTenderAlternative($formOfTenderId, $tenderAlternativeClassName)
    {
        return TenderAlternative::where('form_of_tender_id', '=', $formOfTenderId)
            ->where('tender_alternative_class_name', '=', $tenderAlternativeClassName)
            ->first();
    }

    /**
     * Deletes all previous tender alternatives positions.
     *
     * @param $formOfTenderId
     */
    private function deletePreviousTenderAlternativesPositions($formOfTenderId)
    {
        TenderAlternativesPosition::where('form_of_tender_id', $formOfTenderId)->delete();
    }

    /**
     * Creates a new set of tender alternatives.
     *
     * @param $formOfTenderId
     *
     * @return Collection
     */
    private function createNewEmptyTenderAlternatives($formOfTenderId)
    {
        $resource = new Collection();

        foreach(TenderAlternativeList::$list as $className)
        {
            $tenderAlternative                                = new TenderAlternative();
            $tenderAlternative->form_of_tender_id             = $formOfTenderId;
            $tenderAlternative->tender_alternative_class_name = $className;
            $tenderAlternative->save();

            $resource->push($tenderAlternative);
        }
    }

}