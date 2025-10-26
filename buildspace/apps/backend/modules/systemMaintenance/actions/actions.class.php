<?php

/**
 * systemMaintenance actions.
 *
 * @package    buildspace
 * @subpackage systemMaintenance
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class systemMaintenanceActions extends BaseActions {

    // =========================================================================================================================================
    // Region Maintenance
    // =========================================================================================================================================

    public function executeGetRegions(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $regions = DoctrineQuery:: create()
            ->select(
                'r.id,
                r.iso,
                r.iso3,
                r.fips,
                r.country,
                r.continent,
                r.currency_code,
                r.currency_name,
                r.phone_prefix,
                r.postal_code,
                r.languages,
                r.geonameid')
            ->from('Regions r')
            ->orderBy('r.country ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $regions as $key => $region )
        {
            $regions[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $region );
        }

        array_push($regions, array(
            'id'            => Constants::GRID_LAST_ROW,
            'iso'           => '',
            'iso3'          => '',
            'fips'          => '',
            'country'       => '',
            'continent'     => '',
            'currency_code' => '',
            'currency_name' => '',
            'phone_prefix'  => '',
            'postal_code'   => '',
            'languages'     => '',
            'geonameid'     => ''
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $regions
        ));
    }

    public function executeRegionForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( !$region = Doctrine_Core::getTable('Regions')->find($request->getParameter('id')) )
        {
            $region = new Regions();
        }

        $form = new RegionsForm($region);

        return $this->renderJson(array(
            'id'                     => $region->isNew() ? - 1 : $region->id,
            'regions[iso]'           => $region->isNew() ? '' : $region->iso,
            'regions[iso3]'          => $region->isNew() ? '' : $region->iso3,
            'regions[fips]'          => $region->isNew() ? '' : $region->fips,
            'regions[country]'       => $region->isNew() ? '' : $region->country,
            'regions[continent]'     => $region->isNew() ? '' : $region->continent,
            'regions[currency_code]' => $region->isNew() ? '' : $region->currency_code,
            'regions[currency_name]' => $region->isNew() ? '' : $region->currency_name,
            'regions[phone_prefix]'  => $region->isNew() ? '' : $region->phone_prefix,
            'regions[postal_code]'   => $region->isNew() ? '' : $region->postal_code,
            'regions[languages]'     => $region->isNew() ? '' : $region->languages,
            'regions[geonameid]'     => $region->isNew() ? '' : $region->geonameid,
            'regions[_csrf_token]'   => $form->getCSRFToken()
        ));
    }

    public function executeRegionUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new RegionsForm(Doctrine_Core::getTable('Regions')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeRegionPreDelete(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $region = Doctrine_Core::getTable('Regions')->find($request->getParameter('id'))
        );

        return $this->renderJson(array(
            'can_delete' => RegionsTable::canBeDeletedById($region->id)
        ));
    }

    public function executeRegionDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $region = Doctrine_Core::getTable('Regions')->find($request->getParameter('id'))
        );

        try
        {
            $region->delete();

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    // =========================================================================================================================================
    // =========================================================================================================================================

    //============================================= Work Category Maintenance==================================================================
    //=========================================================================================================================================
    public function executeGetWorkCategories()
    {
        $works = DoctrineQuery::create()->select('w.id, w.name, w.description,w.updated_at')
            ->from('WorkCategory w')
            ->orderBy('w.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $works as $key => $work )
        {
            $works[$key]['can_be_deleted'] = WorkCategoryTable::canBeDeletedById($work['id']);
            $works[$key]['updated_at']     = date('d/m/Y H:i', strtotime($work['updated_at']));
            $works[$key]['_csrf_token']    = $form->getCSRFToken();

            unset( $work );
        }

        array_push($works, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'description'    => '',
            'updated_at'     => '-',
            'can_be_deleted' => false,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $works
        ));
    }

    public function executeWorkCategoryUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $form = new WorkCategoryForm(Doctrine_Core::getTable('WorkCategory')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $work = $form->save();

            $errors  = null;
            $success = true;
            $values  = array(
                'id'                         => $work->id,
                'work_category[name]'        => $work->name,
                'work_category[description]' => $work->description,
                'work_category[_csrf_token]' => $form->getCSRFToken() // get the CSFFToken

            );
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeWorkCategoryForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( !$work = Doctrine_Core::getTable('WorkCategory')->find($request->getParameter('id')) )
        {
            $work = new WorkCategory();
        }

        $form = new WorkCategoryForm($work);

        return $this->renderJson(array(
            'id'                         => $work->isNew() ? - 1 : $work->id,
            'work_category[name]'        => $work->isNew() ? '' : $work->name,
            'work_category[description]' => $work->isNew() ? '' : $work->description,
            'work_category[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeWorkCategoryDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $work = Doctrine_Core::getTable('WorkCategory')->find($request->getParameter('id'))
        );

        try
        {
            EProjectWorkCategoryTable::deleteByName($work->name);

            $work->delete();

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    //========================================================================================================================================
    //========================================================================================================================================

    // =========================================================================================================================================
    // Sub Package Works
    // =========================================================================================================================================

    public function executeGetSubPackageWorks(sfWebRequest $request)
    {
        $works = DoctrineQuery::create()->select('w.id, w.name')
            ->from('SubPackageWorks w')
            ->where('w.type = ?', $request->getParameter('type'))
            ->orderBy('w.id DESC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $form = new BaseForm();

        foreach ( $works as $key => $work )
        {
            $works[$key]['can_be_deleted'] = SubPackageWorksTable::canBeDeletedById($work['id']);
            $works[$key]['_csrf_token']    = $form->getCSRFToken();

            unset( $work );
        }

        array_push($works, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'can_be_deleted' => false,
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $works
        ));
    }

    public function executeSubPackageWorkUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $form = new SubPackageWorksForm(Doctrine_Core::getTable('SubPackageWorks')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $work = $form->save();

            $errors  = null;
            $success = true;
            $values  = array(
                'id'                             => $work->id,
                'sub_package_works[name]'        => $work->name,
                'sub_package_works[_csrf_token]' => $form->getCSRFToken() // get the CSFFToken
            );
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
            $values  = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors, 'values' => $values ));
    }

    public function executeSubPackageWorkForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if ( !$work = Doctrine_Core::getTable('SubPackageWorks')->find($request->getParameter('id')) )
        {
            $work = new SubPackageWorks();
        }

        $form = new SubPackageWorksForm($work);

        return $this->renderJson(array(
            'id'                             => $work->isNew() ? -1 : $work->id,
            'sub_package_works[name]'        => $work->isNew() ? '' : $work->name,
            'sub_package_works[_csrf_token]' => $form->getCSRFToken()
        ));
    }

    public function executeSubPackageWorkDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $work = Doctrine_Core::getTable('SubPackageWorks')->find($request->getParameter('id'))
        );

        try
        {
            $work->delete();

            $errorMsg = null;
            $success  = true;
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg
        ));
    }

    //========================================================================================================================================
    //========================================================================================================================================

    // =========================================================================================================================================
    // Project summary default settings
    // =========================================================================================================================================
    public function executeGetProjectSummaryDefaultSettings(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $settings = Doctrine_Core::getTable('ProjectSummaryDefaultSetting')->find(1);

        $form = new ProjectSummaryDefaultSettingForm();

        return $this->renderJson(
            array(
                'settings'   => array(
                    'project_summary_default_setting[summary_title]'                     => $settings->summary_title,
                    'project_summary_default_setting[include_printing_date]'             => $settings->include_printing_date,
                    'project_summary_default_setting[carried_to_next_page_text]'         => $settings->carried_to_next_page_text,
                    'project_summary_default_setting[continued_from_previous_page_text]' => $settings->continued_from_previous_page_text,
                    'project_summary_default_setting[page_number_prefix]'                => $settings->page_number_prefix,
                    'project_summary_default_setting[first_row_text]'                    => $settings->first_row_text,
                    'project_summary_default_setting[second_row_text]'                   => $settings->second_row_text,
                    'project_summary_default_setting[_csrf_token]'                       => $form->getCSRFToken()

                ),
                'left_text'  => $settings->left_text,
                'right_text' => $settings->right_text
            )
        );
    }

    public function executeGetVoPrintingDefaultSettings(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $settings = Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1);

        $form = new VoFooterDefaultSettingForm();

        return $this->renderJson(
            array(
                'settings'   => array(
                    'vo_footer_default_setting[_csrf_token]' => $form->getCSRFToken()

                ),
                'left_text'  => $settings->left_text,
                'right_text' => $settings->right_text
            )
        );
    }

    public function executeUpdateProjectSummaryDefaultSettings(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $form = new ProjectSummaryDefaultSettingForm(Doctrine_Core::getTable('ProjectSummaryDefaultSetting')->find(1));

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeUpdateVoPrintingDefaultSettings(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $form = new VoFooterDefaultSettingForm(Doctrine_Core::getTable('VoFooterDefaultSetting')->find(1));

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeGetPredefinedLocationCodes(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $pdo = PreDefinedLocationCodeTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.name, p.root_id, p.level, p.priority, p.lft, p.updated_at
            FROM " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " c
            JOIN " . PreDefinedLocationCodeTable::getInstance()->getTableName() . " p ON c.lft BETWEEN p.lft AND p.rgt
            WHERE c.root_id = p.root_id AND c.deleted_at IS NULL AND p.deleted_at IS NULL
            ORDER BY p.priority, p.lft, p.level ASC");

        $stmt->execute();

        $locationCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        foreach ( $locationCodes as $key => $locationCode )
        {
            $locationCodes[$key]['type_txt']    = PreDefinedLocationCode::getTypeTextByLevel($locationCode['level']);
            $locationCodes[$key]['updated_at']  = date('d/m/Y H:i', strtotime($locationCode['updated_at']));
            $locationCodes[$key]['_csrf_token'] = $form->getCSRFToken();

            unset( $locationCode );
        }

        array_push($locationCodes, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'type_txt'    => '',
            'description' => '',
            'updated_at'  => '-',
            'level'       => 0,
            'lft'         => 0,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'label'      => 'name',
            'items'      => $locationCodes
        ));
    }

    public function executePredefinedLocationCodeAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $items    = array();
        $nextItem = null;
        $con      = PreDefinedLocationCodeTable::getInstance()->getConnection();

        try
        {
            $con->beginTransaction();

            $asRoot   = true;

            $location = new PreDefinedLocationCode();

            $previousLocation = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('PreDefinedLocationCode')->find(intval($request->getParameter('prev_item_id'))) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                if ( $fieldName )
                {
                    $columns = array_keys(PreDefinedLocationCodeTable::getInstance()->getColumns());
                    if ( in_array($fieldName, $columns))
                    {
                        $location->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                    }
                }

                $priority = 0;

                if ( $previousLocation )
                {
                    if ( $previousLocation->node->isRoot() )
                    {
                        $priority = $previousLocation->priority + 1;
                    }
                    else
                    {
                        $asRoot = false;

                        $location->node->insertAsNextSiblingOf($previousLocation);

                        $priority = $previousLocation->priority;
                    }
                }

                $location->priority = $priority;

                $location->save($con);
            }
            else
            {
                $this->forward404Unless($nextLocation = Doctrine_Core::getTable('PreDefinedLocationCode')->find(intval($request->getParameter('before_id'))));

                if ( $nextLocation->node->isRoot() )
                {
                    $priority = $nextLocation->priority;

                    $location->priority = $priority;

                    $location->save($con);

                    $asRoot = true;
                }
                else
                {
                    $asRoot = false;

                    $location->priority = $nextLocation->priority;

                    $location->node->insertAsPrevSiblingOf($nextLocation);
                }
            }

            if($asRoot)
            {
                $node = $location->node;

                if ( $node->isValidNode() )
                {
                    $node->makeRoot($location->id);
                }
                else
                {
                    $location->getTable()->getTree()->createRoot($location);
                }

                $location->updateRootPriority($priority, $location->id);
            }

            $location->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $form = new BaseForm();

            $location->refresh();

            array_push($items, array(
                'id'             => $location->id,
                'name'           => $location->name,
                'updated_at'     => date('d/m/Y H:i', strtotime($location->updated_at)),
                'lft'            => $location->lft,
                'level'          => $location->level,
                'type_txt'       => PreDefinedLocationCode::getTypeTextByLevel($location->level),
                '_csrf_token'    => $form->getCSRFToken()
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'             => Constants::GRID_LAST_ROW,
                    'name'           => '',
                    'updated_at'     => '-',
                    'lft'            => 0,
                    'level'          => 0,
                    'type_txt'       => '',
                    '_csrf_token'    => $form->getCSRFToken()
                ));
            }
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'items'    => $items,
            'errorMsg' => $errorMsg
        ));
    }

    public function executePredefinedLocationCodeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $location = Doctrine_Core::getTable('PreDefinedLocationCode')->find(intval($request->getParameter('id')))
        );

        $form = new BaseForm();
        $con = $location->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            if ( $fieldName )
            {
                $columns = array_keys(PreDefinedLocationCodeTable::getInstance()->getColumns());
                if ( in_array($fieldName, $columns))
                {
                    $location->{'set' . sfInflector::camelize($fieldName)}($fieldValue);
                }
            }

            $location->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $location->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'          => $location->id,
                'name'        => $location->name,
                'type_txt'    => PreDefinedLocationCode::getTypeTextByLevel($location->level),
                'lft'         => $location->lft,
                'level'       => $location->level,
                'updated_at'  => date('d/m/Y H:i', strtotime($location->updated_at)),
                '_csrf_token' => $form->getCSRFToken()
            )
        ));
    }

    public function executePredefinedLocationCodeIndent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $location = Doctrine_Core::getTable('PreDefinedLocationCode')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();

        try
        {
            if ( $location->indent() )
            {
                $data['id']         = $location->id;
                $data['level']      = $location->level;
                $data['type_txt']   = PreDefinedLocationCode::getTypeTextByLevel($location->level);
                $data['updated_at'] = date('d/m/Y H:i', strtotime($location->updated_at));

                $children = DoctrineQuery::create()->select('l.id, l.level, l.updated_at')
                    ->from('PreDefinedLocationCode l')
                    ->where('l.root_id = ?', $location->root_id)
                    ->andWhere('l.lft > ? AND l.rgt < ?', array( $location->lft, $location->rgt ))
                    ->addOrderBy('l.priority, l.lft, l.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                foreach($children as $key => $child)
                {
                    $children[$key]['type_txt'] = PreDefinedLocationCode::getTypeTextByLevel($child['level']);
                    $children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));
                }

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executePredefinedLocationCodeOutdent(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $location = Doctrine_Core::getTable('PreDefinedLocationCode')->find($request->getParameter('id'))
        );

        $success  = false;
        $children = array();
        $errorMsg = null;
        $data     = array();

        try
        {
            if ( $location->outdent() )
            {
                $data['id']         = $location->id;
                $data['level']      = $location->level;
                $data['type_txt']   = PreDefinedLocationCode::getTypeTextByLevel($location->level);
                $data['updated_at'] = date('d/m/Y H:i', strtotime($location->updated_at));

                $children = DoctrineQuery::create()->select('l.id, l.level, l.updated_at')
                    ->from('PreDefinedLocationCode l')
                    ->where('l.root_id = ?', $location->root_id)
                    ->andWhere('l.lft > ? AND l.rgt < ?', array( $location->lft, $location->rgt ))
                    ->addOrderBy('l.priority, l.lft, l.level')
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                foreach($children as $key => $child)
                {
                    $children[$key]['type_txt'] = PreDefinedLocationCode::getTypeTextByLevel($child['level']);
                    $children[$key]['updated_at'] = date('d/m/Y H:i', strtotime($child['updated_at']));
                }

                $success = true;
            }
        } catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'item' => $data, 'c' => $children ));
    }

    public function executePredefinedLocationCodeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $location = Doctrine_Core::getTable('PreDefinedLocationCode')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;

        $con = $location->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('l.id')
                ->from('PreDefinedLocationCode l')
                ->where('l.root_id = ?', $location->root_id)
                ->andWhere('l.lft >= ? AND l.rgt <= ?', array( $location->lft, $location->rgt ))
                ->addOrderBy('l.priority, l.lft, l.level')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $location->delete($con);

            $con->commit();

            $success = true;
        } catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => $items ));
    }

    public function executeRetentionSumCodeForm(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $retentionSumCode = Doctrine_Query::create()
            ->from('RetentionsumCode u')
            ->fetchOne();

        if(!$retentionSumCode)
        {
            $retentionSumCode = new RetentionSumCode();
            $retentionSumCode->code = 'RET001';

            $retentionSumCode->save();
        }

        $form = new RetentionSumCodeForm($retentionSumCode);

        return $this->renderJson([
            'id'                              => $retentionSumCode->id,
            'retention_sum_code[code]'        => $retentionSumCode->code,
            'retention_sum_code[_csrf_token]' => $form->getCSRFToken()
        ]);
    }

    public function executeRetentionSumCodeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new RetentionSumCodeForm(Doctrine_Core::getTable('RetentionSumCode')->find($request->getParameter('id')));

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $errors  = null;
            $success = true;
        }
        else
        {
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errors' => $errors ));
    }

    public function executeGetClaimCertificateTaxes(sfWebRequest $request) 
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new BaseForm();

        $claimCertTaxes = [];

        $claimCertTaxes = DoctrineQuery::create()
                            ->select('cct.id, cct.tax, cct.description, cct.priority, cct.updated_at')
                            ->from("ClaimCertificateTax cct")
                            ->addOrderBy('cct.priority ASC')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        foreach($claimCertTaxes as $key => $claimCertTax)
        {
            $claimCertTaxes[$key]['updated_at']  = date('d/m/Y H:i', strtotime($claimCertTax['updated_at']));
            $claimCertTaxes[$key]['_csrf_token'] = $form->getCSRFToken();

            unset($claimCertTax);
        }

        array_push($claimCertTaxes, array(
            'id'            => Constants::GRID_LAST_ROW,
            'tax'           => '',
            'description'   => '',
            'priority'      => '',
            'updated_at'    => '',
            '_csrf_token'   => $form->getCSRFToken(),
        ));
        
        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'tax',
            'items'         => $claimCertTaxes,
        ));
    }

    public function executeClaimCertificateTaxAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $items    = array();
        $con      = ClaimCertificateTaxTable::getInstance()->getConnection();
        
        try {
            $con->beginTransaction();

            $claimCertTax = new ClaimCertificateTax();
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;
            $previousClaimCertTax = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('ClaimCertificateTax')->find(intval($request->getParameter('prev_item_id'))) : null;
            $priority = 0;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                if($previousClaimCertTax)
                {
                    $priority = $previousClaimCertTax->priority + 1;
                }

                if($fieldName)
                {
                    $claimCertTax->{$fieldName} = $fieldValue;
                }

                $claimCertTax->priority = $priority;
                $claimCertTax->save($con);
            }
            else
            {
                $this->forward404Unless($nextClaimCertTax = Doctrine_Core::getTable('ClaimCertificateTax')->find(intval($request->getParameter('before_id'))));
                $priority = $nextClaimCertTax->priority;

                $claimCertTax->priority = $priority;
                $claimCertTax->save($con);

                $claimCertTax->updatePriority(true);            
            }

            $con->commit();

            $success    = true;
            $errorMsg   = null;
            $form       = new BaseForm();

            $claimCertTax->refresh();
            
            array_push($items, array(
                'id'                => $claimCertTax->id,
                'description'       => $claimCertTax->description,
                'tax'               => $claimCertTax->tax,
                'priority'          => $claimCertTax->priority,
                'updated_at'        => date('d/m/Y H:i', strtotime($claimCertTax->updated_at)),
                '_csrf_token'       => $form->getCSRFToken(),
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                => Constants::GRID_LAST_ROW,
                    'description'       => '',
                    'tax'               => '',
                    'priority'          => '',
                    'updated_at'        => '',
                    '_csrf_token'       => $form->getCSRFToken()
                ));
            }
        } catch(Exception $e) {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'items'     => $items,
            'errorMsg'  => $errorMsg,
        ));
    }

    public function executeClaimCertificateTaxUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertTax = Doctrine_Core::getTable('ClaimCertificateTax')->find(intval($request->getParameter('id')))
        );
        
        $form = new BaseForm();
        $con = $claimCertTax->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            if ( $fieldName )
            {
                $claimCertTax->{$fieldName} = $fieldValue;
            }

            $claimCertTax->save($con);
            $con->commit();

            $success  = true;
            $errorMsg = null;

            $claimCertTax->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }
        
        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'             => $claimCertTax->id,
                'tax'            => $claimCertTax->tax,
                'description'    => $claimCertTax->description,
                'priority'       => $claimCertTax->priority,
                'updated_at'     => date('d/m/Y H:i', strtotime($claimCertTax->updated_at)),
                '_csrf_token'    => $form->getCSRFToken()
            )
        ));
    }

    public function executeClaimCertificateTaxDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $claimCertTax = Doctrine_Core::getTable('ClaimCertificateTax')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;
        $con = $claimCertTax->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            if(!$claimCertTax->canBeDeleted())
            {
                throw new Exception();
            }

            $items = DoctrineQuery::create()->select('cct.id')
                        ->from('ClaimCertificateTax cct')
                        ->where('cct.id = ?', $claimCertTax->id)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

            $claimCertTax->delete($con);

            $claimCertTax->updatePriority(false);

            $con->commit();
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeGetAccountGroups(sfWebRequest $request) {
        $this->forward404Unless($request->isXmlHttpRequest());

        $form = new BaseForm();

        $accountGroups = [];

        $accountGroups = DoctrineQuery::create()
                            ->select('ac.id, ac.name, ac.priority, ac.disable, ac.updated_at')
                            ->from('AccountGroup ac')
                            ->addOrderBy('ac.priority ASC')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        foreach($accountGroups as $key => $accountGroup)
        {
            $accountGroups[$key]['disable']  = $accountGroup['disable'] ? 'Yes' : 'No';

            $accountGroups[$key]['updated_at']  = date('d/m/Y H:i', strtotime($accountGroup['updated_at']));
            $accountGroups[$key]['_csrf_token'] = $form->getCSRFToken();

            unset($accountGroup);
        }

        array_push($accountGroups, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => '',
            'priority'    => '',
            'disable'      => '',
            'updated_at'   => '',
            '_csrf_token' => $form->getCSRFToken(),
        ));
        
        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'name',
            'items'         => $accountGroups,
        ));
    }

    public function executeAccountGroupAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post')
        );

        $items    = array();
        $con      = AccountGroupTable::getInstance()->getConnection();
        
        try {
            $con->beginTransaction();

            $accountGroup = new AccountGroup();
            $previousAccountGroup = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('prev_item_id'))) : null;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

                $priority = 0;

                if($previousAccountGroup)
                {
                    $priority = $previousAccountGroup->priority + 1;
                }

                $accountGroup->name = $fieldValue ;
                $accountGroup->priority = $priority;
                $accountGroup->save($con);
            }
            else
            {
                $this->forward404Unless($nextAccountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('before_id'))));

                $accountGroup->priority = $nextAccountGroup->priority;
                $accountGroup->save($con);

                $accountGroup->updatePriority(true);
            }

            $con->commit();

            $success    = true;
            $errorMsg   = null;
            $form       = new BaseForm();

            $accountGroup->refresh();
            
            array_push($items, array(
                'id'            => $accountGroup->id,
                'name'          => $accountGroup->name,
                'priority'      => $accountGroup->priority,
                'updated_at'    => date('d/m/Y H:i', strtotime($accountGroup->updated_at)),
                '_csrf_token'   => $form->getCSRFToken(),
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'             => Constants::GRID_LAST_ROW,
                    'name'           => '',
                    'priority'       => '',
                    'updated_at'     => '',
                    '_csrf_token'    => $form->getCSRFToken()
                ));
            }
        } catch(Exception $e) {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'items'     => $items,
            'errorMsg'  => $errorMsg,
        ));
    }

    public function executeAccountGroupDisable(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('id')))
        );
        
        $form = new BaseForm();
        $con = $accountGroup->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('ag.id')
                                ->from('AccountGroup ag')
                                ->where('ag.id = ?', $accountGroup->id)
                                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                                ->execute();

            
            $accountGroup->disable = true;
            $accountGroup->save($con);
            $con->commit();

            $success  = true;
            $errorMsg = null;

            $accountGroup->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeAccountGroupEnable(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('id')))
        );
        
        $form = new BaseForm();
        $con = $accountGroup->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $items = DoctrineQuery::create()->select('ag.id')
                                ->from('AccountGroup ag')
                                ->where('ag.id = ?', $accountGroup->id)
                                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                                ->execute();

            
            $accountGroup->disable = false;
            $accountGroup->save($con);
            $con->commit();

            $success  = true;
            $errorMsg = null;

            $accountGroup->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeAccountGroupUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('id')))
        );
        
        $form = new BaseForm();
        $con = $accountGroup->getTable()->getConnection();

        try
        {
            $con->beginTransaction();
            
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            $accountGroup->name = $fieldValue;
            $accountGroup->save($con);
            $con->commit();

            $success  = true;
            $errorMsg = null;

            $accountGroup->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }
        
        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'             => $accountGroup->id,
                'name'           => $accountGroup->name,
                'priority'       => $accountGroup->priority,
                'updated_at'     => date('d/m/Y H:i', strtotime($accountGroup->updated_at)),
                '_csrf_token'    => $form->getCSRFToken()
            )
        ));
    }

    public function executeAccountGroupDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;
        $con = $accountGroup->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            if(!$accountGroup->canBeDeleted())
            {
                throw new Exception();
            }

            $items = DoctrineQuery::create()->select('ag.id')
                        ->from('AccountGroup ag')
                        ->where('ag.id = ?', $accountGroup->id)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

            $accountGroup->delete($con);

            $accountGroup->updatePriority(false);

            $con->commit();
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    public function executeGetAccountCodes(sfWebRequest $request) {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );

        $form = new BaseForm();

        $accountCodes = [];

        $accountCodes = DoctrineQuery::create()
                            ->select('ac.id, ac.account_group_id, ac.code, ac.description, ac.tax_code, ac.type, ac.priority, ac.updated_at')
                            ->from('AccountCode ac')
                            ->where('ac.account_group_id = ?', $accountGroup->id)
                            ->addOrderBy('ac.priority ASC')
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        foreach($accountCodes as $key => $accountCode)
        {
            $accountCodes[$key]['updated_at']  = date('d/m/Y H:i', strtotime($accountCode['updated_at']));
            $accountCodes[$key]['_csrf_token'] = $form->getCSRFToken();

            unset($accountCode);
        }

        array_push($accountCodes, array(
            'id'                => Constants::GRID_LAST_ROW,
            'account_group_id'  => '',
            'code'              => '',
            'description'       => '',
            'tax_code'          => '',
            'type'              => AccountCode::ACCOUNT_TYPE_PIC,
            'priority'          => '',
            'updated_at'        => '',
            '_csrf_token'       => $form->getCSRFToken(),
        ));
        
        return $this->renderJson(array(
            'identifier'    => 'id',
            'label'         => 'name',
            'items'         => $accountCodes,
        ));
    }

    public function executeAccountCodeAdd(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountGroup = Doctrine_Core::getTable('AccountGroup')->find(intval($request->getParameter('accountGroupId')))
        );

        $items    = array();
        $con      = AccountCodeTable::getInstance()->getConnection();

        try {
            $con->beginTransaction();

            $accountCode = new AccountCode();
            $previousAccountCode = $request->getParameter('prev_item_id') > 0 ? Doctrine_Core::getTable('AccountCode')->find(intval($request->getParameter('prev_item_id'))) : null;
            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;
            $priority = 0;

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                if($previousAccountCode)
                {
                    $priority = $previousAccountCode->priority + 1;
                }
                
                if($fieldName)
                {
                    $accountCode->{$fieldName} = $fieldValue;
                }

                if($fieldName !== 'type')
                {
                    $accountCode->type = AccountCode::ACCOUNT_TYPE_PIC;
                }

                $accountCode->account_group_id = $accountGroup->id;
                $accountCode->priority = $priority;
                $accountCode->save($con);
            }
            else
            {
                $this->forward404Unless($nextAccountCode = Doctrine_Core::getTable('AccountCode')->find(intval($request->getParameter('before_id'))));
                $priority = $nextAccountCode->priority;

                $accountCode->account_group_id = $accountGroup->id;
                $accountCode->priority = $priority;
                $accountCode->save($con);

                $accountCode->updatePriority(true);
            }

            $con->commit();

            $success    = true;
            $errorMsg   = null;
            $form       = new BaseForm();

            $accountCode->refresh();
            
            array_push($items, array(
                'id'  => $accountCode->id,
                'account_group_id'  => $accountCode->account_group_id,
                'code'              => $accountCode->code,
                'description'       => $accountCode->description,
                'tax_code'          => $accountCode->tax_code,
                'type'              => $accountCode->type,
                'priority'          => $accountCode->priority,
                'updated_at'        => date('d/m/Y H:i', strtotime($accountCode->updated_at)),
                '_csrf_token'       => $form->getCSRFToken(),
            ));

            if ( $request->hasParameter('id') and $request->getParameter('id') == Constants::GRID_LAST_ROW )
            {
                array_push($items, array(
                    'id'                => Constants::GRID_LAST_ROW,
                    'account_group_id'  => '',
                    'code'              => '',
                    'description'       => '',
                    'tax_code'          => '',
                    'type'              => AccountCode::ACCOUNT_TYPE_PIC,
                    'priority'          => '',
                    'updated_at'        => '',
                    '_csrf_token'    => $form->getCSRFToken()
                ));
            }
        } catch(Exception $e) {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'items'     => $items,
            'errorMsg'  => $errorMsg,
        ));
    }

    public function executeAccountCodeUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountCode = Doctrine_Core::getTable('AccountCode')->find(intval($request->getParameter('id')))
        );

        $accountCode = Doctrine_Core::getTable('AccountCode')->find(intval($request->getParameter('id')));

        $form = new BaseForm();
        $con = $accountCode->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $fieldName  = $request->hasParameter('attr_name') ? $request->getParameter('attr_name') : null;
            $fieldValue = $request->hasParameter('val') ? $request->getParameter('val') : null;

            if ( $fieldName )
            {
                $accountCode->{$fieldName} = $fieldValue;
            }

            $accountCode->save($con);

            $con->commit();

            $success = true;

            $errorMsg = null;

            $accountCode->refresh();
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'  => $success,
            'errorMsg' => $errorMsg,
            'data'     => array(
                'id'                => $accountCode->id,
                'account_group_id'  => $accountCode->account_group_id,
                'code'              => $accountCode->code,
                'description'       => $accountCode->description,
                'tax_code'          => $accountCode->tax_code,
                'type'              => $accountCode->type,
                'priority'          => $accountCode->priority,
                'updated_at'        => date('d/m/Y H:i', strtotime($accountCode->updated_at)),
                '_csrf_token'       => $form->getCSRFToken(),
            )
        ));
    }

    public function executeAccountCodeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $accountCode = Doctrine_Core::getTable('AccountCode')->find(intval($request->getParameter('id')))
        );

        $items    = array();
        $errorMsg = null;
        $con = $accountCode->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            if(!$accountCode->canBeDeleted())
            {
                throw new Exception();
            }

            $items = DoctrineQuery::create()->select('ac.id')
                        ->from('AccountCode ac')
                        ->where('ac.id = ?', $accountCode->id)
                        ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                        ->execute();

            $accountCode->delete($con);

            $accountCode->updatePriority(false);
            
            $con->commit();
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array(
            'success'   => $success,
            'errorMsg'  => $errorMsg,
            'items'     => $items,
        ));
    }

    // =========================================================================================================================================
    // =========================================================================================================================================

    protected function getMenus($group)
    {
        $menus[] = array();

        if ( !$group->isNew() AND count($group['Menus']) > 0 )
        {
            foreach ( $group['Menus'] as $menu )
            {
                $menus[$menu->id] = $menu->id;
            }
        }

        return $menus;
    }

}
