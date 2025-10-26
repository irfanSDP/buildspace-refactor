<?php

class DateCompletedForm extends BaseForm
{
    public function __construct(ScheduleTaskItem $scheduleTaskItem)
    {
        $this->scheduleTaskItem = $scheduleTaskItem;
        parent::__construct();
    }

    public function configure()
    {
        $this->setWidgets(array(
            'is_completed' => new sfWidgetFormInputCheckbox(array('value_attribute_value' => 'single', 'default' => $this->scheduleTaskItem->date_completed ? true : false)),
            'date_completed' => new sfWidgetFormInputText()
        ));

        $this->validatorSchema['is_completed'] = new sfValidatorPass();
        $this->validatorSchema['date_completed'] = new sfValidatorPass();

        $this->widgetSchema->setNameFormat('date_completed[%s]');
    }

    public function getObject()
    {
        return $this->scheduleTaskItem;
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        $day = null;
        $month = null;
        $year = null;

        if(array_key_exists('is_completed', $taintedValues) && strlen($taintedValues['is_completed']) > 0)
        {
            $this->validatorSchema['date_completed'] = new sfValidatorString(array(
                    'required' => true,
                    'trim'=>true,
                    'max_length'=>35
                ),
                array('max_length'=>'Document number is too long (%max_length% maximum characters)')
            );
        }
        else
        {
            $taintedValues['date_completed'] = null;
        }

        return parent::bind($taintedValues, $taintedFiles);
    }

    public function save(Doctrine_Connection $con = null)
    {
        $con = is_null($con) ? $this->scheduleTaskItem->getTable()->getConnection() : $con;

        try
        {
            $con->beginTransaction();

            $this->scheduleTaskItem->date_completed = $this->getValue('date_completed');
            $this->scheduleTaskItem->save($con);

            $con->commit();

            return $this->scheduleTaskItem;
        }
        catch(Exception $e)
        {
            $con->rollback();
            throw $e;
        }
    }
}
