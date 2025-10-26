<?php

class sfGuardUser extends PluginsfGuardUser {

    public function save(Doctrine_Connection $conn = null)
    {
        $isNew = $this->isNew() ? true : false;

        if ( $isNew )
        {
            $this->getTable()->getUserCount();
        }

        parent::save($conn);
    }

    public function getMenuItems()
    {
        $collection = array();
        $menuIds    = array();

        if ( !$this->is_super_admin )
        {
            $userGroups = Doctrine_Query::create()
                ->select('g.id, ug.group_id, gm.menu_id')
                ->from('sfGuardUserGroup ug')
                ->leftJoin('ug.Group g')
                ->leftJoin('g.sfGuardGroupMenu gm ON gm.group_id = ug.group_id')
                ->where('ug.user_id = ?', $this->id)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

            if ( count($userGroups) == 0 )
            {
                return $collection;
            }

            foreach ( $userGroups as $userGroup )
            {
                foreach ( $userGroup['Group']['sfGuardGroupMenu'] as $userMenu )
                {
                    $menuIds[$userMenu['menu_id']] = $userMenu['menu_id'];
                }
            }

            if ( count($menuIds) == 0 )
            {
                return $collection;
            }
        }

        $query = Doctrine_Query::create()
            ->select('m.id, m.title, m.icon, m.is_app, m.sysname, m.level, m.lft, m.rgt')
            ->from('Menu m')
            ->andWhere('m.root_id = m.id')
            ->andWhere('m.level = 0')
            ->orderBy('m.priority ASC')
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY_HIERARCHY);

        if ( !$this->is_super_admin )
        {
            $query->andWhereIn('m.id', $menuIds);
        }

        $mainMenus = $query->execute();

        foreach ( $mainMenus as $key => $mainMenu )
        {
            $query2 = Doctrine_Query::create()
                ->select('m.id, m.title, m.icon, m.is_app, m.sysname, m.level, m.priority')
                ->from('Menu m')
                ->andWhere('m.root_id = ?', $mainMenu['id'])
                ->andWhere('m.lft > ?', $mainMenu['lft'])
                ->andWhere('m.rgt < ?', $mainMenu['rgt'])
                ->orderBy('m.root_id, m.lft, m.priority ASC')
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY_HIERARCHY);

            if ( !$this->is_super_admin )
            {
                $query2->andWhereIn('m.id', $menuIds);
            }

            $subMenus = $query2->execute();

            $mainMenus[$key]['__children'] = $subMenus;
        }

        $collection = array_merge($collection, $mainMenus);

        return $collection;
    }

    public function getAssignedGroups()
    {
        $data       = array();
        $userGroups = $this->getGroups();

        if ( count($userGroups) == 0 )
        {
            return '-';
        }

        foreach ( $userGroups as $userGroup )
        {
            $data[] = $userGroup->name;
        }

        return implode(', ', $data);
    }

    public function delete(Doctrine_Connection $conn = null)
    {
        // delete user's profile information
        $this->Profile->delete();

        parent::delete($conn);
    }

    public function isAdminForProject(ProjectStructure $project, $status=ProjectUserPermission::STATUS_PROJECT_BUILDER)
    {
        if($this->is_super_admin)
            return true;

        $pdo = $this->getTable()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT COALESCE(is_admin, false)
            FROM ".ProjectUserPermissionTable::getInstance()->getTableName()." up
            WHERE up.user_id = ".$this->id." AND up.project_structure_id = ".$project->id." AND up.project_status = ".$status);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function canAccessEditorByProject(ProjectStructure $project)
    {
        $client = new GuzzleHttp\Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post("buildspace/canAccessBqEditor/project/{$project->MainInformation->eproject_origin_id}/user/{$this->Profile->eproject_user_id}");

            return json_decode($res->getBody())->canAccess;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function canAccessMasterCostData($masterCostDataId)
    {
        $client = new GuzzleHttp\Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post("buildspace/canAccessMasterCostData/masterCostData/{$masterCostDataId}/user/{$this->Profile->eproject_user_id}");

            return json_decode($res->getBody())->canAccess;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function canAccessCostData($costDataId)
    {
        $client = new GuzzleHttp\Client(array(
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res = $client->post("buildspace/canAccessCostData/costData/{$costDataId}/user/{$this->Profile->eproject_user_id}");

            return array(
                'canAccess' => json_decode($res->getBody())->canAccess,
                'isEditor'  => json_decode($res->getBody())->isEditor,
            );
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function hasProjectUserPermission(ProjectStructure $project, $projectStatus)
    {
        if ( $this->is_super_admin ) return true;

        return in_array($this->id, array_column(ProjectUserPermissionTable::getAssignedUserIdsByProjectAndStatus($project, $projectStatus), 'id'));
    }

    public function hasMenuItemAccess($menuItemName)
    {
        if ( $this->is_super_admin ) return true;

        $pdo = sfGuardUserTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT COUNT(m.id)
            FROM " . sfGuardUserGroupTable::getInstance()->getTableName() . " ug
            JOIN " . sfGuardGroupMenuTable::getInstance()->getTableName() . " gm ON gm.group_id = ug.group_id
            JOIN " . MenuTable::getInstance()->getTableName() . " m ON m.id = gm.menu_id
            WHERE ug.user_id = :userId
            AND m.sysname = :menuItemName;");

        $stmt->execute(array('userId' => $this->id, 'menuItemName' => $menuItemName));

        $result = $stmt->fetch(PDO::FETCH_COLUMN);

        return $result > 0;
    }
}
