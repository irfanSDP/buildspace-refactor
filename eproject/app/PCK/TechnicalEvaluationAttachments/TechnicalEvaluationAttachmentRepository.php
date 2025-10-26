<?php namespace PCK\TechnicalEvaluationAttachments;

use PCK\Base\Upload;
use PCK\Companies\Company;
use PCK\Projects\Project;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TechnicalEvaluationAttachmentRepository
{
    private $setReferenceRepository;

    public function __construct
    (
        TechnicalEvaluationSetReferenceRepository $setReferenceRepository
    )
    {
        $this->setReferenceRepository = $setReferenceRepository;
    }

    public function find($id)
    {
        return TechnicalEvaluationAttachment::find($id);
    }

    /**
     * Saves the uploaded file and the Attachment.
     *
     * @param UploadedFile $uploadedFile
     * @param              $listItemId
     * @param Company $company
     */
    public function save(UploadedFile $uploadedFile, $listItemId, Company $company)
    {
        // check for existing upload
        $existingAttachment = TechnicalEvaluationAttachment::where('item_id', '=', $listItemId)
            ->where('company_id', '=', $company->id)
            ->first();

        if ($existingAttachment) {
            // Remove existing attachment
            $existingAttachment->delete();
        }

        $upload = new Upload;
        $upload->process($uploadedFile);

        $attachment = new TechnicalEvaluationAttachment;
        $attachment->company_id = $company->id;
        $attachment->item_id = $listItemId;
        $attachment->upload_id = $upload->id;

        $fileParts = pathinfo($uploadedFile->getClientOriginalName());
        $attachment->filename = $fileParts['filename'];

        try {
            \Log::info("Saving attachment ({$attachment->filename}).");

            $attachment->save();

            \Log::info("Saved attachment ({$attachment->filename}).");
        } catch (\Exception $e) {
            \Log::error("Failed saving attachment ({$attachment->filename}): {$e->getMessage()}.");
        }
    }

    /**
     * Returns the company's Technical Evaluation Attachments for a project.
     *
     * @param Project $project
     * @param Company $company
     *
     * @return mixed
     */
    public function get(Project $project, Company $company)
    {
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);
        $listItems = $setReference->attachmentListItems;

        return TechnicalEvaluationAttachment::whereIn('item_id', $listItems->lists('id'))
            ->where('company_id', '=', $company->id)
            ->get();
    }

    public function compulsoryAttachmentsSubmitted(Project $project, Company $company)
    {
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);
        $listItems = $setReference->attachmentListItems->filter(function ($item) {
            return $item->compulsory;
        });

        foreach ($listItems as $listItem) {
            if (!$listItem->attachmentSubmitted($company)) return false;
        }

        return true;
    }

    /**
     * Deletes the attachment.
     *
     * @param TechnicalEvaluationAttachment $attachment
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(TechnicalEvaluationAttachment $attachment)
    {
        return $attachment->delete();
    }

}