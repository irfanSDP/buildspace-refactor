<?php

/**
 * systemAdministration actions.
 *
 * @package    buildspace
 * @subpackage systemAdministration
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class systemAdministrationActions extends BaseActions {

    public function executeGetSystemAdministrationMenu()
    {
        return $this->renderJson(array(
            'identifier' => 'name',
            'label'      => 'name',
            'items'      => array(
                array(
                    'name' => "Company Profile",
                    'slug' => "CompanyProfileMaintenance",
                    'app'  => true
                ),
                array(
                    'name'       => "User Management",
                    'slug'       => "UserManagement",
                    'app'        => false,
                    'parent'     => true,
                    '__children' => array(
                        array(
                            'name' => "User Maintenance",
                            'slug' => "UserMaintenance",
                            'app'  => true
                        ),
                        array(
                            'name' => "Group Maintenance",
                            'slug' => "GroupMaintenance",
                            'app'  => true
                        )
                    ),
                )
            )
        ));
    }

    // =========================================================================================================================================
    // User Management
    // =========================================================================================================================================
    public function executeGetUserLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $eProjectUsers = Doctrine_Query::create()
            ->select('epu.id, epu.email AS email, epu.name, epc.name AS company, cgc.name AS group')
            ->from('EProjectUser epu')
            ->leftJoin('epu.Company epc')
            ->leftJoin('epc.ContractGroupCategory cgc')
            ->andWhere('epu.allow_access_to_buildspace IS TRUE')
            ->andWhere('epu.account_blocked_status IS FALSE')
            ->orderBy('epc.name, epu.name')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $users = array();

        foreach($eProjectUsers as $eProjectUser)
        {
            $users[$eProjectUser['id']] = array(
                'id'           => null,
                'name'         => $eProjectUser['name'],
                'email'        => $eProjectUser['email'],
                'company'      => $eProjectUser['company'],
                'company_role' => $eProjectUser['group'] ? $this->getContext()->getI18N()->__($eProjectUser['group'], null, 'contractTypes') : ""
            );
        }

        unset($eProjectUsers);

        $sfGuardUserProfiles = Doctrine_Query::create()
            ->from('sfGuardUserProfile p')
            ->orderBy('p.id')
            ->execute();

        foreach($sfGuardUserProfiles as $sfGuardUserProfile)
        {
            if(array_key_exists($sfGuardUserProfile->eproject_user_id, $users))
            {
                $users[$sfGuardUserProfile->eproject_user_id]['id'] = $sfGuardUserProfile->user_id;
                $users[$sfGuardUserProfile->eproject_user_id]['assignedGroups'] = $sfGuardUserProfile->sfGuardUser->getAssignedGroups();
                $users[$sfGuardUserProfile->eproject_user_id]['is_super_admin'] = $sfGuardUserProfile->sfGuardUser->is_super_admin;
            }
        }

        foreach($users as $key => $user)
        {
            if(!$user['id'])
                unset($users[$key]); //remove user who doesn't has access to buildspace
        }

        $users = array_values($users); // 'reindex' users

        array_push($users, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'email'          => '',
            'company'        => '',
            'assignedGroups' => '',
            'updated_at'     => '',
            'is_super_admin' => false,
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $users
        ));
    }

    public function executeGetUserInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $user = Doctrine_Core::getTable('sfGuardUser')->find($request->getParameter('id'))
        );

        $form = new BaseForm();

        return $this->renderJson(array(
            'id'             => $user->id,
            'name'           => $user->Profile->name,
            'email'          => $user->email_address,
            'company'        => $user->Profile->getEProjectUser()->Company->name,
            'company_role'   => $user->Profile->getEProjectUser()->company_id ? $this->getContext()->getI18N()->__($user->Profile->getEProjectUser()->Company->ContractGroupCategory->name, null, 'contractTypes') : "",
            'contact_number' => $user->Profile->contact_num,
            'profile_img'    => $user->Profile->getPhoto(),
            'assignedGroups' => $user->getAssignedGroups(),
            'is_super_admin' => $user->is_super_admin,
            '_csrf_token'    => $form->getCSRFToken(),
            'success'        => true
        ));
    }

    public function executeDisableAccessToBuildspace(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $user = sfGuardUserTable::getInstance()->find($request->getPostParameter('id'))
        );

        $eProjectUser = $user->Profile->getEProjectUser();

        $con = $eProjectUser->getTable()->getConnection();

        try
        {
            $con->beginTransaction();

            $eProjectUser->allow_access_to_buildspace = false;
            $eProjectUser->save($con);

            $con->commit();

            $errorMsg = "";
            $success = true;
        }
        catch (Exception $e)
        {
            $con->rollback();
            $errorMsg = $e->getMessage();
            $success = false;
        }

        return $this->renderJson(array('success' => $success, 'err' => $errorMsg ));
    }
    // =========================================================================================================================================

    // =========================================================================================================================================
    // Company Profile Management
    // =========================================================================================================================================
    public function executeGetCompanyProfile(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $companyProfile = Doctrine_Core::getTable('myCompanyProfile')->find(1);
        $companyProfile = ( $companyProfile ) ? $companyProfile : new myCompanyProfile();

        $form     = new myCompanyProfileForm($companyProfile);
        $formName = $form->getName();

        return $this->renderJson(array(
            "company_logo"              => $form->getObject()->company_logo,
            "{$formName}[name]"         => $form->getObject()->name,
            "{$formName}[address]"      => $form->getObject()->address,
            "{$formName}[city]"         => $form->getObject()->city,
            "{$formName}[region_id]"    => $form->getObject()->region_id,
            "{$formName}[subregion_id]" => $form->getObject()->subregion_id,
            "{$formName}[zipcode]"      => $form->getObject()->zipcode,
            "{$formName}[timezone]"     => $form->getObject()->timezone,
            "{$formName}[email]"        => $form->getObject()->email,
            "{$formName}[phone_number]" => $form->getObject()->phone_number,
            "{$formName}[fax_number]"   => $form->getObject()->fax_number,
            "{$formName}[website]"      => $form->getObject()->website,
            "{$formName}[_csrf_token]"  => $form->getCSRFToken(),
            'timezones'                 => Utilities::getTimezones(),
        ));
    }

    public function executeUploadMyCompanyLogo(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        sfConfig::set('sf_web_debug', false);
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'I18N', 'Asset', 'Url', 'Tag' ));

        $companyProfile = Doctrine_Core::getTable('myCompanyProfile')->find(1);
        $companyProfile = ( $companyProfile ) ? $companyProfile : new myCompanyProfile();

        $form = new MyCompanyLogoUploadForm($companyProfile);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $success         = true;
            $imgURL          = image_path('company_logo/' . $form->getObject()->company_logo);
            $companyLogoName = $form->getObject()->company_logo;
            $errorMsgs       = null;
        }
        else
        {
            $success         = false;
            $imgURL          = null;
            $safeFileName    = null;
            $companyLogoName = null;
            $errorMsgs       = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'imgURL' => $imgURL, 'imgName' => $companyLogoName, 'errorMsgs' => $errorMsgs ));
    }

    public function executeUpdateCompanyProfile(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post')
        );

        $companyProfile = Doctrine_Core::getTable('myCompanyProfile')->find(1);
        $companyProfile = ( $companyProfile ) ? $companyProfile : new myCompanyProfile();
        $form           = new myCompanyProfileForm($companyProfile);

        if ( $this->isFormValid($request, $form) )
        {
            $form->save();

            $success   = true;
            $errorMsgs = array();
        }
        else
        {
            $success   = false;
            $errorMsgs = $form->getErrors();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsgs' => $errorMsgs ));
    }
    // =========================================================================================================================================

    // =========================================================================================================================================
    // Group Management
    // =========================================================================================================================================
    public function executeGetGroupLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $data = array();

        $groups = Doctrine_Query::create()
            ->from('sfGuardGroup u')
            ->orderBy('u.id')
            ->execute();

        $form = new BaseForm();

        foreach ( $groups as $group )
        {
            $data[] = array(
                'id'             => $group->id,
                'name'           => $group->name,
                'usersCount'     => $group->getUsers()->count(),
                'updated_at'     => date('d/m/Y H:i', strtotime($group->updated_at)),
                'is_super_admin' => $group->is_super_admin,
                '_csrf_token'    => $form->getCSRFToken(),
            );
        }

        array_push($data, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'usersCount'     => '',
            'updated_at'     => '',
            'is_super_admin' => false,
            '_csrf_token'    => $form->getCSRFToken(),
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $group = Doctrine_Core::getTable('sfGuardGroup')->find($request->getParameter('id'));
        $group = ( $group ) ? $group : new sfGuardGroup();
        $form  = new sfGuardGroupForm($group);

        $data[] = array(
            'id'                          => ( !$group->isNew() ) ? $group->id : - 1,
            'sf_guard_group[name]'        => ( !$group->isNew() ) ? $group->name : null,
            'sf_guard_group[_csrf_token]' => $form->getCSRFToken(),
            'menus'                       => self::getMenus($group),
            'name'                        => ( !$group->isNew() ) ? $group->name : null,
        );

        return $this->renderJson($data);
    }

    public function executeUpdateGroupInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post')
        );

        $group = Doctrine_Core::getTable('sfGuardGroup')->find($request->getPostParameter('id'));
        $group = ( $group ) ? $group : new sfGuardGroup();

        $form = new sfGuardGroupForm($group);

        if ( $this->isFormValid($request, $form) )
        {
            $group   = $form->save();
            $id      = $group->getId();
            $name    = $form->getObject()->name;
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $name    = null;
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'name' => $name, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeDeleteGroupInfo(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $group = Doctrine_Core::getTable('sfGuardGroup')->find($request->getPostParameter('id'))
        );

        try
        {
            $group->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $success = false;
        }

        return $this->renderJson(array( 'id' => $request->getPostParameter('id'), 'success' => $success ));
    }

    public function executeGetMenuLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest()
        );

        $roots = Doctrine_Query::create()->select('m.id, m.lft, m.rgt')->from('Menu m')
            ->where('m.id = m.root_id')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->orderBy('m.priority ASC')
            ->execute();

        $data = array();

        foreach ( $roots as $root )
        {
            $menus = Doctrine_Query::create()->select('m.id, m.title, m.is_app, m.level')->from('Menu m')
                ->where('m.root_id = ?', $root['id'])
                ->andWhere('m.lft >= ? AND m.rgt <= ?', array( $root['lft'], $root['rgt'] ))
                ->orderBy('m.lft ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            $data = array_merge($data, $menus);
        }

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetMenuDescendants(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $menuItem = Doctrine_Core::getTable('Menu')->find($request->getParameter('id'))
        );

        $ancestors = array();

        try
        {
            $node      = $menuItem->getNode();
            $ancestors = ( $node->getAncestors() ) ? $node->getAncestors()->toArray() : array();

            $items = DoctrineQuery::create()->select('i.id')
                ->from('Menu i')
                ->where('i.root_id = ?', $menuItem->root_id)
                ->andWhere('i.lft >= ? AND i.rgt <= ?', array( $menuItem->lft, $menuItem->rgt ))
                ->addOrderBy('i.lft')
                ->fetchArray();

            $success  = true;
            $errorMsg = null;
        }
        catch (Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $items    = array();
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'items' => array_merge($ancestors, $items) ));
    }

    public function executeGetGroupUserLists(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $group = Doctrine_Core::getTable('sfGuardGroup')->find($request->getParameter('id'))
        );

        $nameParam        = addcslashes($request->getParameter('n'), "%_");
        $companyParam     = addcslashes($request->getParameter('c'), "%_");

        $query = Doctrine_Query::create()
            ->select('epu.id, epu.email AS email, epu.name, epc.name AS company, cgc.name AS group')
            ->from('EProjectUser epu')
            ->leftJoin('epu.Company epc')
            ->leftJoin('epc.ContractGroupCategory cgc')
            ->where('epu.name ILIKE ?', array("%$nameParam%"))
            ->andWhere('epc.name ILIKE ?', array("%$companyParam%"));

        //need to be refactored because of changes in eproject
        /*if($request->getParameter('cr') > 0)
        {
            $query->andWhere('cg.group = ?', $request->getParameter('cr'));
        }*/

        $eProjectUsers = $query->andWhere('epu.allow_access_to_buildspace IS TRUE')
            ->andWhere('epu.account_blocked_status IS FALSE')
            ->orderBy('epc.name, epu.name')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        $users = array();
        foreach($eProjectUsers as $eProjectUser)
        {
            $users[$eProjectUser['id']] = array(
                'id'           => null,
                'name'         => $eProjectUser['name'],
                'email'        => $eProjectUser['email'],
                'company'      => $eProjectUser['company'],
                'company_role' => $eProjectUser['group'] ? $this->getContext()->getI18N()->__($eProjectUser['group'], null, 'contractTypes') : ""
            );
        }

        $groupUsers = array();

        $form = new UserGroupAssignmentForm($group);

        $usersWithGroup = $group->Users;

        foreach ( $usersWithGroup as $userWithGroup )
        {
            $groupUsers[$userWithGroup->id] = $userWithGroup->id;
        }

        $sfGuardUsers = Doctrine_Query::create()
            ->select('u.id AS user_id, p.eproject_user_id,')
            ->from('sfGuardUserProfile p')
            ->leftJoin('p.sfGuardUser u')
            ->orderBy('p.id')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($sfGuardUsers as $sfGuardUser)
        {
            if(array_key_exists($sfGuardUser['eproject_user_id'], $users))
            {
                $users[$sfGuardUser['eproject_user_id']]['id'] = $sfGuardUser['user_id'];
                $users[$sfGuardUser['eproject_user_id']]['_csrf_token'] = $form->getCSRFToken();
            }
        }

        unset($sfGuardUsers);

        foreach($users as $key => $user)
        {
            if(!$user['id'])
                unset($users[$key]); //remove user who doesn't has access to buildspace
        }

        $users = array_values($users); // 'reindex' users

        array_push($users, array(
            'id'           => Constants::GRID_LAST_ROW,
            'name'         => "",
            'company'      => "",
            'company_role' => "",
            'email'        => "",
            '_csrf_token'  => $form->getCSRFToken()
        ));

        return $this->renderJson(array( 'users' => array( $groupUsers ), 'data' => array(
            'identifier' => 'id',
            'items'      => $users
        )));
    }

    public function executeUpdateGroupUserInfo(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $group = Doctrine_Core::getTable('sfGuardGroup')->find($request->getPostParameter('id'))
        );

        $form = new UserGroupAssignmentForm($group);

        if ( $this->isFormValid($request, $form) )
        {
            $group   = $form->save($group->getTable()->getConnection());
            $id      = $group->getId();
            $success = true;
            $errors  = array();
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }
    // =========================================================================================================================================

    // =========================================================================================================================================
    public function executeGetBusinessTypes(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $data = array();

        $businessTypes = DoctrineQuery::create()
            ->select('m.id, m.name, m.updated_at')
            ->from('CompanyBusinessType m')
            ->addOrderBy('m.id DESC')
            ->fetchArray();

        $form = new BaseForm();

        foreach ( $businessTypes as $businessType )
        {
            $data[] = array(
                'id'          => $businessType['id'],
                'name'        => $businessType['name'],
                'updated_at'  => date('d/m/Y H:i', strtotime($businessType['updated_at'])),
                '_csrf_token' => $form->getCSRFToken(),
            );
        }

        array_push($data, array(
            'id'             => Constants::GRID_LAST_ROW,
            'name'           => '',
            'updated_at'     => '-',
            '_csrf_token'    => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $data
        ));
    }

    public function executeGetBusinessType(sfWebRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        if(!$businessType = Doctrine_Core::getTable('CompanyBusinessType')->find($request->getParameter('id')))
        {
            $businessType = new CompanyBusinessType();
        }

        $form = new CompanyBusinessTypeForm($businessType);

        return $this->renderJson(array(
            'id'                                 => $businessType->isNew() ? -1 : $businessType->id,
            'company_business_type[name]'        => $businessType->isNew() ? '' : $businessType->name,
            'company_business_type[_csrf_token]' => $form->getCSRFToken(),
        ));
    }

    public function executeBusinessTypeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post')
        );

        $businessType = Doctrine_Core::getTable('CompanyBusinessType')->find($request->getParameter('id'));
        $businessType = ( $businessType ) ? $businessType : new CompanyBusinessType();
        $form         = new CompanyBusinessTypeForm($businessType);

        if ( $this->isFormValid($request, $form) )
        {
            $businessType = $form->save();
            $id           = $businessType->getId();
            $success      = true;
            $errors       = array();
        }
        else
        {
            $id      = $request->getPostParameter('id');
            $errors  = $form->getErrors();
            $success = false;
        }

        return $this->renderJson(array( 'id' => $id, 'success' => $success, 'errorMsgs' => $errors ));
    }

    public function executeBusinessTypePreDelete(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $businessType = Doctrine_Core::getTable('CompanyBusinessType')->find($request->getParameter('id'))
        );

        return $this->renderJson(array(
            'can_delete' => CompanyBusinessTypeTable::canBeDeletedById($businessType->id)
        ));
    }

    public function executeDeleteBusinessType(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() AND
            $request->isMethod('post') AND
            $businessType = Doctrine_Core::getTable('CompanyBusinessType')->find($request->getParameter('id'))
        );

        try
        {
            $businessType->delete();

            $success = true;
        }
        catch (Exception $e)
        {
            $success = false;
        }

        return $this->renderJson(array( 'id' => $request->getPostParameter('id'), 'success' => $success ));
    }

    // =========================================================================================================================================

    protected function  getMenus($group)
    {
        $menus = array();

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