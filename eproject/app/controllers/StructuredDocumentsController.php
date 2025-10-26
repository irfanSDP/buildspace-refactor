<?php

use PCK\Forms\StructuredDocumentForm;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\StructuredDocument\StructuredDocument;
use PCK\StructuredDocument\StructuredDocumentClause;
use PCK\StructuredDocument\StructuredDocumentRepository;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

class StructuredDocumentsController extends \BaseController {

    private $structuredDocumentRepository;
    private $form;

    public function __construct(StructuredDocumentRepository $structuredDocumentRepository, StructuredDocumentForm $form)
    {
        $this->structuredDocumentRepository = $structuredDocumentRepository;
        $this->form                         = $form;
    }

    public function createTemplate(int $folderId)
    {
        $structuredDocument = $this->structuredDocumentRepository->createTemplate($folderId);

        return Redirect::route('structured_documents.template.edit', array( $folderId, $structuredDocument->id ));
    }

    public function editTemplate(int $folderId, int $structuredDocumentId)
    {
        $folder   = TemplateTenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);
        $root     = TemplateTenderDocumentFolder::getRootFolder($folder->root_id);

        return View::make('structured_documents.template.edit', array(
            'root'     => $root,
            'folder'   => $folder,
            'document' => $document,
        ));
    }

    public function updateTemplate(int $folderId, int $structuredDocumentId)
    {
        $this->form->validate(Input::all());

        $structuredDocument = StructuredDocument::find($structuredDocumentId);

        $structuredDocument->update(Input::all());

        Flash::success(trans('structuredDocuments.updatedDocument'));

        return Redirect::back();
    }

    public function editTemplateClauses(int $folderId, int $structuredDocumentId)
    {
        $folder   = TemplateTenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);
        $root     = TemplateTenderDocumentFolder::getRootFolder($folder->root_id);

        return View::make('structured_documents.template.editClauses', array(
            'root'     => $root,
            'folder'   => $folder,
            'document' => $document,
        ));
    }

    public function updateTemplateClauses(int $folderId, int $structuredDocumentId)
    {
        $document = StructuredDocument::find($structuredDocumentId);

        ModelOperations::deleteWithTrigger($document->clauses);

        $this->saveTemplateClauses($document, null, Input::get('clauses') ?? array());

        return json_encode(array( 'success' => true ));
    }

    private function saveTemplateClauses(StructuredDocument $document, $parentId, array $clauses = array())
    {
        $count = 0;

        foreach($clauses as $clause)
        {
            $object = StructuredDocumentClause::create(array(
                'content'                => $clause['content'],
                'is_editable'            => $clause['is_editable'] ?? false,
                'parent_id'              => $parentId,
                'priority'               => ( ++$count ),
                'structured_document_id' => $document->id,
            ));

            if( isset( $clause['children'] ) ) $this->saveTemplateClauses($document, $object->id, $clause['children']);
        }
    }

    public function edit(Project $project, int $folderId, int $structuredDocumentId)
    {
        $folder   = TenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);

        return View::make('structured_documents.edit', array(
            'project'  => $project,
            'folder'   => $folder,
            'document' => $document,
        ));
    }

    public function update(Project $project, int $folderId, int $structuredDocumentId)
    {
        $this->form->validate(Input::all());

        $structuredDocument = StructuredDocument::find($structuredDocumentId);

        $structuredDocument->update(Input::all());

        $structuredDocument->touch();

        Flash::success(trans('structuredDocuments.updatedDocument'));

        return Redirect::back();
    }

    public function editClauses(Project $project, int $folderId, int $structuredDocumentId)
    {
        $folder   = TenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);

        return View::make('structured_documents.editClauses', array(
            'project'  => $project,
            'folder'   => $folder,
            'document' => $document,
        ));
    }

    public function updateClauses(Project $project, int $folderId, int $structuredDocumentId)
    {
        $document = StructuredDocument::find($structuredDocumentId);

        $unDeletableClauses = $this->structuredDocumentRepository->deleteClauses(Input::get('deleted_clauses') ?? array());

        $this->saveClauses($document, null, Input::get('clauses') ?? array());

        $this->structuredDocumentRepository->repopulateClauses($document, $unDeletableClauses);

        $document->touch();

        return json_encode(array( 'success' => true ));
    }

    private function saveClauses(StructuredDocument $document, $parentId, array $clauses = array())
    {
        $count = 0;

        foreach($clauses as $clause)
        {
            if( $clause['id'] > 0 )
            {
                // Existing clause.
                $object = StructuredDocumentClause::find($clause['id']);
            }
            else
            {
                // New clause.
                $object = new StructuredDocumentClause(array(
                    'is_editable'            => true,
                    'structured_document_id' => $document->id,
                ));
            }

            if( $object->is_editable ) $object->content = $clause['content'];

            $object->priority  = ( ++$count );
            $object->parent_id = $parentId;

            $object->save();

            if( isset( $clause['children'] ) ) $this->saveClauses($document, $object->id, $clause['children']);
        }
    }

    public function printTemplateDocument(int $folderId, int $structuredDocumentId)
    {
        $folder   = TemplateTenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);

        PDF::setOptions($this->structuredDocumentRepository->getPrintOptions($document));
        PDF::setHeaderHtml($this->structuredDocumentRepository->getHeading($document));

        return PDF::html('structured_documents.printLayout', array(
            'folder'   => $folder,
            'document' => $document,
        ));
    }

    public function printDocument(Project $project, int $folderId, int $structuredDocumentId)
    {
        $folder   = TemplateTenderDocumentFolder::find($folderId);
        $document = StructuredDocument::find($structuredDocumentId);

        PDF::setOptions($this->structuredDocumentRepository->getPrintOptions($document));
        PDF::setHeaderHtml($this->structuredDocumentRepository->getHeading($document));

        return PDF::html('structured_documents.printLayout', array(
            'folder'   => $folder,
            'document' => $document,
        ));
    }

}