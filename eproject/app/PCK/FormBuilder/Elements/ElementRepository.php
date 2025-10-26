<?php namespace PCK\FormBuilder\Elements;

use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementDefinition;
use PCK\FormBuilder\ElementRejection;

class ElementRepository
{
    public function getElementsBySection(FormColumnSection $section)
    {
        $customElementClasses = Element::getClassByIdentifier();
        $elements             = [];
      
        foreach($section->mappings()->orderBy('priority', 'ASC')->get() as $mapping)
        {
            if(in_array($mapping->element_class, $customElementClasses))
            {
                $element = Element::findById($mapping->element_id);
            }
            else
            {
                $element = SystemModuleElement::find($mapping->element_id);
            }

            $data = $element->getElementDetails();

            array_push($elements, $data);
        }

        return $elements;
    }

    public function store($inputs)
    {
        $section     = FormColumnSection::find($inputs['section_id']);
        $elementType = $inputs['element_type'];
        $element     = null;

        if($elementType == Element::ELEMENT_TYPE_ID)
        {
            $class   = Element::getClassByIdentifier($inputs['class_identifier']);
            $element = $class::createNewElement($inputs);
        }
        else
        {
            $element = SystemModuleElement::createNewElement($inputs);
        }

        $mapping = FormElementMapping::createNewMapping($section, $element);

        return $element;
    }

    public function update($elementId, $originalElementType, $inputs)
    {
        $section             = FormColumnSection::find($inputs['section_id']);
        $originalElementType = trim($originalElementType);      // original element type
        $elementType         = trim($inputs['element_type']);   // new element type
        $elementTypeChanged  = ($originalElementType != $elementType);

        $element = null;

        // if element change from custom to system and vice versa
        if($elementTypeChanged)
        {
            $originalElement = null;

            // find original element and delete
            if($originalElementType == Element::ELEMENT_TYPE_ID)
            {
                $class           = Element::getClassByIdentifier($inputs['class_identifier']);
                $originalElement = $class::findById($elementId);
            }
            else
            {
                $originalElement = SystemModuleElement::find($elementId);
            }

            $originalElement->delete();

            // create new element
            if($elementType == Element::ELEMENT_TYPE_ID)
            {
                $class   = Element::getClassByIdentifier($inputs['class_identifier']);
                $element = $class::createNewElement($inputs);
            }
            else
            {
                $element = SystemModuleElement::createNewElement($inputs);
            }

            $mapping = FormElementMapping::createNewMapping($section, $element);
        }
        else
        {
            if($elementType == Element::ELEMENT_TYPE_ID)
            {
                $class   = Element::getClassByIdentifier($inputs['class_identifier']);
                $element = $class::findById($elementId);
                $element = $element->updateElement($inputs);
            }
            else
            {
                $element = SystemModuleElement::find($elementId);
    
                $originalSystemModuleIdentifier = trim(array_search($element->elementDefinition->module_class, ElementDefinition::getSystemModuleClassNameByIdentifier()));
                $systemModuleIdentifier         = trim($inputs['system_module_identifier']);
                $systemModuleChanged            = ($originalSystemModuleIdentifier != $systemModuleIdentifier);
                
                // system module element changed, for example, from User Type to Work Category
                if($systemModuleChanged)
                {
                    $element->delete();

                    $element = SystemModuleElement::createNewElement($inputs);
                    $mapping = FormElementMapping::createNewMapping($section, $element);
                }
                else
                {
                    $element->updateElement($inputs);
                }
            }
        }

        ElementRejection::markAsAmeded($element);

        return $element;
    }

    public function swap($draggedElementMappingId, $swappedElementMappingId)
    {
        $draggedElementMapping = FormElementMapping::find($draggedElementMappingId);
        $swappedElementMapping = FormElementMapping::find($swappedElementMappingId);
        
        FormElementMapping::swap($draggedElementMapping, $swappedElementMapping);
    }

    public function getElementSelections(FormColumnSection $section)
    {
       $elements = [];

       foreach(FormElementMapping::where('form_column_section_id', '!=', $section->id)->orderBy('form_column_section_id', 'ASC')->orderBy('priority', 'ASC')->get() as $mapping)
       {
            $class                 = $mapping->element_class;
            $element               = $class::find($mapping->element_id);
            $elementMarker         = ($element instanceof Element) ? Element::ELEMENT_TYPE_ID : SystemModuleElement::ELEMENT_TYPE_ID;
            $elementTypeIdentifier = ($element instanceof Element) ? $element::getClassIdentifier() : $element->elementDefinition->element_render_identifier;
            $elementType           = $element::getElementTypesByIdentifer($elementTypeIdentifier);

            array_push($elements, [
                'id'             => $mapping->id,  //mapping id is unique, element id is not
                'label'          => $element->label,
                'element_marker' => $elementMarker,
                'element_type'   => $elementType,
            ]);
       }

       return $elements;
    }
}

