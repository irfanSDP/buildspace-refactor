<?php

class ProjectSummaryBottomFooterForm extends ProjectSummaryFooterForm
{
    public function configure()
    {
        parent::configure();

        unset($this['first_row_text'], $this['second_row_text']);
    }
}
