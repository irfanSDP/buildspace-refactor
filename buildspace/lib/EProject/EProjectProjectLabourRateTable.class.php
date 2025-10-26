<?php

class EProjectProjectLabourRateTable extends Doctrine_Table
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EProjectProjectLabourRate');
    }

    public static function updateProjectLabourRates(ProjectStructure $project, $data)
    {
        $eprojectProject = $project->MainInformation->getEProjectProject();

        $records = DoctrineQuery::create()->select('*')
            ->from('EProjectProjectLabourRate r')
            ->where('r.project_id = ?', $eprojectProject->id)
            ->execute();

        foreach($records as $record)
        {
            $record->normal_working_hours = $data['normal_working_hours'];

            switch($record->labour_type)
            {
                case EProjectProjectLabourRate::LABOUR_TYPE_SKILL:
                    $labourType = 'skilled';
                    break;
                case EProjectProjectLabourRate::LABOUR_TYPE_SEMI_SKILL:
                    $labourType = 'semi_skilled';
                    break;
                case EProjectProjectLabourRate::LABOUR_TYPE_LABOUR:
                    $labourType = 'labour';
                    break;
                default:
                    throw new Exception('Invalid labour type');
            }
            $record->normal_rate_per_hour = $data[$labourType]['normal_rate_per_hour'];
            $record->ot_rate_per_hour = $data[$labourType]['ot_rate_per_hour'];
            $record->pre_defined_location_code_id = $data['trade'];
            $record->contractor_id = $data['contractor'];
            $record->save();
        }
    }

    public static function getProjectLabourRates(ProjectStructure $project)
    {
        $eprojectProject = $project->MainInformation->getEProjectProject();

        $rates = array();

        if(!$eprojectProject) return $rates;

        $records = DoctrineQuery::create()->select('*')
            ->from('EProjectProjectLabourRate r')
            ->where('r.project_id = ?', $eprojectProject->id)
            ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
            ->execute();

        foreach($records as $record)
        {
            $rates['hours'] = strval($record['normal_working_hours']);

            switch($record['labour_type'])
            {
                case EProjectProjectLabourRate::LABOUR_TYPE_SKILL:
                    $rates['skilled'] = array(
                        'normal' => strval($record['normal_rate_per_hour']),
                        'ot'     => strval($record['ot_rate_per_hour']),
                    );
                    break;
                case EProjectProjectLabourRate::LABOUR_TYPE_SEMI_SKILL:
                    $rates['semi_skilled'] = array(
                        'normal' => strval($record['normal_rate_per_hour']),
                        'ot'     => strval($record['ot_rate_per_hour']),
                    );
                    break;
                case EProjectProjectLabourRate::LABOUR_TYPE_LABOUR:
                    $rates['labour'] = array(
                        'normal' => strval($record['normal_rate_per_hour']),
                        'ot'     => strval($record['ot_rate_per_hour']),
                    );
                    break;
                default:
                    throw new Exception('Invalid labour type');
            }
        }

        return $rates;
    }

    public static function getProjectLabourRateRecords(ProjectStructure $project, $tradeId)
    {
        $eprojectProject = $project->MainInformation->getEProjectProject();

        $rates = array("hours" => 0, "skilled" => array('normal' => 0, 'ot' => 0), "semi_skilled" => array('normal' => 0, 'ot' => 0), "labour" => array('normal' => 0, 'ot' => 0));

        if(!$eprojectProject) return $rates;

        $q = DoctrineQuery::create()->select('*')
        ->from('EProjectProjectLabourRate r')
        ->where('r.project_id = ?', $eprojectProject->id);

        if(!empty($tradeId) && is_numeric($tradeId))
        {
            $q->andWhere('r.pre_defined_location_code_id = ?', $tradeId);
        }
        
        $records = $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

        if( empty($records) )
        {
            if( $eprojectProject->isSubProject() )
            {
                $q = DoctrineQuery::create()->select('*')
                ->from('EProjectProjectLabourRate r')
                ->where('r.project_id = ?', $eprojectProject->parent_project_id);

                if(!empty($tradeId) && is_numeric($tradeId))
                {
                    $q->andWhere('r.pre_defined_location_code_id = ?', $tradeId);
                }

                $mainProjectRecords = $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

                $records = $mainProjectRecords;

                if( empty($mainProjectRecords) )
                {
                    $subProjects = DoctrineQuery::create()->select('*')
                    ->from('EProjectProject e')
                    ->where('e.parent_project_id = ?', $eprojectProject->parent_project_id)
                    ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                    ->execute();

                    foreach($subProjects as $subProject)
                    {
                        $q = DoctrineQuery::create()->select('*')
                        ->from('EProjectProjectLabourRate r')
                        ->where('r.project_id = ?', $subProject['id']);

                        if(!empty($tradeId) && is_numeric($tradeId))
                        {
                            $q->andWhere('r.pre_defined_location_code_id = ?', $tradeId);
                        }

                        $subProjectRecords = $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

                        if( ! empty($subProjectRecords) )
                        {
                            $records = $subProjectRecords;
                        }
                    }
                }
            }
            else
            {
                $subProjects = DoctrineQuery::create()->select('*')
                ->from('EProjectProject e')
                ->where('e.parent_project_id = ?', $eprojectProject->parent_project_id)
                ->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
                ->execute();

                foreach($subProjects as $subProject)
                {
                    $subProjectRecords = DoctrineQuery::create()->select('*')
                    ->from('EProjectProjectLabourRate r')
                    ->where('r.project_id = ?', $subProject['id']);

                    if(!empty($tradeId) && is_numeric($tradeId))
                    {
                        $q->andWhere('r.pre_defined_location_code_id = ?', $tradeId);
                    }
                    
                    $subProjectRecords = $q->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)->execute();

                    if( ! empty($subProjectRecords) )
                    {
                        $records = $subProjectRecords;
                    }
                }
            }
        }

        if(isset($records))
        {
            foreach($records as $record)
            {
                $rates['hours'] = strval($record['normal_working_hours']);

                switch($record['labour_type'])
                {
                    case EProjectProjectLabourRate::LABOUR_TYPE_SKILL:
                        $rates['skilled'] = array(
                            'normal' => strval($record['normal_rate_per_hour']),
                            'ot'     => strval($record['ot_rate_per_hour']),
                        );
                        break;
                    case EProjectProjectLabourRate::LABOUR_TYPE_SEMI_SKILL:
                        $rates['semi_skilled'] = array(
                            'normal' => strval($record['normal_rate_per_hour']),
                            'ot'     => strval($record['ot_rate_per_hour']),
                        );
                        break;
                    case EProjectProjectLabourRate::LABOUR_TYPE_LABOUR:
                        $rates['labour'] = array(
                            'normal' => strval($record['normal_rate_per_hour']),
                            'ot'     => strval($record['ot_rate_per_hour']),
                        );
                        break;
                    default:
                        throw new Exception('Invalid labour type');
                }
            }
        }

        return $rates;
    }
}
