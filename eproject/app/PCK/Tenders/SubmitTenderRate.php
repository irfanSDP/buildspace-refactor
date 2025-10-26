<?php namespace PCK\Tenders;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PCK\Projects\Project;
use PCK\Buildspace\Project as ProjectStructure;
use PCK\Companies\Company;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;

class SubmitTenderRate extends Pivot {

    CONST ratesFileName = 'rates.tr';

    use TimestampFormatterTrait;

    protected $with = array( 'attachments' );

    public function attachments()
    {
        return $this->morphMany('PCK\ModuleUploadedFiles\ModuleUploadedFile', 'uploadable', null, null, 'id')
            ->orderBy('id');
    }

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company');
    }

    /**
     * Returns true if the rates file has been submitted.
     *
     * @return mixed
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }

    public static function getDefaultRateFileName()
    {
        return 'rates';
    }

    public static function getContractorRatesUploadPath(Project $project, Tender $tender, Company $tenderer)
    {
        $encodedFolderName = 'project_' . $project->id . '_tender_' . $tender->id;

        $path = '/upload/rates/' . base64_encode($encodedFolderName) . '/' . $tenderer->id;

        return public_path() . $path;
    }

    public function getSubmittedAtAttribute($value)
    {
        if( is_null($value) )
        {
            return null;
        }

        return Carbon::parse($value)->format(\Config::get('dates.created_and_updated_at_formatting'));
    }

    /**
     * Returns true if all tender submission requirements have been fulfilled.
     *
     * @return bool
     */
    public function tenderSubmissionIsComplete()
    {
        return ( ! in_array(false, $this->getSubmitTenderChecklist()) );
    }

    /**
     * Returns the list of tender submission requirements with their completion status.
     *
     * @return array
     */
    public function getSubmitTenderChecklist()
    {
        $checklist = array();

        // Tender Rate Submission.
        if( ! $this->tender->callingTenderInformation->disable_tender_rates_submission )
        {
            $itemName = trans('tenders.tenderRates');

            $checklist[ $itemName ] = $this->isSubmitted();
        }

        // Tender Evaluation.
        if( $this->tender->listOfTendererInformation->technical_evaluation_required )
        {
            $setReferenceRepository = \App::make('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository');
            $setReference           = $setReferenceRepository->getSetReferenceByProject($this->tender->project);

            if(isset($setReference))
            {
                $checklist[ trans('technicalEvaluation.technicalEvaluation') ] = $setReference->allItemsChecked($this->company);

                if( ! $setReference->attachmentListItems->isEmpty() )
                {
                    $checklist[ trans('technicalEvaluation.technicalEvaluationAttachments') ] = $setReference->allAttachmentsSubmitted($this->company);
                }
            }
        }

        return $checklist;
    }

    public static function getIncludedBills(Project $project, Tender $tender, Company $tenderer)
    {
        $fileName = self::ratesFileName;

        $path = self::getContractorRatesUploadPath($project, $tender, $tenderer) . "/{$fileName}";

        if(!file_exists($path)) return new Collection();

        $service = new GetTenderAmountFromImportedZip($project, $tender, $tenderer);

        $service->parseBillFiles();

        $allBillInfo = $service->getParsedBillFileContents();

        $billIds = [];

        foreach($allBillInfo as $billInfo)
        {
            $billIds[] = (int)$billInfo['contents']->attributes()->billId;
        }

        return ProjectStructure::whereIn('id', $billIds)->get();
    }
}