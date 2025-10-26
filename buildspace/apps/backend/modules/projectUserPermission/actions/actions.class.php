<?php

/**
 * projectUserPermission actions.
 *
 * @package    buildspace
 * @subpackage projectUserPermission
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectUserPermissionActions extends BaseActions
{
    public function executeGetGroupsBySysName(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('sys')
        );

        $pdo = sfGuardGroupTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT g.id, g.name
            FROM ".sfGuardGroupTable::getInstance()->getTableName()." g
            JOIN ".sfGuardGroupMenuTable::getInstance()->getTableName()." gm ON gm.group_id = g.id
            JOIN ".MenuTable::getInstance()->getTableName()." m ON gm.menu_id = m.id
            WHERE m.sysname = '". $request->getParameter('sys')."' AND m.level = 0 ORDER BY g.name");

        $stmt->execute();

        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $form = new BaseForm();

        $groups[] = array(
            'id'   => Constants::GRID_LAST_ROW,
            'name' => ""
        );

        return $this->renderJson(array(
            'items' => $groups,
            '_csrf_token' => $form->getCSRFToken()
        ));
    }

    public function executeGetUsersByGroup(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->hasParameter('st') and
            $projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $sfGuardGroup = sfGuardGroupTable::getInstance()->find($request->getParameter('gid'));

        $users = array();
        $form = new BaseForm();

        if($sfGuardGroup instanceof sfGuardGroup)
        {
            $pdo = sfGuardGroupTable::getInstance()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT u.id, p.name, u.email_address AS email
            FROM ".sfGuardUserProfileTable::getInstance()->getTableName()." p
            JOIN ".sfGuardUserTable::getInstance()->getTableName()." u ON p.user_id = u.id
            JOIN ".sfGuardUserGroupTable::getInstance()->getTableName()." ug ON ug.user_id = u.id
            WHERE ug.group_id = ".$sfGuardGroup->id." AND u.is_super_admin IS FALSE
            AND p.eproject_user_id IN (" . implode(',', EProjectUserTable::getEProjectUserIds()) . ")
            AND p.deleted_at IS NULL AND u.deleted_at IS NULL
            AND u.is_active IS TRUE ORDER BY p.name");

            $stmt->execute();

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT p.user_id AS id, p.is_admin
            FROM ".ProjectUserPermissionTable::getInstance()->getTableName()." p
            JOIN ".sfGuardUserTable::getInstance()->getTableName()." u ON p.user_id = u.id
            WHERE p.project_structure_id = ".$projectStructure->id." AND p.project_status = ".$request->getParameter('st')."
            AND u.is_super_admin IS FALSE AND u.deleted_at IS NULL AND u.is_active IS TRUE ORDER BY u.id");

            $stmt->execute();

            $assignedUsers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach($users as $key => $user)
            {
                $users[$key]['is_admin'] = false;

                if(isset($assignedUsers[$user['id']]))
                {
                    $users[$key]['is_admin'] = $assignedUsers[$user['id']];
                }

                $users[$key]['_csrf_token'] = $form->getCSRFToken();
            }
        }

        array_push($users, array(
            'id'          => Constants::GRID_LAST_ROW,
            'name'        => "",
            'email'       => "",
            'is_admin'    => false,
            '_csrf_token' => $form->getCSRFToken()
        ));

        return $this->renderJson(array(
            'identifier' => 'id',
            'items'      => $users
        ));
    }

    public function executeGetAssignedUsersByGroup(sfWebRequest $request)
    {
        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('id')) and
            $group = sfGuardGroupTable::getInstance()->find($request->getParameter('group'))
        );

        $assignedUserIds = ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatusAndGroup($projectStructure, $group, $request->getParameter('st'));

        return $this->renderJson($assignedUserIds);
    }

    public function executeUpdateUserPermission(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('st') and
            $projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('id'))
        );

        $errorMsg = null;

        try
        {
            $uid1 = Utilities::array_filter_integer($request->getParameter('uid1'));
            $uid2 = Utilities::array_filter_integer($request->getParameter('uid2'));
            $selectedUserIds = ! empty( $uid1 ) ? $uid1 : array();
            $deselectedUserIds = ! empty( $uid2 ) ? $uid2 : array();

            ProjectUserPermissionTable::assignUsersPermission($projectStructure, $request->getParameter('st'), $selectedUserIds, $deselectedUserIds);

            $assignedUserIds = ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatus($projectStructure, $request->getParameter('st'));

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg        = $e->getMessage();
            $success         = false;
            $assignedUserIds = array();
        }

        return $this->renderJson(array('success' => $success, 'ids' => $assignedUserIds, 'errorMsg' => $errorMsg ));
    }

    public function executeIsAdminUpdate(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $request->isMethod('post') and
            $request->hasParameter('st') and
            $user = sfGuardUserTable::getInstance()->find($request->getParameter('id')) and
            $projectStructure = ProjectStructureTable::getInstance()->find($request->getParameter('pid'))
        );

        $errorMsg = null;

        try
        {
            $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT p.user_id, p.is_admin
            FROM ".ProjectUserPermissionTable::getInstance()->getTableName()." p
            JOIN ".sfGuardUserTable::getInstance()->getTableName()." u ON p.user_id = u.id
            WHERE p.project_structure_id = ".$projectStructure->id." AND p.project_status = ".$request->getParameter('st')."
            AND u.id = ".$user->id."
            AND u.deleted_at IS NULL AND u.is_active IS TRUE ORDER BY u.id");

            $stmt->execute();

            $record = $stmt->fetch(PDO::FETCH_KEY_PAIR);

            if(empty($record))
            {
                ProjectUserPermissionTable::assignUsersPermission($projectStructure, $request->getParameter('st'), array($user->id), array());
                $isAdmin = true;
            }
            else
            {
                $isAdmin = $record[$user->id] ? false : true;
            }

            DoctrineQuery::create()
                ->update('ProjectUserPermission')
                ->set('is_admin', $isAdmin ? 'TRUE' : 'FALSE')
                ->where('project_structure_id = ?', $projectStructure->id)
                ->andWhere('project_status = ?', $request->getParameter('st'))
                ->andWhere('user_id = ?', $user->id)
                ->execute();

            $assignedUserIds = ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatus($projectStructure, $request->getParameter('st'));

            $success = true;
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
            $isAdmin  = false;
            $assignedUserIds = array();
        }

        return $this->renderJson(array('success' => $success, 'ids' => $assignedUserIds, 'is_admin' => $isAdmin, 'errorMsg' => $errorMsg ));
    }
}
