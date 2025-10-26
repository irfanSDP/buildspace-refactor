<?php

class CsrfForm extends BaseForm {

    public function configure()
    {
        $this->widgetSchema->setNameFormat('form[%s]');
    }
}