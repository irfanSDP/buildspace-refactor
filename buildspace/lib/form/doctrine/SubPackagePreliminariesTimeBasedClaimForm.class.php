<?php

/**
 * SubPackagePreliminariesTimeBasedClaim form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SubPackagePreliminariesTimeBasedClaimForm extends BaseSubPackagePreliminariesTimeBasedClaimForm
{
    public function configure()
    {
        parent::configure();

        unset($this['total'], $this['created_at'], $this['updated_at']);
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);

        if ( $values['total_project_duration'] != 0 )
        {
            $this->getObject()->setTotal($values['up_to_date_duration'] / $values['total_project_duration']);
        }
        else
        {
            $this->getObject()->setTotal( (string) 0 );
        }
    }
}
