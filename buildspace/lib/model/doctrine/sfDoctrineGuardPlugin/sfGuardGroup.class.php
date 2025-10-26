<?php

class sfGuardGroup extends PluginsfGuardGroup
{
    public function getUsers()
    {
        $eProjectUsers = Doctrine_Query::create()
            ->select('epu.id, epu.company_id AS company_id')
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
            if($eProjectUser['company_id'])
                $users[] = $eProjectUser['id'];
        }

        if(!$users)
            $users[] = -1;

        return Doctrine_Query::create()
            ->from('sfGuardUser u')
            ->leftJoin('u.Groups g')
            ->leftJoin('u.Profile p')
            ->whereIn('p.eproject_user_id', $users)
            ->andWhere('g.id = ?', $this->id)
            ->orderBy('u.id')
            ->execute();
    }
}
