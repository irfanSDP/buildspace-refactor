<?php

/**
 * billManagerExportFile actions.
 *
 * @package    buildspace
 * @subpackage billManagerExportFile
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class billManagerExportFileActions extends BaseActions {

    public function executeGetBillElementList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        $elements = DoctrineQuery::create()
            ->select('e.id, e.description')
            ->from('BillElement e')
            ->andWhere('e.project_structure_id = ?', $bill->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $form      = new BaseForm();
        $formToken = $form->getCSRFToken();

        foreach ( $elements as $key => $element )
        {
            $elements[$key]['_csrf_token'] = $formToken;
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => '',
            '_csrf_token' => $formToken,
        );

        array_push($elements, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $elements
        ));
    }

    public function executeGetBillItemList(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $element = Doctrine_Core::getTable('BillElement')->find($request->getParameter('id')));

        $items = array();

        $roots = DoctrineQuery::create()
            ->select('i.id, i.lft, i.rgt')
            ->from('BillItem i')
            ->andWhere('i.element_id = ?', $element->id)
            ->addWhere('i.root_id = i.id')
            ->addOrderBy('i.priority ASC')
            ->fetchArray();

        foreach ( $roots as $root )
        {
            $billItems = DoctrineQuery::create()
                ->select('i.id, i.description, i.type, i.uom_id, i.level, ifc.column_name, ifc.final_value, ifc.value, ifc.has_build_up')
                ->from('BillItem i')
                ->leftJoin('i.FormulatedColumns ifc')
                ->andWhere('i.root_id = ?', $root['id'])
                ->addWhere('i.lft >= ? AND i.rgt <= ?', array( $root['lft'], $root['rgt'] ))
                ->addOrderBy('i.lft ASC')
                ->fetchArray();

            foreach ( $billItems as $billItem )
            {
                $billItem['type']       = (string) $billItem['type'];
                $billItem['uom_id']     = (string) $billItem['uom_id'];
                $billItem['uom_symbol'] = $billItem['uom_id'] > 0 ? Doctrine_Core::getTable('UnitOfMeasurement')->find($billItem['uom_id'])->symbol : '---';

                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-final_value']        = 0;
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-value']              = '';
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-has_cell_reference'] = false;
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-has_formula']        = false;
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-linked']             = false;
                $billItem[BillItem::FORMULATED_COLUMN_RATE . '-has_build_up']       = false;

                foreach ( $billItem['FormulatedColumns'] as $formulatedColumn )
                {
                    $columnName                                    = $formulatedColumn['column_name'];
                    $billItem[$columnName . '-final_value']        = $formulatedColumn['final_value'];
                    $billItem[$columnName . '-value']              = $formulatedColumn['value'];
                    $billItem[$columnName . '-has_cell_reference'] = false;
                    $billItem[$columnName . '-has_formula']        = false;
                    $billItem[$columnName . '-linked']             = false;
                    $billItem[$columnName . '-has_build_up']       = $formulatedColumn['has_build_up'];
                }

                unset( $billItem['FormulatedColumns'] );

                array_push($items, $billItem);
            }
        }

        $defaultLastRow = array(
            'id'          => Constants::GRID_LAST_ROW,
            'description' => ''
        );

        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-final_value']        = 0;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-value']              = 0;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-linked']             = false;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-has_build_up']       = false;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-has_cell_reference'] = false;
        $defaultLastRow[BillItem::FORMULATED_COLUMN_RATE . '-has_formula']        = false;

        array_push($items, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetFileByBill(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $bill = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url', 'Tag' ));

        $exportedFiles = DoctrineQuery::create()
            ->select('f.id, f.filename, f.extension, f.file_type, f.export_type, f.created_at')
            ->from('ExportedFile f')
            ->andWhere('f.project_structure_id = ?', $bill->id)
            ->addOrderBy('f.id DESC')
            ->fetchArray();

        foreach ( $exportedFiles as $key => $file )
        {
            $baseUrl    = $this->getController()->genUrl('homepage', true);
            $uploadPath = 'uploads' . DIRECTORY_SEPARATOR;

            $exportedFiles[$key]['file_type']    = ExportedFileTable::getFileTypeText($file['file_type']);
            $exportedFiles[$key]['export_type']  = ExportedFileTable::getExportTypeText($file['export_type']);
            $exportedFiles[$key]['filename']     = $file['filename'] . $file['extension'];
            $exportedFiles[$key]['downloadPath'] = '<a target="_blank" href="' . $baseUrl . $uploadPath . $exportedFiles[$key]['filename'] . '"> Download </a>';
            $exportedFiles[$key]['created_at']   = date('d/m/Y H:i', strtotime($file['created_at']));
        }

        $defaultLastRow = array(
            'id'           => Constants::GRID_LAST_ROW,
            'filename'     => '',
            'extension'    => '',
            'file_type'    => ExportedFile::FILE_TYPE_EXCEL_TEXT,
            'export_type'  => '',
            'downloadPath' => '',
            'created_at'   => ''
        );

        array_push($exportedFiles, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $exportedFiles
        ));
    }

    public function executeGetFileByProject(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')));

        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url', 'Tag' ));

        $billIds       = array();
        $exportedFiles = array();

        $bills = DoctrineQuery::create()->select('p.id')
            ->from('ProjectStructure p')
            ->where('p.root_id = ?', $project->id)
            ->andWhere('p.root_id <> p.id')
            ->andWhere('p.type = ?', ProjectStructure::TYPE_BILL)
            ->addOrderBy('p.priority ASC')
            ->fetchArray();

        foreach ( $bills as $k => $bill )
        {
            array_push($billIds, $bill['id']);
        }

        array_push($billIds, $project->id);

        if ( ! empty($billIds) )
        {
            $exportedFiles = DoctrineQuery::create()
                ->select('f.id, f.filename, f.extension, f.file_type, f.export_type, f.created_at, p.id, p.title')
                ->from('ExportedFile f')
                ->leftJoin('f.ProjectStructure p')
                ->andWhere('f.project_structure_id IN ?', array( $billIds ))
                ->addOrderBy('f.id DESC')
                ->fetchArray();

            sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

            foreach ( $exportedFiles as $key => $file )
            {
                $uploadPath = 'uploads' . DIRECTORY_SEPARATOR;

                $exportedFiles[$key]['file_type']    = ExportedFileTable::getFileTypeText($file['file_type']);
                $exportedFiles[$key]['export_type']  = ExportedFileTable::getExportTypeText($file['export_type']);
                $exportedFiles[$key]['filename']     = $file['filename'] . $file['extension'];
                $exportedFiles[$key]['bill_title']   = $file['ProjectStructure']['title'];
                $exportedFiles[$key]['downloadPath'] = '<a target="_blank" href="' . public_path($uploadPath . $exportedFiles[$key]['filename']) . '"> Download </a>';
                $exportedFiles[$key]['created_at']   = date('d/m/Y H:i', strtotime($file['created_at']));

                unset( $exportedFiles[$key]['ProjectStructure'] );
            }
        }

        $defaultLastRow = array(
            'id'           => Constants::GRID_LAST_ROW,
            'bill_title'   => '',
            'filename'     => '',
            'extension'    => '',
            'file_type'    => ExportedFile::FILE_TYPE_EXCEL_TEXT,
            'export_type'  => '',
            'downloadPath' => '',
            'created_at'   => ''
        );

        array_push($exportedFiles, $defaultLastRow);

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $exportedFiles
        ));
    }

    public function executeDeleteFile(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $exportedFile = Doctrine_Core::getTable('ExportedFile')->find($request->getParameter('id')));

        $items      = array();
        $uploadPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
        $filename   = $exportedFile->filename . $exportedFile->extension;
        $fullPath   = $uploadPath . DIRECTORY_SEPARATOR . $filename;

        $errorMsg = null;

        try
        {
            if ( is_readable($fullPath) )
            {
                unlink($fullPath);
            }

            array_push($items, array( 'id' => $exportedFile->id ));

            $exportedFile->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success, 'items' => $items ));
    }

    public function executeExportExcelByElement(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isMethod('post') and
            $bill = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $bill->type == ProjectStructure::TYPE_BILL and
            strlen($request->getParameter('filename')) > 0 and
            strlen($request->getParameter('eids')) > 0
        );

        $elementIds   = explode(',', $request->getParameter('eids'));
        $withRate     = ( $request->getParameter('wr') == 'true' ) ? true : false;
        $withQuantity = ( $request->getParameter('wq') == 'true' ) ? true : false;

        //Initiate sfBillExport
        $sfBillExport = new sfBillExportExcel($bill, $elementIds, $withRate, $withQuantity);

        $errorMsg = null;

        try
        {
            //process
            $sfBillExport->process();
            
            $tmpFile = $sfBillExport->write('Excel2007');

            $fileSize     = filesize($tmpFile);
            $fileContents = file_get_contents($tmpFile);
            $mimeType     = Utilities::mimeContentType($tmpFile);

            unlink($tmpFile);

            $this->getResponse()->clearHttpHeaders();
            $this->getResponse()->setStatusCode(200);
            $this->getResponse()->setContentType($mimeType);
            $this->getResponse()->setHttpHeader(
                "Content-Disposition",
                "attachment; filename*=UTF-8''" . rawurlencode($request->getParameter('filename')) . ".xlsx"
            );
            $this->getResponse()->setHttpHeader('Content-Description', 'File Transfer');
            $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
            $this->getResponse()->setHttpHeader('Content-Length', $fileSize);
            $this->getResponse()->setHttpHeader('Cache-Control', 'public, must-revalidate');
            // if https then always give a Pragma header like this  to overwrite the "pragma: no-cache" header which
            // will hint IE8 from caching the file during download and leads to a download error!!!
            $this->getResponse()->setHttpHeader('Pragma', 'public');
            $this->getResponse()->sendHttpHeaders();

            ob_end_flush();

            return $this->renderText($fileContents);
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'errorMsg' => $errorMsg, 'success' => $success ));
    }

}