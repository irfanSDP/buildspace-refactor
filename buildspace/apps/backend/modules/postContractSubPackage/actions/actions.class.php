<?php

/**
 * postContractSubPackage actions.
 *
 * @package    buildspace
 * @subpackage postContractSubPackage
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class postContractSubPackageActions extends BaseActions {

    public function executeGetSubPackageList(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('projectId'))
        );

        $pdo = $project->getTable()->getConnection()->getDbh();

        //Get Subpackages & Standard Bill Amount
        $stmt = $pdo->prepare("SELECT DISTINCT s.name AS title, s.id, s.locked, s.selected_company_id, s.project_structure_id,
            COALESCE(SUM(ROUND(rate.rate * type.qty_per_unit, 2)), 0) AS standard_bill_amount, s.priority
            FROM " . SubPackageTable::getInstance()->getTableName() . " s
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON s.project_structure_id = pc.project_structure_id
            JOIN " . SubPackageTypeReferenceTable::getInstance()->getTableName() . " type_ref ON type_ref.sub_package_id = s.id
            JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " type ON type.post_contract_id = pc.id AND type.bill_column_setting_id = type_ref.bill_column_setting_id
            JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate ON rate.sub_package_id = s.id AND rate.bill_item_id = type.bill_item_id
            WHERE s.project_structure_id = " . $project->id . " AND s.locked IS TRUE AND s.deleted_at IS NULL GROUP BY s.id ORDER BY s.priority");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //Get Prelim Bills and its amount
        $stmt = $pdo->prepare("SELECT sp.id, COALESCE(SUM(rate.single_unit_grand_total), 0) AS amount
            FROM " . SubPackageTable::getInstance()->getTableName() . " sp
            JOIN " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate ON rate.sub_package_id = sp.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
            JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
            WHERE sp.project_structure_id = " . $project->id . " AND t.type = " . BillType::TYPE_PRELIMINARY . " GROUP BY sp.id ORDER BY sp.id ASC");

        $stmt->execute();

        $subPackagePrelimAmount = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $form = new BaseForm();

        foreach ( $records as $key => $record )
        {
            $totalAmount = $records[$key]['standard_bill_amount'];

            if ( count($subPackagePrelimAmount) && array_key_exists($record['id'], $subPackagePrelimAmount) )
            {
                $totalAmount += $subPackagePrelimAmount[$record['id']][0];
            }

            $records[$key]['amount']      = $totalAmount;
            $records[$key]['relation_id'] = $project->id;
            $records[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $record, $records[$key]['standard_bill_amount'] );
        }

        unset( $subPackageEstAmounts, $selectedAmounts );

        array_push($records, array(
            'id'                  => Constants::GRID_LAST_ROW,
            'title'               => '',
            'relation_id'         => $project->id,
            'selected_company_id' => - 1,
            'locked'              => true,
            'amount'              => '',
            '_csrf_token'         => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetProjectBreakdown(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id')) and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($subPackage->project_structure_id)
        );

        $revision = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        $records = array();

        $pdo = $subPackage->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT s.id, s.title, s.type, s.level, t.type AS bill_type, t.status AS bill_status,
            bls.id AS layout_id, COALESCE(SUM(rate.single_unit_grand_total), 0) AS overall_total_after_markup
            FROM " . SubPackagePostContractBillItemRateTable::getInstance()->getTableName() . " rate
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON rate.bill_item_id  = i.id AND i.deleted_at IS NULL
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON i.element_id =  e.id AND e.deleted_at IS NULL
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " s ON e.project_structure_id = s.id AND s.deleted_at IS NULL
            JOIN " . BillTypeTable::getInstance()->getTableName() . " t ON t.project_structure_id = s.id
            JOIN " . BillLayoutSettingTable::getInstance()->getTableName() . " bls ON bls.bill_id = s.id
            WHERE rate.sub_package_id = " . $subPackage->id . " AND t.deleted_at IS NULL AND bls.deleted_at IS NULL GROUP BY s.id, s.title, s.type, s.level, t.type,
            t.status, bls.id ORDER BY s.id ASC");

        $stmt->execute();

        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;

        $form = new BaseForm();

        array_push($records, array(
            'id'                         => $project->id,
            'billLayoutSettingId'        => null,
            'title'                      => $project->title,
            'type'                       => $project->type,
            'level'                      => $project->level,
            'count'                      => null,
            'up_to_date_percentage'      => 0,
            'up_to_date_amount'          => 0,
            'overall_total_after_markup' => 0,
            '_csrf_token'                => $form->getCSRFToken()
        ));

        foreach ( $bills as $key => $record )
        {
            $count ++;

            $bills[$key]['count']                 = $count;
            $bills[$key]['billLayoutSettingId']   = $record['layout_id'];
            $bills[$key]['up_to_date_percentage'] = 0;
            $bills[$key]['up_to_date_amount']     = 0;
            $bills[$key]['_csrf_token']           = $form->getCSRFToken();

            if ( $bills[$key]['bill_type'] == BillType::TYPE_PRELIMINARY )
            {
                list( $billTotal, $upToDateAmount ) = SubPackagePreliminariesClaimTable::getUpToDateAmountByBillId($subPackage, $record['id'], $revision);
            }
            else
            {
                $bills[$key]['overall_total_after_markup'] = SubPackagePostContractStandardClaimTable::getOverallTotalByBillId($record['id'], $revision);
                $upToDateAmount                            = SubPackagePostContractStandardClaimTable::getUpToDateAmountByBillId($record['id'], $revision);
            }

            $percentage = ( $bills[$key]['overall_total_after_markup'] > 0 ) ? number_format(( $upToDateAmount / $bills[$key]['overall_total_after_markup'] ) * 100, 2, '.', '') : 0;

            $bills[$key]['up_to_date_percentage'] = ( $percentage ) ? $percentage : 0;
            $bills[$key]['up_to_date_amount']     = ( $upToDateAmount ) ? $upToDateAmount : 0;

            array_push($records, $bills[$key]);

            unset( $record, $bills['layout_id'], $bills['quantity'] );
        }

        unset( $bills );

        $postContractClaims = array(
            array(//empty row
                'id'                         => Constants::GRID_LAST_ROW,
                'title'                      => "",
                'type'                       => - 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => 0
            ),
            array(
                'id'                         => PostContractClaim::TYPE_VARIATION_ORDER_TEXT . '-' . PostContractClaim::TYPE_VARIATION_ORDER,
                'title'                      => PostContractClaim::TYPE_VARIATION_ORDER_TEXT,
                'type'                       => PostContractClaim::TYPE_VARIATION_ORDER,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => $subPackage->getVariationOrderUpToDateClaimAmountPercentage(),
                'up_to_date_amount'          => $subPackage->getVariationOrderUpToDateClaimAmount(),
                'overall_total_after_markup' => $subPackage->getVariationOrderOverallTotal(),
                '_csrf_token'                => $form->getCSRFToken()
            ),
            array(
                'id'                         => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT . '-' . PostContractClaim::TYPE_MATERIAL_ON_SITE,
                'title'                      => PostContractClaim::TYPE_MATERIAL_ON_SITE_TEXT,
                'type'                       => PostContractClaim::TYPE_MATERIAL_ON_SITE,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => $subPackage->getMaterialOnSiteUpToDateClaimAmount(),
                'overall_total_after_markup' => 0,
                '_csrf_token'                => $form->getCSRFToken()
            ),
            array(//last empty row
                'id'                         => - 999,
                'title'                      => "",
                'type'                       => 1,
                'level'                      => 0,
                'count'                      => 0,
                'up_to_date_percentage'      => 0,
                'up_to_date_amount'          => 0,
                'overall_total_after_markup' => 0,
                '_csrf_token'                => 0
            )
        );

        foreach ( $postContractClaims as $postContractClaim )
        {
            array_push($records, $postContractClaim);
            unset( $postContractClaim );
        }

        unset( $postContractClaims );

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $records
        ));
    }

    public function executeGetClaimRevisionLists(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('id'))
        );

        $claimRevision = $subPackage->getClaimRevisions();

        if ( count($claimRevision->toArray()) )
        {
            foreach ( $claimRevision as $revision )
            {
                $data['claimRevisions'][] = array(
                    'id'             => $revision['id'],
                    'sub_package_id' => $revision['sub_package_id'],
                    'version'        => $revision['version'],
                    'selected'       => $revision['current_selected_revision'],
                    'locked_status'  => ( $revision['locked_status'] ) ? 1 : 0,
                    'updated_at'     => date('d M Y', strtotime($revision['updated_at']))
                );
            }
        }
        else
        {
            $claimRevision                            = new SubPackagePostContractClaimRevision();
            $claimRevision->sub_package_id            = $subPackage->id;
            $claimRevision->current_selected_revision = true;
            $claimRevision->version                   = 1;
            $claimRevision->save();

            $claimRevision->refresh();

            $data['claimRevisions'][] = array(
                'id'             => $claimRevision->id,
                'sub_package_id' => $claimRevision->sub_package_id,
                'version'        => $claimRevision->version,
                'selected'       => $claimRevision->current_selected_revision,
                'locked_status'  => ( $claimRevision->locked_status ) ? 1 : 0,
                'updated_at'     => date('d M Y', strtotime($claimRevision->updated_at))
            );
        }

        $claimRevisionForm    = new SubPackagePostContractClaimRevisionForm();
        $data['form']         = array( 'csrf_token' => $claimRevisionForm->getCSRFToken() );
        $data['subPackageId'] = $subPackage->id;

        return $this->renderJson($data);
    }

    public function executeSaveClaimRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $isNew          = false;
        $claimRevisions = array();

        $claimRevisionRecord = Doctrine_Core::getTable('SubPackagePostContractClaimRevision')->find($request->getParameter('revisionId'));
        $claimRevisionRecord = $claimRevisionRecord ? : new SubPackagePostContractClaimRevision();
        $form                = new SubPackagePostContractClaimRevisionForm($claimRevisionRecord);

        if ( $this->isFormValid($request, $form) )
        {
            if ( $claimRevisionRecord->isNew() )
            {
                $isNew = true;
            }

            $claim   = $form->save();
            $errors  = null;
            $success = true;
            $form    = new BaseForm();

            $item = array(
                'id'            => $claim->id,
                'locked_status' => ( $claim->locked_status ) ? 1 : 0,
                'updated_at'    => date('d M Y', strtotime($claim->updated_at)),
                '_csrf_token'   => $form->getCSRFToken()
            );

            if ( $isNew )
            {
                $subPackage->refresh(true);

                $claims = $subPackage->getClaimRevisions()->toArray();

                foreach ( $claims as $claim )
                {
                    $claimRevisions[] = array(
                        'id'             => $claim['id'],
                        'version'        => $claim['version'],
                        'sub_package_id' => $claim['sub_package_id'],
                        'selected'       => $claim['current_selected_revision'],
                        'locked_status'  => ( $claim['locked_status'] ) ? 1 : 0,
                        'updated_at'     => date('d M Y', strtotime($claim['updated_at']))
                    );
                }
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $item    = array();
            $success = false;
        }

        $claimRevisionForm = new SubPackagePostContractClaimRevisionForm();

        $data         = array( 'success' => $success, 'errors' => $errors, 'item' => $item, 'claimRevisions' => $claimRevisions );
        $data['form'] = array( 'csrf_token' => $claimRevisionForm->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeAssignNewSelectedRevision(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id')) and
            $claimRevision = Doctrine_Core::getTable('SubPackagePostContractClaimRevision')->find($request->getParameter('revisionId'))
        );

        $claimRevisions = array();
        $form           = new SubPackagePostContractClaimRevisionForm($claimRevision, array( 'type' => 'assignSelectedRevision' ));

        if ( $this->isFormValid($request, $form) )
        {
            $pdo = $claimRevision->getTable()->getConnection()->getDbh();

            $sql  = "UPDATE " . SubPackagePostContractClaimRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = false WHERE (sub_package_id = :subPackageId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'subPackageId' => $subPackage->id ));

            $sql  = "UPDATE " . SubPackagePostContractClaimRevisionTable::getInstance()->getTableName() . " SET current_selected_revision = true WHERE (id = :revisionId)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array( 'revisionId' => $claimRevision->id ));

            $errors  = null;
            $success = true;

            $subPackage->refresh();

            $claims = $subPackage->getClaimRevisions()->toArray();

            foreach ( $claims as $claim )
            {
                $claimRevisions[] = array(
                    'id'             => $claim['id'],
                    'version'        => $claim['version'],
                    'sub_package_id' => $claim['sub_package_id'],
                    'selected'       => $claim['current_selected_revision'],
                    'locked_status'  => ( $claim['locked_status'] ) ? 1 : 0,
                    'updated_at'     => date('d M Y', strtotime($claim['updated_at']))
                );
            }
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        $claimRevisionForm = new SubPackagePostContractClaimRevisionForm();
        $data              = array( 'success' => $success, 'errors' => $errors, 'claimRevisions' => $claimRevisions );
        $data['form']      = array( 'csrf_token' => $claimRevisionForm->getCSRFToken() );

        return $this->renderJson($data);
    }

    public function executeGetBillInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $data['markup_settings'] = array(
            'bill_markup_enabled'    => $structure->BillMarkupSetting->bill_markup_enabled,
            'bill_markup_percentage' => number_format($structure->BillMarkupSetting->bill_markup_percentage, 2, '.', ''),
            'bill_markup_amount'     => number_format($structure->BillMarkupSetting->bill_markup_amount, 2, '.', ''),
            'element_markup_enabled' => $structure->BillMarkupSetting->element_markup_enabled,
            'item_markup_enabled'    => $structure->BillMarkupSetting->item_markup_enabled,
            'rounding_type'          => $structure->BillMarkupSetting->rounding_type
        );

        $data['bill_type'] = array(
            'id'   => $structure->BillType->id,
            'type' => $structure->BillType->type
        );

        $data['column_settings'] = DoctrineQuery::create()->select('c.id, c.name, c.quantity, c.is_hidden,
            c.total_floor_area_m2, c.total_floor_area_ft2, c.floor_area_has_build_up, c.floor_area_use_metric,
            c.floor_area_display_metric, c.show_estimated_total_cost, c.remeasurement_quantity_enabled, c.use_original_quantity')
            ->from('BillColumnSetting c')
            ->where('c.project_structure_id = ?', $structure->id)
            ->addOrderBy('c.id ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $data['claim_project_revision_status'] = SubPackagePostContractClaimRevisionTable::getCurrentProjectRevision($subPackage);

        // get selected printable BQ's Claim Version
        $data['current_selected_claim_project_revision_status'] = SubPackagePostContractClaimRevisionTable::getCurrentSelectedProjectRevision($subPackage);

        // addendum printing csrf protection
        $addendumCSRF = new BaseForm();

        $data['bqCSRFToken'] = $addendumCSRF->getCSRFToken();

        return $this->renderJson($data);
    }

    public function executeMainInfoForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and
            $structure = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('id')) and
            $subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('sub_package_id'))
        );

        $data = array(
            'sub_package_title' => $subPackage->name,
            'selected_company'  => $subPackage->SelectedCompany->name,
            'title'             => $structure->title,
            'description'       => $structure->MainInformation->description,
            'region'            => ProjectMainInformation::getCountryNameById($structure->MainInformation->region_id),
            'subregion'         => ProjectMainInformation::getStateNameById($structure->MainInformation->subregion_id),
            'work_category'     => ProjectMainInformation::getWorkCategoryById($structure->MainInformation->work_category_id),
            'site_address'      => $structure->MainInformation->site_address,
            'client'            => $structure->MainInformation->client,
            'start_date'        => $structure->MainInformation->start_date ? date('Y-m-d', strtotime($structure->MainInformation->start_date)) : date('Y-m-d')
        );

        if ( $structure->MainInformation->currency_id )
        {
            $data['currency'] = $structure->MainInformation->Currency->currency_code;
        }

        $data['isProjectOwner'] = ( $structure->created_by == $this->getUser()->getGuardUser()->getId() ) ? true : false;
        $data['isSuperAdmin']   = $this->getUser()->getGuardUser()->getIsSuperAdmin();

        return $this->renderJson($data);
    }

}