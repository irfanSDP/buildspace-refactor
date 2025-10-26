<?php namespace PCK\FormBuilder\Elements;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\FormBuilder\FormColumnSection;
use PCK\FormBuilder\FormElementMapping;
use PCK\FormBuilder\ElementAttribute;
use PCK\FormBuilder\ElementRejection;
use PCK\Base\ModuleAttachmentTrait;

abstract class Element extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'elements';

    const TYPE_TEXT           = 1;
    const TYPE_MULTILINE_TEXT = 2;
    const TYPE_EMAIL          = 4;
    const TYPE_URL            = 8;
    const TYPE_NUMBER         = 16;
    const TYPE_RADIO          = 32;
    const TYPE_CHECKBOX       = 64;
    const TYPE_FILE_UPLOAD    = 128;
    const TYPE_DROPDOWN       = 256;
    const TYPE_DATE_TIME      = 512;

    const ELEMENT_TYPE_ID = 'cus';

    public static function getElementTypesByIdentifer($identifier = null)
    {
        $listing = [
            self::TYPE_TEXT           => trans('formBuilder.textbox'),
            self::TYPE_MULTILINE_TEXT => trans('formBuilder.multiLineTextbox'),
            self::TYPE_EMAIL          => trans('formBuilder.email'),
            self::TYPE_URL            => trans('formBuilder.url'),
            self::TYPE_NUMBER         => trans('formBuilder.number'),
            self::TYPE_RADIO          => trans('formBuilder.radiobox'),
            self::TYPE_CHECKBOX       => trans('formBuilder.checkbox'),
            self::TYPE_FILE_UPLOAD    => trans('formBuilder.fileUpload'),
            self::TYPE_DROPDOWN       => trans('formBuilder.dropdown'),
            self::TYPE_DATE_TIME      => trans('formBuilder.dateTimePicker'),
        ];

        return is_null($identifier) ? $listing : $listing[$identifier];
    }

    public static function getClassByIdentifier($identifier = null)
    {
        $classes =[
            self::TYPE_TEXT           => TextBox::class,
            self::TYPE_MULTILINE_TEXT => TextArea::class,
            self::TYPE_EMAIL          => EmailTextBox::class,
            self::TYPE_URL            => URLTextBox::class,
            self::TYPE_NUMBER         => NumberTextBox::class,
            self::TYPE_RADIO          => RadioBox::class,
            self::TYPE_CHECKBOX       => CheckBox::class,
            self::TYPE_FILE_UPLOAD    => FileUpload::class,
            self::TYPE_DROPDOWN       => Dropdown::class,
            self::TYPE_DATE_TIME      => DateTimePicker::class,
        ];

        return is_null($identifier) ? $classes : $classes[$identifier];
    }

    public function isKeyInformation()
    {
        return ($this->is_key_information == true);
    }

    public function getDynamicFormAttribute()
    {
        return FormElementMapping::getElementMappingByElement($this)->section->column->dynamicForm;
    }

    public static function findById($id)
    {
        $listOfClasses = self::getClassByIdentifier();
        $mapping = FormElementMapping::where('element_id', $id)->whereIn('element_class', $listOfClasses)->first();
        $class   = $mapping->element_class;

        return $class::find($mapping->element_id);
    }

    public function getValidationRulesString()
    {
        $elementAttributes = ElementAttribute::getElementAttributes($this);
        $rules             = [];

        foreach($elementAttributes as $name => $value)
        {
            if(!in_array($name, $this->validationRules)) continue;

            array_push($rules, $value);
        }

        return implode('|', $rules);
    }

    public function isRejected()
    {
        $record = ElementRejection::findRecordByElement($this);

        return !is_null($record);
    }

    public function isAmended()
    {
        $isAmended = false;
        $record    = ElementRejection::findRecordByElement($this);

        if(is_null($record)) return false;

        return $record->is_amended;
    }

    public static function getElementValues(Array $elementIds)
    {
        if(count($elementIds) == 0) return [];

        $query ="SELECT ev.element_id, ARRAY_TO_JSON(ARRAY_AGG(ev.value)) AS element_values
                    FROM element_values ev 
                    WHERE ev.element_id IN (" . implode(',', $elementIds) . ")
                    AND ev.element_class != '" . SystemModuleElement::class . "'
                    GROUP BY ev.element_id
                    ORDER BY element_id ASC;";

        $queryResult = DB::select(DB::raw($query));

        $data = [];

        foreach($queryResult as $result)
        {
            $data[$result->element_id] = $result->element_values;
        }

        return $data;
    }

    public static function getComplexElementValues(Array $elementIds)
    {
        if(count($elementIds) == 0) return [];

        $formValues = [];

        // excluding other options
        $query = "SELECT e.parent_id AS element_id, ARRAY_TO_JSON(ARRAY_AGG(e.label)) AS values
                    FROM elements e
                    WHERE e.id IN (" . implode(', ', $elementIds) . ")
                    AND e.is_other_option IS FALSE
                    GROUP BY parent_id
                    ORDER BY parent_id ASC;";

        $queryResult = DB::select(DB::raw($query));

        // value for all non-optional elements
        foreach($queryResult as $result)
        {
            $formValues[$result->element_id] = json_decode($result->values);
        }

        // get other options
        $otherOptionsQuery = "SELECT e.parent_id AS element_id, aev.value AS other_value
                                FROM elements e
                                INNER JOIN element_values ev ON TRIM(ev.value) = TRIM(e.id::TEXT)
                                INNER JOIN additional_element_values aev ON aev.element_value_id = ev.id
                                WHERE e.is_other_option IS TRUE
                                AND e.id IN (" . implode(', ', $elementIds) . ")
                                ORDER BY e.id ASC;";

        $otherOptionsQueryResults = DB::select(DB::raw($otherOptionsQuery));

        // append other options to existing parent element id
        // creates new entry is other options is the only selected option for radiobox / checkbox
        foreach($otherOptionsQueryResults as $result)
        {
            $formValues[$result->element_id][] = $result->other_value;
        }

        $data = [];

        // append all values into string separated by commas
        foreach($formValues as $elementId => $formValue)
        {
            $data[$elementId] = implode('; ', $formValue);
        }

        return $data;
    }
}

