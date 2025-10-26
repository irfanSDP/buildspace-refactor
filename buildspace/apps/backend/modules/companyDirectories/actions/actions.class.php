<?php

/**
 * companyDirectories actions.
 *
 * @package    buildspace
 * @subpackage companyDirectories
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class companyDirectoriesActions extends BaseActions {

    // =============================================================================================================
    public function executeGetCompanyListing(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $items = array();

        $companies = DoctrineQuery::create()
            ->select('c.id, c.name, c.reference_id, c.registration_no, r.country, sr.name, c.updated_at')
            ->from('Company c')
            ->leftJoin('c.Region r')
            ->leftJoin('c.SubRegion sr')
            ->where('c.reference_id IS NOT NULL')
            ->orderBy('c.name ASC')
            ->fetchArray();

        if(!empty($companies))
        {
            $contractGroupCategories = array();

            $EProjectCompanies = DoctrineQuery::create()
                ->select('c.id, c.reference_id, g.id, g.name')
                ->from('EProjectCompany c')
                ->leftJoin('c.ContractGroupCategory g')
                ->whereIn('c.reference_id', array_column($companies, 'reference_id'))
                ->orderBy('c.name ASC')
                ->fetchArray();

            foreach($EProjectCompanies as $EProjectCompany)
            {
                $contractGroupCategories[$EProjectCompany['reference_id']] = $EProjectCompany['ContractGroupCategory']['name'];
            }
        }

        foreach ( $companies as $company )
        {
            $items[] = array(
                'id'              => $company['id'],
                'company_name'    => $company['name'],
                'registration_no' => $company['registration_no'],
                'business_type'   => isset($contractGroupCategories[$company['reference_id']]) ? $contractGroupCategories[$company['reference_id']] : '',
                'country'         => $company['Region']['country'],
                'state'           => $company['SubRegion']['name']
            );
        }

        // empty row
        $items[] = array(
            'id'              => Constants::GRID_LAST_ROW,
            'company_name'    => null,
            'registration_no' => null,
            'business_type'   => null,
            'country'         => null,
            'state'           => null
        );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $items
        ));
    }

    public function executeGetCompanyInformation(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $form = new CompanyForm($company);

        return $this->renderJson(array(
            'form' => array(
                'company[shortname]'                  => $form->getObject()->shortname,
                'company[about]'                      => $form->getObject()->about,
                'company[contact_person_name]'        => $form->getObject()->contact_person_name,
                'company[contact_person_direct_line]' => $form->getObject()->contact_person_direct_line,
                'company[contact_person_email]'       => $form->getObject()->contact_person_email,
                'company[contact_person_mobile]'      => $form->getObject()->contact_person_mobile,
                'company[website]'                    => $form->getObject()->website,
                'company[_csrf_token]'                => $form->getCSRFToken()
            ),
            'info' => array(
                'name'            => $company->name,
                'registration_no' => $company->registration_no,
                'business_type'   => $company->getEProjectCompany()->ContractGroupCategory->name,
                'phone_number'    => $company->phone_number,
                'fax_number'      => $company->fax_number,
                'address'         => $company->address,
                'region'       => $company->Region->country,
                'sub_region'   => $company->SubRegion->name
            )
        ));
    }

    public function executeUpdateCompanyInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post')
        );

        $rfqId   = $request->getParameter('rfqId');
        $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'));
        $company = ( $company ) ? $company : new Company();

        $isNew = $company->isNew();

        $form = new CompanyForm($company);

        if ( $this->isFormValid($request, $form) )
        {
            $group   = $form->save();
            $id      = $group->getId();
            $success = true;
            $errors  = array();

            // for RFQ add new Company's form
            if ( isset ( $rfqId ) AND $isNew )
            {
                $rfqSupplier                           = new RFQSupplier();
                $rfqSupplier->request_for_quotation_id = $rfqId;
                $rfqSupplier->company_id               = $id;
                $rfqSupplier->save();
            }
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
            $isNew   = false;
        }

        return $this->renderJson(array( 'id' => $id, 'isNew' => $isNew, 'companyName' => $form->getObject()->name, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeDeleteCompanyInfo(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $success  = false;
        $errorMsg = null;

        try
        {
            $company->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'companyId' => $request->getPostParameter('companyId'), 'success' => $success, 'errorMsg' => $errorMsg ));
    }
    // =============================================================================================================

    // =============================================================================================================
    public function executeGetBranchesListing(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() AND $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId')));

        $form          = new BaseForm();
        $data['items'] = array();

        $branches = DoctrineQuery::create()
            ->select('cb.id, cb.name, cb.contact_person_name, r.country, sr.name, cb.updated_at')
            ->from('CompanyBranch cb')
            ->leftJoin('cb.Region r')
            ->leftJoin('cb.SubRegion sr')
            ->orderBy('cb.id')
            ->where('cb.company_id = ?', $company->id)
            ->fetchArray();

        foreach ( $branches as $branch )
        {
            $data['items'][] = array(
                'id'                  => $branch['id'],
                'branch_name'         => $branch['name'],
                'contact_person_name' => $branch['contact_person_name'],
                'updated_at'          => date('d/m/Y H:i', strtotime($branch['updated_at'])),
                'country'             => $branch['Region']['country'],
                'state'               => $branch['SubRegion']['name'],
                '_csrf_token'         => $form->getCSRFToken(),
            );
        }

        $data['items'][] = array(
            'id'                  => Constants::GRID_LAST_ROW,
            'branch_name'         => null,
            'contact_person_name' => null,
            'updated_at'          => '-',
            'country'             => null,
            'state'               => null,
            '_csrf_token'         => null,
        );

        $data['identifier'] = 'id';

        return $this->renderJson($data);
    }

    public function executeGetBranchInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $data = array();

        $companyBranch = Doctrine_Core::getTable('CompanyBranch')->find($request->getParameter('branchId'));
        $companyBranch = ( $companyBranch ) ? $companyBranch : new CompanyBranch();
        $form          = new CompanyBranchForm($companyBranch);



        return $this->renderJson(array(
            'branchInformationForm' => array(
                'company_branch[name]'                       => $form->getObject()->name,
                'company_branch[contact_person_name]'        => $form->getObject()->contact_person_name,
                'company_branch[contact_person_direct_line]' => $form->getObject()->contact_person_direct_line,
                'company_branch[contact_person_email]'       => $form->getObject()->contact_person_email,
                'company_branch[contact_person_mobile]'      => $form->getObject()->contact_person_mobile,
                'company_branch[phone_number]'               => $form->getObject()->phone_number,
                'company_branch[fax_number]'                 => $form->getObject()->fax_number,
                'company_branch[address]'                    => $form->getObject()->address,
                'company_branch[postcode]'                   => $form->getObject()->postcode,
                'company_branch[region_id]'                  => $form->getObject()->region_id ? : 0,
                'company_branch[sub_region_id]'              => $form->getObject()->sub_region_id ? : 0,
                'company_branch[_csrf_token]'                => $form->getCSRFToken()
            )
        ));
    }

    public function executeUpdateBranchInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $companyBranch = Doctrine_Core::getTable('CompanyBranch')->find($request->getParameter('branchId'));
        $companyBranch = ( $companyBranch ) ? $companyBranch : new CompanyBranch();
        $form          = new CompanyBranchForm($companyBranch);

        if ( $this->isFormValid($request, $form) )
        {
            $form->updateObject();
            $form->getObject()->setCompanyId($company->id);
            $form->getObject()->save();

            $group   = $form->save();
            $id      = $group->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('branchId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'branchName' => $form->getObject()->name, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeDeleteBranchInfo(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $companyBranch = Doctrine_Core::getTable('CompanyBranch')->find($request->getParameter('branchId'))
        );

        $errorMsg = null;

        try
        {
            $companyBranch->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'branchId' => $request->getPostParameter('branchId'), 'success' => $success, 'errorMsg' => $errorMsg ));
    }
    // =============================================================================================================

    // =============================================================================================================
    public function executeGetOtherInformationListing(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() AND $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId')));

        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'I18N', 'Asset', 'Url', 'Tag' ));

        $data['items'] = array();
        $form          = new BaseForm();

        $otherInformations = DoctrineQuery::create()
            ->select('coi.id, coi.title, coi.updated_at')
            ->from('CompanyOtherInformation coi')
            ->orderBy('coi.id')
            ->where('coi.company_id = ?', $company->id)
            ->fetchArray();

        foreach ( $otherInformations as $otherInformation )
        {
            $data['items'][] = array(
                'id'          => $otherInformation['id'],
                'title'       => $otherInformation['title'],
                'updated_at'  => date('d/m/Y H:i', strtotime($otherInformation['updated_at'])),
                '_csrf_token' => $form->getCSRFToken(),
            );
        }

        $data['items'][] = array(
            'id'          => Constants::GRID_LAST_ROW,
            'title'       => null,
            'updated_at'  => '-',
            '_csrf_token' => null,
        );

        $data['identifier'] = 'id';

        return $this->renderJson($data);
    }

    public function executeGetOtherInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $otherInformation = Doctrine_Core::getTable('CompanyOtherInformation')->find($request->getParameter('otherInformationId'));
        $otherInformation = ( $otherInformation ) ? $otherInformation : new CompanyOtherInformation();
        $form             = new CompanyOtherInformationForm($otherInformation);

        return $this->renderJson(array(
            'otherInformationForm' => array(
                'company_other_information[title]'       => $form->getObject()->title,
                'company_other_information[description]' => $form->getObject()->description,
                'company_other_information[_csrf_token]' => $form->getCSRFToken()
            )
        ));
    }

    public function executeUpdateOtherInformation(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $company = Doctrine_Core::getTable('Company')->find($request->getParameter('companyId'))
        );

        $otherInformation = Doctrine_Core::getTable('CompanyOtherInformation')->find($request->getParameter('otherInformationId'));
        $otherInformation = ( $otherInformation ) ? $otherInformation : new CompanyOtherInformation();
        $form             = new CompanyOtherInformationForm($otherInformation);
        $isNew            = $otherInformation->isNew();

        if ( $this->isFormValid($request, $form) )
        {
            $group   = $form->save();
            $id      = $group->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('otherInformationId');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'isNew' => $isNew, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeOtherInformationFileListing(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $otherInformation = Doctrine_Core::getTable('CompanyOtherInformation')->find($request->getParameter('otherInformationId'))
        );

        $data      = array();
        $items     = array();
        $form      = new BaseForm();
        $baseUrl   = $this->getController()->genUrl('homepage', true);
        $csrfToken = $form->getCSRFToken();

        $files = DoctrineQuery::create()
            ->select('c.id, c.original_file_name, c.uploaded_file_name, c.created_at')
            ->from('CompanyOtherInformationFile c')
            ->where('c.company_other_information_id = ?', $otherInformation->id)
            ->orderBy('c.id')
            ->fetchArray();

        foreach ( $files as $file )
        {
            $items[] = array(
                'id'          => $file['id'],
                'file_name'   => $file['original_file_name'],
                'created_at'  => date('d/m/Y H:i', strtotime($file['created_at'])),
                'download'    => '<a target="_blank" href="' . $baseUrl . "companyDirectories/downloadUploadedFile/fileId/{$file['id']}/_csrf_token/{$csrfToken}" . '">Download</a>',
                '_csrf_token' => $form->getCSRFToken(),
            );
        }

        $data['identifier'] = 'id';
        $data['items']      = $items;

        return $this->renderJson($data);
    }

    public function executeUploadOtherInformationFile(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $this->forward404Unless(
            $request->isMethod('post') AND
            $otherInformation = Doctrine_Core::getTable('CompanyOtherInformation')->find($request->getParameter('otherInformationId'))
        );

        $errors  = array();
        $success = false;

        $form = new CompanyDirectoriesFileUploadForm();

        if ( $this->isFormValid($request, $form) )
        {
            foreach ( $request->getFiles($form->getName()) as $uploadedFile )
            {
                $uploadDir        = sfConfig::get('sf_upload_dir_company_directories');
                $currentFileName  = explode('.', $uploadedFile["name"]);
                $uploadedFileName = sha1($currentFileName[0] . microtime() . mt_rand()) . '.' . $currentFileName[count($currentFileName) - 1];

                try
                {
                    if ( !is_dir($uploadDir) )
                    {
                        mkdir($uploadDir, 0777);
                    }

                    $success = true;

                    move_uploaded_file($uploadedFile["tmp_name"], $uploadDir . DIRECTORY_SEPARATOR . $uploadedFileName);

                    $file                               = new CompanyOtherInformationFile();
                    $file->company_other_information_id = $otherInformation->id;
                    $file->original_file_name           = $uploadedFile["name"];
                    $file->uploaded_file_name           = $uploadedFileName;
                    $file->save();

                    break;
                }
                catch (Exception $e)
                {
                    break;
                }
            }
        }
        else
        {
            $errors = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeDownloadUploadedFile(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $file = Doctrine_Core::getTable('CompanyOtherInformationFile')->find($request->getParameter('fileId'))
        );

        $uploadDir = sfConfig::get('sf_upload_dir_company_directories');
        $filePath  = $uploadDir . DIRECTORY_SEPARATOR . $file->uploaded_file_name;
        $mimeType  = Utilities::mimeContentType($filePath);

        /** @var $response sfWebResponse */
        $response = $this->getResponse();
        $response->clearHttpHeaders();
        $response->setContentType($mimeType);
        $response->setHttpHeader('Content-Disposition', 'attachment; filename="' . basename($file->original_file_name) . '"');
        $response->setHttpHeader('Content-Description', 'File Transfer');
        $response->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $response->setHttpHeader('Content-Length', filesize($filePath));
        $response->setHttpHeader('Cache-Control', 'public, must-revalidate');

        // if https then always give a Pragma header like this to overwrite the "pragma: no-cache" header which
        // will hint IE8 from caching the file during download and leads to a download error!!!
        $response->setHttpHeader('Pragma', 'public');
        //$response->setContent(file_get_contents($filePath)); # will produce a memory limit exhausted error
        $response->sendHttpHeaders();

        ob_end_flush();

        return $this->renderText(readfile($filePath));
    }

    public function executeDeleteUploadedFile(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $file = Doctrine_Core::getTable('CompanyOtherInformationFile')->find($request->getParameter('fileId'))
        );

        try
        {
            $file->delete();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
    }

    public function executeDeleteOtherInformation(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $otherInformation = Doctrine_Core::getTable('CompanyOtherInformation')->find($request->getParameter('otherInformationId'))
        );

        try
        {
            $otherInformation->delete();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'otherInformationId' => $request->getPostParameter('otherInformationId'), 'success' => $success, 'errorMsg' => $errorMsg ));
    }
    // =============================================================================================================
}
