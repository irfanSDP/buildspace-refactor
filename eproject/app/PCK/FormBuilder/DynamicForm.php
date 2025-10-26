<?php namespace PCK\FormBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\Elements\Element;
use PCK\FormBuilder\Elements\SystemModuleElement;
use PCK\FormBuilder\ElementRejection;
use PCK\Verifier\Verifiable;
use PCK\Verifier\Verifier;
use PCK\Companies\Company;
use PCK\VendorRegistration\FormTemplateMapping\VendorRegistrationFormTemplateMapping;
use PCK\VendorRegistration\VendorRegistration;
use PCK\FormBuilder\Elements\FileUpload;
use PCK\FormBuilder\Elements\RadioBox;
use PCK\FormBuilder\Elements\CheckBox;
use PCK\FormBuilder\Elements\Dropdown;
use PCK\VendorManagement\VendorManagementUserPermission;

class DynamicForm extends Model implements Verifiable
{
    protected $table = 'dynamic_forms';

    const STATUS_OPEN                        = 1;
    const STATUS_DESIGN_PENDING_FOR_APPROVAL = 2;
    const STATUS_DESIGN_APPROVED             = 4;
    const STATUS_VENDOR_SUBMITTED            = 8;
    const STATUS_VENDOR_SUBMISSION_APPROVED  = 16;

    const VENDOR_REGISTRATION_IDENTIFIER = 1;

