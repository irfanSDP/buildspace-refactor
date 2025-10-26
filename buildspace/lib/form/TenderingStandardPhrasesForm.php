<?php

class TenderingStandardPhrasesForm extends BaseBillLayoutPhraseForm {

    public function configure()
    {
        parent::configure();

        $this->widgetSchema->setNameFormat('tendering_standard_phrases[%s]');

        $this->useFields(array('summary_page_one', 'summary_page_two', 'summary_page_three', 'summary_page_four', 'summary_page_five', 'summary_page_six', 'summary_page_seven', 'summary_page_eight', 'summary_page_nine'));
    }


}