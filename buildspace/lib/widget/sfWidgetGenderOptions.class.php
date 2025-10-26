<?php

class sfWidgetGenderOptions extends sfWidgetForm
{
    public function configure($options = array(), $attributes = array())
    {
        $this->addOption('please_select', true);
        $this->addOption('select_all', false);
    }
    
    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $value = is_null($value) ? 'null' : $value;
        
        $widgetAttributes = array_merge(array('name' => $name), $attributes);
        $options = array();
        
        $options[-1] = $this->renderContentTag('option','Please Select',array('value'=>''));
        if(!$this->getOption('please_select'))
        {
            unset($options[-1]);
        }
        
        if($this->getOption('select_all'))
        {
            $options[-2] = $this->renderContentTag('option','Select All',array('value'=>-1));
        }
        
       $genders = array(Constants::GENDER_MALE => Constants::GENDER_MALE_TEXT,
                        Constants::GENDER_FEMALE => Constants::GENDER_FEMALE_TEXT);
        
        foreach ($genders as $key => $gender)
        {
            $attributes = array('value' => self::escapeOnce($key));
            
            if ($key == $value)
            {
                $attributes['selected'] = 'selected';
            }
            
            $options[] = $this->renderContentTag('option',self::escapeOnce(__($gender)),$attributes);
        }
        return $this->renderContentTag('select','\n'.implode('\n', $options).'\n', $widgetAttributes);
        
    }
}

?>
