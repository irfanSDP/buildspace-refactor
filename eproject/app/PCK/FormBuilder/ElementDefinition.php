<?php namespace PCK\FormBuilder;

use Illuminate\Database\Eloquent\Model;
use PCK\States\State;
use PCK\Countries\Country;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\WorkCategories\WorkCategory;

class ElementDefinition extends Model
{
    protected $table = 'element_definitions';

    const TYPE_STATE         = 1;
    const TYPE_COUNTRY       = 2;
    const TYPE_USER_TYPE     = 4;
    const TYPE_WORK_CATEGORY = 8;

    // element render identifier
    const TYPE_RADIOBOX  = 1;
    const TYPE_CHECKBOX  = 2;
    const TYPE_DROPDOWN  = 4;

    public function systemModuleElement()
    {
        $this->hasMany('PCK\FormBuilder\Elements\SystemModuleElement', 'element_definition_id');
    }

    private static function getAllRenderElementsIdentifier()
    {
        return [
            self::TYPE_RADIOBOX,
            self::TYPE_CHECKBOX,
            self::TYPE_DROPDOWN,
        ];
    }

    public static function getSystemModuleClassNameByIdentifier($identifier = null)
    {
        $systemModules = [
            self::TYPE_STATE         => State::class,
            self::TYPE_COUNTRY       => Country::class,
            self::TYPE_USER_TYPE     => ContractGroupCategory::class,
            self::TYPE_WORK_CATEGORY => WorkCategory::class,
        ];

        return is_null($identifier) ? $systemModules : $systemModules[$identifier];
    }

    public static function getAllElementDefinitionCombinations()
    {
        $combinations = [];

        foreach(self::getAllRenderElementsIdentifier() as $elementIdentifier)
        {
            foreach(self::getSystemModuleClassNameByIdentifier() as $class)
            {
                array_push($combinations, [
                    'element_render_identifier' => $elementIdentifier,
                    'module_class'              => $class,
                ]);
            }
        }

        return $combinations;
    }

    public static function findElementDefinition($elementRenderIdentifier, $moduleClass)
    {
        return self::where('element_render_identifier', $elementRenderIdentifier)->where('module_class', $moduleClass)->first();
    }

    public static function createElementDefinitionIfNotExists($elementRenderIdentifier, $moduleClass)
    {
        $elementDefinition = self::findElementDefinition($elementRenderIdentifier, $moduleClass);

        if($elementDefinition)
        {
            return $elementDefinition;
        }

        $elementDefinition = new self();
        $elementDefinition->element_render_identifier = $elementRenderIdentifier;
        $elementDefinition->module_class              = $moduleClass;
        $elementDefinition->save();

        return self::find($elementDefinition->id);
    }
}