    const SUBMISSION_STATUS_INITIAL            = 1;
    const SUBMISSION_STATUS_SUBMITTED          = 2;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $model)
        {
            $model->deleteRelatedModels();
        });

        static::updated(function(self $model)
        {
            // update VendorRegistrationFormTemplateMapping when a form has a new revision and the form design is approved
            // only works on template forms with previous revision
            if($model->isTemplate() && $model->isRevisedForm() && $model->isFormDesignApproved())
            {
                VendorRegistrationFormTemplateMapping::updateMappedFormToLatestRevision($model);
            }
        });
    }

    public function formObjectMapping()
    {
        return $this->hasOne('PCK\FormBuilder\FormObjectMapping', 'dynamic_form_id');
    }

    public function columns()
    {
        return $this->hasMany('PCK\FormBuilder\FormColumn', 'dynamic_form_id');
    }

    public function isTemplate()
    {
        return $this->is_template;
    }

    public function isRoot()
    {
        return is_null($this->root_id);
    }

    public function isOriginalForm()
    {
        return ($this->revision == 0);
    }

    public function isRevisedForm()
    {
        return ($this->revision > 0);
    }

    public function isOpenForEditing()
    {
        return ($this->status == self::STATUS_OPEN);
    }

    public function isDesignPendingForApproval()
    {
        return ($this->status == self::STATUS_DESIGN_PENDING_FOR_APPROVAL);
    }

    public function isFormDesignApproved()
    {
        return ($this->status == self::STATUS_DESIGN_APPROVED);
    }

    public function isVendorSubmitted()
    {
        return ($this->status == self::STATUS_VENDOR_SUBMITTED);
    }

    public function isVendorSubmissionApproved()
    {
        return ($this->status == self::STATUS_VENDOR_SUBMISSION_APPROVED);
    }

    public function isVendorSubmissionStatusInitial()
    {
        return ($this->submission_status == self::SUBMISSION_STATUS_INITIAL);
    }

    public function isVendorSubmissionStatusSubmitted()
    {
        return ($this->submission_status == self::SUBMISSION_STATUS_SUBMITTED);
    }

    public function isRenewalForm()
    {
        return ($this->is_renewal_form);
    }

    public function isRenewalApprovalRequired()
    {
        return ($this->renewal_approval_required);
    }

    public function getPreviousRevisionForm()
    {
        if($this->isOriginalForm()) return null;

        return self::where('root_id', $this->root_id)->where('revision', ($this->revision - 1))->first();
    }

    public function getLatestFormRevision()
    {
        return self::where('root_id', $this->root_id)->orderBy('revision', 'DESC')->first();
    }

    public function getIndexRouteByIdentifier()
    {
        $route = null;

        switch($this->module_identifier)
        {
            case self::VENDOR_REGISTRATION_IDENTIFIER:
                $route = route('vendor.registration.forms.library.index');
                break;
        }

        return $route;
    }

    public function getOriginalTemplateForm()
    {
        $originId     = $this->origin_id;
        $templateForm = null;
        $found        = false;

        $currentOriginForm = self::find($originId);

        do
        {
            $currentOriginForm = self::find($originId);

            if($currentOriginForm->isTemplate())
            {
                $templateForm = $currentOriginForm;
                $found = true;
            }
            else
            {
                $originId = $currentOriginForm->origin_id;
            }
        } while($found == false);

        return $templateForm;
    }

    public static function createNewForm($name, $moduleIdentifier, $isModuleForm)
    {
        $dynamicForm                    = new self();
        $dynamicForm->module_identifier = $moduleIdentifier;
        $dynamicForm->name              = $name;
        $dynamicForm->is_template       = $isModuleForm ? false : true;
        $dynamicForm->revision          = 0;
        $dynamicForm->status            = $isModuleForm ? self::STATUS_DESIGN_APPROVED : self::STATUS_OPEN;
        $dynamicForm->save();

        // root_id equals to id if revision is 0
        $dynamicForm          = self::find($dynamicForm->id);
        $dynamicForm->root_id = $dynamicForm->id;
        $dynamicForm->save();

        return self::find($dynamicForm->id);
    }

    // only templates can have revisions
    public static function createNewRevisedForm(self $originForm)
    {
        $rootId = $originForm->isRoot() ? $originForm->id : $originForm->root_id;

        $newForm                    = new self();
        $newForm->root_id           = $rootId;
        $newForm->module_identifier = $originForm->module_identifier;
        $newForm->name              = $originForm->name;
        $newForm->is_template       = true;
        $newForm->revision          = ($originForm->revision + 1);
        $newForm->save();

        foreach($originForm->columns()->orderBy('priority', 'ASC')->get() as $originColumn)
        {
            $originColumn->clone($newForm);
        }

        return self::find($newForm->id);
    }

    public function clone($name, $moduleIdentifier, $isModuleForm)
    {
        $newForm = self::createNewForm($name, $moduleIdentifier, $isModuleForm);

        if($isModuleForm)
        {
            $newForm->origin_id = $this->id;
            $newForm->save();

            $newForm = self::find($newForm->id);
        }

        foreach($this->columns()->orderBy('priority', 'ASC')->get() as $originColumn)
        {
            $originColumn->clone($newForm);
        }

        return $newForm;
    }

    private function deleteRelatedModels()
    {
        foreach($this->columns as $column)
        {
            $column->delete();
        }
    }

    public function getAllFormElementIdsGroupedByType()
    {
        $records[Element::ELEMENT_TYPE_ID]             = [];
        $records[SystemModuleElement::ELEMENT_TYPE_ID] = [];

        foreach($this->columns as $column)
        {
            foreach($column->sections as $section)
            {
                foreach($section->mappings as $mapping)
                {
                    if($mapping->element_class == SystemModuleElement::class)
                    {
                        array_push($records[SystemModuleElement::ELEMENT_TYPE_ID], $mapping->element_id);
                    }
                    else
                    {
                        array_push($records[Element::ELEMENT_TYPE_ID], $mapping->element_id);
                    }
                }
            }
        }

        return $records;
    }

    public function getKeyElementsGroupedByType()
    {
        $records[Element::ELEMENT_TYPE_ID]             = [];
        $records[SystemModuleElement::ELEMENT_TYPE_ID] = [];

        $elementIdsGroupedByType = $this->getAllFormElementIdsGroupedByType();

        foreach($elementIdsGroupedByType[Element::ELEMENT_TYPE_ID] as $id)
        {
            $element = Element::findById($id);

            if( ! $element->is_key_information ) continue;

            array_push($records[Element::ELEMENT_TYPE_ID], $element);
        }

        foreach($elementIdsGroupedByType[SystemModuleElement::ELEMENT_TYPE_ID] as $id)
        {
            $element = SystemModuleElement::find($id);

            if( ! $element->is_key_information ) continue;

            array_push($records[SystemModuleElement::ELEMENT_TYPE_ID], $element);
        }

        return $records;
    }

    public function hasRejectedElements()
    {
        $allFormElementIds   = $this->getAllFormElementIdsGroupedByType();
        $hasRejectedElements = false;

        $customElements = array_map(function($id) {
            return Element::findById($id);
        }, $allFormElementIds[Element::ELEMENT_TYPE_ID]);

        $systemElements = array_map(function($id) {
            return SystemModuleElement::find($id);
        }, $allFormElementIds[SystemModuleElement::ELEMENT_TYPE_ID]);

        $elements = array_merge($customElements, $systemElements);

        foreach($elements as $element)
        {
            if(ElementRejection::findRecordByElement($element))
            {
                $hasRejectedElements = true;
                break;
            }
        }

        return $hasRejectedElements;
    }

    public static function getSelectableFormsByModule($moduleIdentifier)
    {
        $formTemplates = [];

        foreach(self::where('is_template', true)->where('revision', 0)->where('module_identifier', $moduleIdentifier)->orderBy('id', 'ASC')->get() as $formTemplate)
        {
            $latestRevisionForm = DynamicForm::where('root_id', $formTemplate->root_id)->where('status', self::STATUS_DESIGN_APPROVED)->orderBy('revision', 'DESC')->first();

            if(is_null($latestRevisionForm)) continue;

            $data = [
                'id'       => $latestRevisionForm->id,
                'name'     => $latestRevisionForm->name,
                'revision' => $latestRevisionForm->revision,
            ];

            array_push($formTemplates, $data);
        }

        return $formTemplates;
    }

    public function getFormDesignApprovers()
    {
        return VendorManagementUserPermission::getUsers(VendorManagementUserPermission::TYPE_FORM_TEMPLATES);
    }

    public static function getFormFieldValueByLabel(array $companyIds, $labelKey)
    {
        if(count($companyIds) == 0) return [];
        if(! $labelKey) return [];

        $query = "SELECT vr.company_id, vr.id as vendor_registration_id, MAX(vr.revision)
                  FROM vendor_registrations vr
                  JOIN (
                      SELECT vr2.company_id, max(vr2.revision) AS max_revision
                      FROM vendor_registrations vr2
                      WHERE vr2.deleted_at IS NULL
                      AND vr2.status = " . VendorRegistration::STATUS_COMPLETED . "
                      GROUP BY vr2.company_id
                  ) vr2 ON vr2.company_id = vr.company_id AND vr2.max_revision = vr.revision 
                  AND vr.deleted_at IS NULL
                  AND vr.status = " . VendorRegistration::STATUS_COMPLETED . "
                  AND vr.company_id IN (" . implode(',', $companyIds) . ")
                  GROUP BY vr.company_id, vr.id
                  ORDER BY MAX(vr.revision) DESC;";

        $records = DB::select(DB::RAW($query));

        $vendorRegistrationIds = array_column($records, 'vendor_registration_id');

        if(count($vendorRegistrationIds) == 0) return [];

        $query = "SELECT
                  vr.company_id, vr.id AS vendor_registration_id, e.label, ev.value
                  FROM form_object_mappings fom 
                  INNER JOIN vendor_registrations vr ON vr.id = fom.object_id 
                  INNER JOIN dynamic_forms df ON df.id = fom.dynamic_form_id 
                  INNER JOIN form_columns fc ON fc.dynamic_form_id = df.id
                  INNER JOIN form_column_sections fcs ON fcs.form_column_id = fc.id
                  INNER JOIN form_element_mappings fem ON fem.form_column_section_id = fcs.id AND fem.element_class = 'PCK\FormBuilder\Elements\TextBox'
                  INNER JOIN elements e ON e.id = fem.element_id 
                  INNER JOIN element_values ev ON ev.element_id = e.id AND ev.element_class = 'PCK\FormBuilder\Elements\TextBox'
                  WHERE fom.object_class = 'PCK\VendorRegistration\VendorRegistration' 
                  AND df.module_identifier = " . self::VENDOR_REGISTRATION_IDENTIFIER . " 
                  AND lower(e.label) = '" . strtolower($labelKey) . "' 
                  AND vr.id IN (" . implode(',', $vendorRegistrationIds) .") 
                  order by vr.company_id ASC, e.id ASC";

        $records = DB::select(DB::RAW($query));

        $resultsByCompanyId = [];

        foreach($records as $record)
        {
            if(array_key_exists($record->company_id, $resultsByCompanyId)) continue;

            $resultsByCompanyId[$record->company_id] = $record->value;
        }

        return $resultsByCompanyId;
    }

    public static function getDynamicFormElementValues(Array $dynamicFormIds)
    {
        if(count($dynamicFormIds) == 0) return [];

        // get dynamic form elements and their type
        // both custom and system elements
        // file uploads excluded
        $query = "SELECT df.id AS dynamic_form_id, fem.element_id, fem.element_class
                    FROM dynamic_forms df
                    INNER JOIN form_columns fc ON fc.dynamic_form_id = df.id
                    INNER JOIN form_column_sections fcs ON fcs.form_column_id = fc.id
                    INNER JOIN form_element_mappings fem ON fem.form_column_section_id = fcs.id AND fem.element_class != '" . FileUpload::class . "'
                    WHERE df.id IN (" . implode(', ', $dynamicFormIds) . ")
                    ORDER BY df.id ASC, fc.priority ASC, fcs.priority ASC, fem.priority ASC;";

        $queryResults = DB::select(DB::RAW($query));

        $data = [];

        $customElementIds = [];
        $systemElementIds = [];

        foreach($queryResults as $result)
        {
            $data[$result->dynamic_form_id][$result->element_id]['element_class'] = $result->element_class;
            $data[$result->dynamic_form_id][$result->element_id]['element_value'] = null;

            if($result->element_class == SystemModuleElement::class)
            {
                array_push($systemElementIds, $result->element_id);
            }
            else
            {
                array_push($customElementIds, $result->element_id);
            }
        }

        $elementValues = Element::getElementValues($customElementIds);

        $complexElementIds = [];    // radiobox, checkbox, dropdown

        foreach($data as $dynamicFormId => $elements)
        {
            foreach($elements as $elementId => $value)
            {
                $elementValue = array_key_exists($elementId, $elementValues) ? json_decode($elementValues[$elementId]) : [];

                if(in_array($data[$dynamicFormId][$elementId]['element_class'], [RadioBox::class, CheckBox::class, Dropdown::class]))
                {
                    $data[$dynamicFormId][$elementId]['element_value'] = $elementValue;

                    foreach($elementValue as $ev)
                    {
                        array_push($complexElementIds, $ev);
                    }
                }
                else
                {
                    $data[$dynamicFormId][$elementId]['element_value'] = ((count($elementValue) > 0) && (trim($elementValue[0]) != '')) ? $elementValue[0] : null;
                }
            }
        }

        $complextElementValues = Element::getComplexElementValues($complexElementIds);

        foreach($data as $dynamicFormId => $elements)
        {
            foreach($elements as $elementId => $value)
            {
                if(!in_array($data[$dynamicFormId][$elementId]['element_class'], [RadioBox::class, CheckBox::class, Dropdown::class])) continue;

                $data[$dynamicFormId][$elementId]['element_value'] = array_key_exists($elementId, $complextElementValues) ? $complextElementValues[$elementId] : null;
            }
        }

        // populate element values for system module elements
        $systemElementValues = SystemModuleElement::getElementValues($systemElementIds);

        foreach($data as $dynamicFormId => $elements)
        {
            foreach($elements as $elementId => $value)
            {
                if($value['element_class'] != SystemModuleElement::class) continue;

                $data[$dynamicFormId][$elementId]['element_value'] = array_key_exists($elementId, $systemElementValues) ? $systemElementValues[$elementId] : null;
            }
        }

        return $data;
    }

    public function isProperlyFilled()
    {
        $baseQuery = "SELECT fem.element_id, fem.element_class
                      FROM dynamic_forms df
                      INNER JOIN form_columns fc ON fc.dynamic_form_id = df.id
                      INNER JOIN form_column_sections fcs ON fcs.form_column_id = fc.id
                      INNER JOIN form_element_mappings fem ON fem.form_column_section_id = fcs.id
                      INNER JOIN element_attributes ea ON ea.element_id = fem.element_id AND ea.element_class = fem.element_class
                      WHERE df.id = {$this->id}
                      AND ea.name = 'required'
                      ORDER BY fc.id ASC, fcs.id ASC, fem.id ASC, fem.priority ASC;";

        $baseQueryResults = DB::select(DB::raw($baseQuery));

        $pass = true;

        foreach($baseQueryResults as $result)
        {
            $class = $result->element_class;

            if($class == SystemModuleElement::class)
            {
                $element = SystemModuleElement::find($result->element_id);

            }
            else
            {
                $element = Element::findById($result->element_id);
            }

            if( method_exists($element, 'isFilled') && (! $element->isFilled()) )
            {
                $pass = false;

                break;
            }

            if($element->has_attachments && ($element->attachments->count() == 0))
            {
                $pass = false;

                break;
            }
        }

        return $pass;
    }

    /**
     * Verifiable functions
     */

    public function getOnApprovedView()
    {
        return 'vendorRegistration.approved';
    }

    public function getOnRejectedView()
    {
        return 'vendorRegistration.rejected';
    }

    public function getOnPendingView()
    {
        return 'vendorRegistration.pending_form_design_approval';
    }

    public function getRoute()
    {
        return route('form.designer.show', [$this->id]);
    }

    public function getViewData($locale)
    {
        return [];
    }

    public function getOnApprovedNotifyList()
    {
        return [];
    }

    public function getOnRejectedNotifyList()
    {
        return [];
    }

    public function getOnApprovedFunction()
    {
        return function()
        {
            if($this->isDesignPendingForApproval())
            {
                $this->status = self::STATUS_DESIGN_APPROVED;
                $this->save();
            }

            if($this->isVendorSubmitted())
            {
                $this->status = self::STATUS_VENDOR_SUBMISSION_APPROVED;
                $this->save();
            }
        };
    }

    public function getOnRejectedFunction()
    {
        return function()
        {
            $this->status = self::STATUS_OPEN;
            $this->save();
        };
    }

    public function onReview()
    {
        return null;
    }

    public function getEmailSubject($locale)
    {
        return '[' . trans('formBuilder.dynamicForm') . '] ' . $this->name;
    }

    public function getSubmitterId()
    {
        return $this->submitted_for_approval_by;
    }

    public function getModuleName()
    {
        return null;
    }
}