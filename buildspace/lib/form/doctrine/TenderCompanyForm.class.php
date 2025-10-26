<?php

/**
 * TenderCompany form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TenderCompanyForm extends BaseTenderCompanyForm
{
    public function configure()
    {
        unset($this['created_at'], $this['updated_at']);

        parent::configure();
    }
}
