<?php

/**
* PreliminariesWorkBasedClaim form.
*
* @package    buildspace
* @subpackage form
* @author     1337 developers
* @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
*/
class PreliminariesWorkBasedClaimForm extends BasePreliminariesWorkBasedClaimForm
{
    public function configure()
    {
        parent::configure();

        unset($this['total'], $this['created_at'], $this['updated_at']);
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);

        if ( $values['total_builders_work'] != 0 )
        {
            $this->getObject()->setTotal($values['builders_work_done'] / $values['total_builders_work']);
        }
        else
        {
            $this->getObject()->setTotal( (string) 0 );
        }
    }
}
