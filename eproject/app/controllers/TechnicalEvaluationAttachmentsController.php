<?php

use PCK\Companies\CompanyRepository;
use PCK\Forms\TechnicalEvaluationAttachmentListItemForm;
use PCK\TechnicalEvaluationAttachmentListItems\TechnicalEvaluationAttachmentListItemRepository;
use PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachmentRepository;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;

class TechnicalEvaluationAttachmentsController extends \BaseController {

    private $setReferenceRepository;
    private $technicalEvaluationAttachmentListItemRepository;
    private $technicalEvaluationAttachmentListItemForm;
    private $technicalEvaluationAttachmentRepository;
    private $companyRepository;

    public function __construct
    (
        TechnicalEvaluationSetReferenceRepository $setReferenceRepository,
        TechnicalEvaluationAttachmentListItemRepository $technicalEvaluationAttachmentListItemRepository,
        TechnicalEvaluationAttachmentListItemForm $technicalEvaluationAttachmentListItemForm,
        TechnicalEvaluationAttachmentRepository $technicalEvaluationAttachmentRepository,
        CompanyRepository $companyRepository
    )
    {
        $this->setReferenceRepository                          = $setReferenceRepository;
        $this->technicalEvaluationAttachmentListItemRepository = $technicalEvaluationAttachmentListItemRepository;
        $this->technicalEvaluationAttachmentListItemForm       = $technicalEvaluationAttachmentListItemForm;
        $this->technicalEvaluationAttachmentRepository         = $technicalEvaluationAttachmentRepository;
        $this->companyRepository                               = $companyRepository;
    }

    /**
     * Returns the view for adding items to the attachment list.
     *
     * @param $setReferenceId
     *
     * @return \Illuminate\View\View
     */
    public function show($setReferenceId)
    {
        $setReference = $this->setReferenceRepository->find($setReferenceId);

        return View::make('technical_evaluation.attachments.list_items.index', array(
            'setReference' => $setReference,
        ));
    }

    /**
     * Saves an item in the attachment list.
     * Creates a new one if none matches.
     *
     * @param $setReferenceId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function saveListItem($setReferenceId)
    {
        $input = Input::all();

        $this->technicalEvaluationAttachmentListItemForm->validate($input);

        if( $listItem = $this->technicalEvaluationAttachmentListItemRepository->find($input['list_item_id']) )
        {
            $success = $this->technicalEvaluationAttachmentListItemRepository->update($listItem, $input);

            $success ? Flash::success(trans('technicalEvaluation.listItemUpdated')) : Flash::error(trans('technicalEvaluation.listItemNotUpdated'));
        }
        else
        {
            $success = $this->technicalEvaluationAttachmentListItemRepository->add($this->setReferenceRepository->find($setReferenceId), $input);

            $success ? Flash::success(trans('technicalEvaluation.listItemAdded')) : Flash::error(trans('technicalEvaluation.listItemNotAdded'));
        }

        return Redirect::back();
    }

    /**
     * Removes an item from the attachment list.
     *
     * @param $setReferenceId
     * @param $listItemId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteListItem($setReferenceId, $listItemId)
    {
        $success = $this->technicalEvaluationAttachmentListItemRepository->delete($this->setReferenceRepository->find($setReferenceId), $listItemId);

        $success ? Flash::success(trans('technicalEvaluation.listItemDeleted')) : Flash::error(trans('technicalEvaluation.listItemNotDeleted'));

        return Redirect::back();
    }

    /**
     * Saves the uploaded files and links them to the attachment resource.
     *
     * @param $project
     * @param $companyId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($project, $companyId)
    {
        $input = Input::all();

        $company = $this->companyRepository->find($companyId);

        foreach($input['attachments'] as $listItemId => $uploadedFile)
        {
            if( empty( $uploadedFile ) ) continue;

            $this->technicalEvaluationAttachmentRepository->save($uploadedFile, $listItemId, $company);
        }

        Flash::success(trans('technicalEvaluation.attachmentsSaved'));

        return Redirect::back();
    }

    /**
     * Downloads a file.
     *
     * @param $project
     * @param $companyId
     * @param $attachmentId
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function fileDownload($project, $companyId, $attachmentId)
    {
        $attachment = $this->technicalEvaluationAttachmentRepository->find($attachmentId);
        $upload     = $attachment->upload;

        if( ! $attachment ) App::abort(404);

        return \PCK\Helpers\Files::download(
            $upload->physicalPath() . $upload->filename,
            $attachment->filename . '.' . $upload->extension, array(
            'Content-Type: ' . $upload->mimetype,
        ));
    }

    /**
     * Deletes the attachment.
     *
     * @param $project
     * @param $companyId
     * @param $attachmentId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAttachment($project, $companyId, $attachmentId)
    {
        $attachment = $this->technicalEvaluationAttachmentRepository->find($attachmentId);

        $success = $this->technicalEvaluationAttachmentRepository->delete($attachment);

        $success ? Flash::success(trans('technicalEvaluation.attachmentDeleted')) : Flash::error(trans('technicalEvaluation.attachmentNotDeleted'));

        return Redirect::back();
    }

}