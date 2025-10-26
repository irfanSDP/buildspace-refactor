<?php namespace PCK\StructuredDocument;

use PCK\Helpers\ModelOperations;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

class StructuredDocumentRepository {

    public function createTemplate(int $folderId)
    {
        $structuredDocument = new StructuredDocument(array(
            'margin_top'    => StructuredDocument::DEFAULT_MARGIN,
            'margin_bottom' => StructuredDocument::DEFAULT_MARGIN,
            'margin_left'   => StructuredDocument::DEFAULT_MARGIN,
            'margin_right'  => StructuredDocument::DEFAULT_MARGIN,
            'font_size'     => StructuredDocument::DEFAULT_FONT_SIZE,
            'title'         => '',
            'heading'       => '',
            'footer_text'   => '',
        ));

        $structuredDocument->is_template = true;

        $folder = TemplateTenderDocumentFolder::find($folderId);

        $structuredDocument->object()->associate($folder);

        $structuredDocument->save();

        return $structuredDocument;
    }

    public function createFromTemplate(int $folderId, TemplateTenderDocumentFolder $templateFolder)
    {
        $folder = TenderDocumentFolder::find($folderId);

        $template = StructuredDocument::getDocument($templateFolder);

        $structuredDocument = $template->replicate(array( 'id', 'heading', 'created_at', 'updated_at' ));

        $structuredDocument->heading = $folder->project->title;

        $structuredDocument->is_template = false;

        $structuredDocument->object()->associate($folder);

        $structuredDocument->save();

        foreach($template->clauses as $clause)
        {
            $this->copyClauses($clause, $structuredDocument->id, null);
        }

        return $structuredDocument;
    }

    private function copyClauses(StructuredDocumentClause $templateClause, int $newDocumentId, $parentId)
    {
        $newClause = $templateClause->replicate(array( 'id', 'parent_id', 'structured_document_id' ));

        $newClause->structured_document_id = $newDocumentId;
        $newClause->parent_id              = $parentId;

        $newClause->save();

        foreach($templateClause->children as $child)
        {
            $this->copyClauses($child, $newDocumentId, $newClause->id);
        }
    }

    public function create(int $folderId)
    {
        $folder = TenderDocumentFolder::find($folderId);

        $structuredDocument = new StructuredDocument(array(
            'margin_top'    => StructuredDocument::DEFAULT_MARGIN,
            'margin_bottom' => StructuredDocument::DEFAULT_MARGIN,
            'margin_left'   => StructuredDocument::DEFAULT_MARGIN,
            'margin_right'  => StructuredDocument::DEFAULT_MARGIN,
            'font_size'     => StructuredDocument::DEFAULT_FONT_SIZE,
            'title'         => '',
            'heading'       => $folder->project->title,
            'footer_text'   => '',
        ));

        $structuredDocument->is_template = false;

        $structuredDocument->object()->associate($folder);

        $structuredDocument->save();

        return $structuredDocument;
    }

    public function deleteClauses(array $deletedClauses)
    {
        $unDeletableClauses = array();

        foreach($deletedClauses as $clauseId)
        {
            if( ! $clause = StructuredDocumentClause::find($clauseId) ) continue;

            if( ! $clause->isDeletable() )
            {
                $unDeletableClauses[] = $clauseId;
                continue;
            }

            ModelOperations::deleteWithTrigger($clause);
        }

        return $unDeletableClauses;
    }

    public function repopulateClauses(StructuredDocument $document, array $clauses)
    {
        $currentSequenceNumber = StructuredDocumentClause::where('structured_document_id', '=', $document->id)
            ->whereNull('parent_id')
            ->max('priority');

        foreach($clauses as $clauseId)
        {
            if( ! $clause = StructuredDocumentClause::find($clauseId) ) continue;

            $clause->parent_id = null;
            $clause->priority  = ++$currentSequenceNumber;
            $clause->save();
        }
    }

    public function getPrintOptions(StructuredDocument $document)
    {
        return '--disable-smart-shrinking --header-spacing 15 --margin-top ' . $document->margin_top . ' --margin-bottom ' . $document->margin_bottom . ' --margin-left ' . $document->margin_left . ' --margin-right ' . $document->margin_right . ' --footer-center "' . $document->footer_text . ' [page]" --footer-font-size 10';
    }

    public function getHeading(StructuredDocument $document)
    {
        $heading = '';

        if( $document->heading ) $heading = $document->heading . '<hr/>';

        return "<!DOCTYPE html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><h1 style='font-weight: bold; font-size: {$document->font_size}px; text-decoration: underline; text-align: center;'>" . $document->title . "</h1><span style='font-size:{$document->font_size}px;'>" . $heading . "</span>";
    }

    public function getStructuredDocumentPhysicalFile(TenderDocumentFolder $folder)
    {
        $structuredDocumentPath = \PCK\Helpers\Files::getTmpFileUri();

        $document = \PCK\StructuredDocument\StructuredDocument::getDocument($folder);

        \PDF::setOptions($this->getPrintOptions($document));
        \PDF::setHeaderHtml($this->getHeading($document));

        \PDF::setOutputMode('F');

        $success = \PDF::html('structured_documents.printLayout', array(
            'folder'   => $folder,
            'document' => $document,
        ), $structuredDocumentPath);

        return $structuredDocumentPath.'.'.\PCK\Helpers\Files::EXTENSION_PDF;
    }

}