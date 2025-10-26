<?php

class EProjectSubsidiary extends BaseEProjectSubsidiary
{
    public function getFullSubsidiaryName()
    {
        $subsidiaryStack = array( $this->name );

        $parent = $this->Parent;

        while( $parent )
        {
            $subsidiaryStack[] = $parent->name;
            $parent            = ($parent->parent_id) ? $parent->Parent : null;
        }

        $firstSubsidiary = array_pop($subsidiaryStack);

        $fullName = $firstSubsidiary;

        if( count($subsidiaryStack) > 0 )
        {
            $subsidiaryStack = array_reverse($subsidiaryStack);

            $fullName = "{$fullName} (" . implode(", ", $subsidiaryStack) . ")";
        }

        return $fullName;
    }

    public static function getSubsidiaryHierarchicalCollection($subsidiaryId)
    {
        $client = new GuzzleHttp\Client(array(
            'debug'    => false,
            'verify'   => sfConfig::get('app_guzzle_ssl_verification'),
            'base_uri' => sfConfig::get('app_e_project_url')
        ));

        try
        {
            $res                              = $client->post("buildspace/getSubsidiaryHierarchicalCollection", [
                'form_params' => [
                    'subsidiaryId' => $subsidiaryId,
                ]
            ]);

            $content                          = $res->getBody()->getContents();
            $jsonObj                          = json_decode($content);
            $subsidiaryHierarchicalCollection = ($jsonObj) ? $jsonObj->subsidiaryHierarchicalCollection : null;
        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $subsidiaryHierarchicalCollection;
    }

    public function getParentsOfSubsidiary()
    {
        if(is_null($this->parent_id))
        {
            return null;
        }

        $parentSubsidiaries = [];
        $currentSubsidiary = $this;
        $continueSearch = true;

        while($continueSearch)
        {
            $isRoot = is_null($currentSubsidiary->parent_id);

            if($isRoot)
            {
                $continueSearch = false;
            }
            else
            {
                $currentSubsidiary = Doctrine_Core::getTable('EProjectSubsidiary')->find($currentSubsidiary->parent_id);
                array_push($parentSubsidiaries, $currentSubsidiary);
            }
        }

        return array_reverse($parentSubsidiaries);
    }

    public static function getInformationOfSelectedSubsidiaries($selectedSubsidiaries)
    {
        $parentsInformation = [];

        foreach($selectedSubsidiaries as $subsidiary)
        {
            $parentsOfSubsidiary = $subsidiary->getParentsOfSubsidiary();

            if(!is_null($parentsOfSubsidiary))
            {
                foreach($parentsOfSubsidiary as $parentSub)
                {
                    if(empty($parentsInformation))
                    {
                        $parentsInformation[$parentSub->id] = [
                            'subsidiary_code' => $parentSub->identifier,
                        ];
    
                        continue;
                    }
    
                    if(!array_key_exists($parentSub->id, $parentsInformation))
                    {
                        $parentsInformation[$parentSub->id] = [
                            'subsidiary_code' => $parentSub->identifier,
                        ];
                    }
                }
            }

            $parentsInformation[$subsidiary->id] = [
                'subsidiary_code' => $subsidiary->identifier,
            ];
        }

        return $parentsInformation;
    }

    /**
     * construct parents' hierarchy from phase subsidiaries
     * @param array subsidiaryIds of phase subsidiaries
     */
    public static function constructHierarchyGroupedByLevel($subsidiaryIds, $forRendering = false)
    {
        if(count($subsidiaryIds) == 0) return [];

        $selectedSubsidiaries = [];
        $hierarchyGroupedByLevel = [];

        foreach($subsidiaryIds as $subId)
        {
            array_push($selectedSubsidiaries, Doctrine_Core::getTable('EProjectSubsidiary')->find($subId));
        }

        // get any phase subsidiary and find the parents' depth
        $depth = count($selectedSubsidiaries[0]->getParentsOfSubsidiary());

        if($depth == 0)
        {
            if($forRendering)
            {
                $hierarchyGroupedByLevel[0] = [];
                array_push($hierarchyGroupedByLevel[0], $selectedSubsidiaries[0]->id);
            }
            else
            {
                return [];
            }
        }
        else
        {
            for($i = 0; $i < $depth; $i++)
            {
                $hierarchyGroupedByLevel[$i] = [];
            }
    
            foreach($selectedSubsidiaries as $subsidiary)
            {
                $currentLevel = 0;
                $parentsOfSubsidiary = $subsidiary->getParentsOfSubsidiary();
    
                if(is_null($parentsOfSubsidiary)) continue;
    
                foreach($parentsOfSubsidiary as $parentSub)
                {
                    if(array_search($parentSub->id, $hierarchyGroupedByLevel[$currentLevel]) === false)
                    {
                        array_push($hierarchyGroupedByLevel[$currentLevel], $parentSub->id);
                    }
    
                    ++$currentLevel;
                }
            }
        }

        return $hierarchyGroupedByLevel;
    }

    public static function getSubsidiaryFromEproject($subsidiaryId)
    {
        $record = DoctrineQuery::create()
                    ->select('*')
                    ->from('EProjectSubsidiary s')
                    ->where('s.id = ?', $subsidiaryId)
                    ->fetchOne();

        return $record;
    }

    public static function getDirectChildrenOf($subsidiaryId)
    {
        $children = DoctrineQuery::create()
                            ->select('*')
                            ->from('EProjectSubsidiary eps')
                            ->where('eps.parent_id = ?', $subsidiaryId)
                            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                            ->execute();

        return $children;
    }
}