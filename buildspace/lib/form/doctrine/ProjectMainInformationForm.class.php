<?php

/**
 * ProjectMainInformation form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectMainInformationForm extends BaseProjectMainInformationForm
{
    public function configure()
    {
        parent::configure();

        unset($this['status'], $this['deleted_at'], $this['created_at'], $this['updated_at'], $this['project_structure_id'], $this['eproject_origin_id']);

        $this->projectStructure = $this->getOption('projectStructure');
        $this->parent = $this->getOption('parent');

        $this->setValidator('title', new sfValidatorString(
            array('required' => true),
            array('required' => 'Title is required')
        ));

        $this->setValidator('site_address', new sfValidatorString(
            array('required' => true),
            array('required' => 'Site Address is required')
        ));
    }

    public function setEprojectValidator()
    {
        $this->setValidator('eproject_origin_id', new sfValidatorInteger(
            array('required' => true)
        ));
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        parent::bind($taintedValues, $taintedFiles);
    }

    public function doSave($con=null)
    {
        if($this->projectStructure)
        {
            DoctrineQuery::create()
                ->update('ProjectStructure')
                ->set('priority', 'priority + 1')
                ->where('priority >= ?', 0)
                ->execute();

            $this->projectStructure->type     = ProjectStructure::TYPE_ROOT; //to factor
            $this->projectStructure->title    = $this->getValue('title');
            $this->projectStructure->priority = 0;
            $this->projectStructure->save();

            $this->object->ProjectStructure = $this->projectStructure;
            $this->projectStructure->getTable()->getTree()->createRoot($this->projectStructure);
        }

        if ( $this->object->isNew() )
        {
            $this->object->status = ProjectMainInformation::STATUS_PRETENDER;

            $projectRevision                            = new ProjectRevision();
            $projectRevision->project_structure_id      = $this->projectStructure->id;
            $projectRevision->current_selected_revision = true;
            $projectRevision->save($con);

            $user = sfGuardUserTable::getInstance()->find($this->projectStructure->created_by);

            if( $user && !$user->is_super_admin)
            {
                $userPermission                       = new ProjectUserPermission();
                $userPermission->project_structure_id = $this->projectStructure->id;
                $userPermission->project_status       = ProjectUserPermission::STATUS_PROJECT_BUILDER;
                $userPermission->user_id              = $this->projectStructure->created_by;
                $userPermission->is_admin             = true;

                $userPermission->save($con);
            }
        }

        parent::doSave($con);

        $projectStructure        = $this->object->ProjectStructure;
        $projectStructure->title = $this->object->title;
        $projectStructure->save($con);

        $eProject = $this->object->getEProjectProject();

        $eProject->title       = $this->object->title;
        $eProject->address     = $this->object->site_address;
        $eProject->description = $this->object->description;

        $eProjectCountry = EProjectCountryTable::getCountryByName($this->object->Regions->country);

        if(!$eProjectCountry)
        {
            $eProjectCountry = new EProjectCountry();

            $eProjectCountry->iso           = $this->object->Regions->iso;
            $eProjectCountry->iso3          = $this->object->Regions->iso3;
            $eProjectCountry->fips          = $this->object->Regions->fips;
            $eProjectCountry->country       = $this->object->Regions->country;
            $eProjectCountry->continent     = $this->object->Regions->continent;
            $eProjectCountry->currency_code = $this->object->Regions->currency_code;
            $eProjectCountry->currency_name = $this->object->Regions->currency_name;
            $eProjectCountry->phone_prefix  = $this->object->Regions->phone_prefix;
            $eProjectCountry->postal_code   = $this->object->Regions->postal_code;
            $eProjectCountry->languages     = $this->object->Regions->languages;
            $eProjectCountry->geonameid     = $this->object->Regions->geonameid;

            $eProjectCountry->save();
        }

        $eProject->country_id = $eProjectCountry->id;

        $eProjectState = EProjectStateTable::getStateByName($this->object->Subregions->name);

        if(!$eProjectState)
        {
            $eProjectState = new EProjectState();

            $eProjectState->country_id = $eProjectCountry->id;
            $eProjectState->name       = $this->object->Subregions->name;
            $eProjectState->timezone   = $this->object->Subregions->timezone;

            $eProjectState->save();
        }

        $eProject->state_id = $eProjectState->id;

        $eProjectWorkCategory = EProjectWorkCategoryTable::getWorkCategoryByName($this->object->WorkCategory->name);

        if(!$eProjectWorkCategory)
        {
            $name = trim($this->object->WorkCategory->name);

            $eProjectWorkCategory = new EProjectWorkCategory();
            $eProjectWorkCategory->name = $name;
            $eProjectWorkCategory->identifier = substr(str_replace(' ', '', $name), 10);//temp identifier before we replave it with its id
            $eProjectWorkCategory->save();
            
            $eProjectWorkCategory->identifier = sprintf('%05d', $eProjectWorkCategory->id);
            $eProjectWorkCategory->save();
        }

        $eProject->work_category_id = $eProjectWorkCategory->id;

        if(!empty($eProject->modified_currency_code) || (empty($eProject->modified_currency_code) && (strtolower(trim($this->getObject()->Currency->currency_code)) != strtolower(trim($eProjectCountry->currency_code)))))
        {
            $eProject->modified_currency_code = trim($this->getObject()->Currency->currency_code);
            $eProject->modified_currency_name = trim($this->getObject()->Currency->currency_name);
        }

        $eProject->save();
    }
}
