<?php namespace PCK\TechnicalAssessments;

use PCK\Base\BaseModuleRepository;
use PCK\TendererTechnicalEvaluationInformation\TechnicalEvaluation;
use PCK\Tenders\Tender;

class TechnicalAssessmentRepository extends BaseModuleRepository {

    public function createOrUpdateSubmittedTechnicalAssessmentData(Tender $tender, $input)
    {
        if( is_null($tender->technicalEvaluation) )
        {
            $object            = new TechnicalEvaluation();
            $object->tender_id = $tender->id;
        }
        else
        {
            $object = $tender->technicalEvaluation;
        }

        $object->targeted_date_of_award = $input['targeted_date_of_award'];
        $object->remarks                = $input['remarks'];
        $object->submitted_by           = \Confide::user()->id;
        $object->save();

        $this->saveAttachments($object, $input);

        return $object;
    }

}