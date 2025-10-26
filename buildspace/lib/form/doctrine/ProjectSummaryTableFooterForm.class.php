<?php

class ProjectSummaryTableFooterForm extends ProjectSummaryFooterForm
{
    public function configure()
    {
        parent::configure();

        unset($this['left_text'], $this['right_text']);
    }
}
