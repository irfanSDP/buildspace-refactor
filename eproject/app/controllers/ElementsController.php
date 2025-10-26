<?php

use PCK\Helpers\DBTransaction;
use PCK\Exceptions\ValidationException;
use PCK\FormBuilder\Elements\ElementRepository;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\FormColumn;
use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\FormBuilder\ElementRejection;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\Forms\ElementsForm;
use PCK\Forms\ElementRejectionsForm;
use PCK\FormBuilder\Elements\FileUpload;

class ElementsController extends \BaseController
{
    private $elementRepository;
    private $form;
    private $elementRejectionForm;

    public function __construct(ElementRepository $elementRepository, ElementsForm $form, ElementRejectionsForm $elementRejectionForm)
    {
        $this->elementRepository    = $elementRepository;
        $this->form                 = $form;
        $this->elementRejectionForm = $elementRejectionForm;
    }

    public function getElementDetails($elementId, $elementType)
    {
        $elementDetails = [];

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $element = Element::findById($elementId);
            $elementDetails = $element->getElementDetails();
        }
        else
        {
            $element = SystemModuleElement::find($elementId);
            $elementDetails = $element->getElementDetails(false);
        }

        return Response::json($elementDetails);
    }

    public function store()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            $this->elementRepository->store($inputs);

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($elementId, $originalElementType)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->form->validate($inputs);
            $this->elementRepository->update($elementId, $originalElementType, $inputs);

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function delete($elementId, $elementType)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();


            if($elementType == Element::ELEMENT_TYPE_ID)
            {
                $element = Element::findById($elementId);
            }
            else
            {
                $element = SystemModuleElement::find($elementId);
            }

            $element->delete();

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function swap()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->elementRepository->swap($inputs['draggedElementMappingId'], $inputs['swappedElementMappingId']);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function attachmentsUpdate($elementId, $elementType)
    {
        $inputs  = Input::all();
        $element = null;

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $element = Element::findById($elementId);
        }
        else
        {
            $element = SystemModuleElement::find($elementId);
        }

        ModuleAttachment::saveAttachments($element, $inputs);

		return array(
			'success' => true,
		);
    }

    public function getAttachmentCount($elementId, $elementType)
    {
        $element = null;

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $element = Element::findById($elementId);
        }
        else
        {
            $element = SystemModuleElement::find($elementId);
        }

        return Response::json([
            'name'                => $element::ELEMENT_TYPE_ID . '_' . $element->id,
            'attachmentCount'     => count($this->getAttachmentDetails($element)),
        ]);
    }

    public function getAttachmentsList($elementId, $elementType)
	{
        $element = null;

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $element = Element::findById($elementId);
        }
        else
        {
            $element = SystemModuleElement::find($elementId);
        }

		$uploadedFiles = $this->getAttachmentDetails($element);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
	}

    public function getElementSelections($sectionId)
    {
        $section = FormColumnSection::find($sectionId);

        $sectionSelections = $this->elementRepository->getElementSelections($section);

        return Response::json($sectionSelections);
    }

    public function importSelectedElements($sectionId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $section                  = FormColumnSection::find($sectionId);
            $selecteElementMappingIds = $inputs['selectedIds'];

            foreach($selecteElementMappingIds as $mappingId)
            {
                $mapping = FormElementMapping::find($mappingId);
                $mapping->clone($section);
            }

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function getElementRejection($elementId, $elementType)
    {
        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $element = Element::findById($elementId);
        }
        else
        {
            $element = SystemModuleElement::find($elementId);
        }

        $rejection = ElementRejection::findRecordByElement($element);
        $remarks   = is_null($rejection) ? '' : $rejection->remarks;
        $updator   = is_null($rejection) ? null : ($rejection->updatedBy ? $rejection->updatedBy->name : null);

        return Response::json([
            'remarks' => $remarks,
            'updator' => $updator,
        ]);
    }

    public function saveRejection($elementId, $elementType)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $this->elementRejectionForm->validate($inputs);

            if($elementType == Element::ELEMENT_TYPE_ID)
            {
                $element = Element::findById($elementId);
            }
            else
            {
                $element = SystemModuleElement::find($elementId);
            }

            ElementRejection::updateOrCreateRecord($element, trim($inputs['remarks']));

            $transaction->commit();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $transaction->rollback();
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }

    public function deleteRejection($elementId, $elementType)
    {
        $errors  = null;
        $success = false;

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            if($elementType == Element::ELEMENT_TYPE_ID)
            {
                $element = Element::findById($elementId);
            }
            else
            {
                $element = SystemModuleElement::find($elementId);
            }

            ElementRejection::deleteRecord($element);

            $transaction->commit();

            $success = true;
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            $errors = $e->getMessage();
        }
        
        return Response::json([
            'success'  => $success,
            'errors'   => $errors,
        ]);
    }
}

