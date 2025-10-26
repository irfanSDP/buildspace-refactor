<?php

/**
 * StockInInvoice form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class StockInInvoiceForm extends BaseStockInInvoiceForm
{

    public function configure()
    {
        parent::configure();

        unset( $this['created_at'], $this['updated_at'] );

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array( 'callback' => array( $this, 'validateInvoiceNoUniqueness' ) ))
        );
    }

    public function validateInvoiceNoUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('u.id')->from('StockInInvoice u');
        $query->where('u.project_structure_id = ?', $values['project_structure_id']);
        $query->andWhere('u.invoice_no = ?', $values['invoice_no']);
        $query->andWhere('u.deleted_at IS NULL');

        if ( $query->count() > 0 )
        {
            $sfError = new sfValidatorError($validator, 'Sorry, currently entered Invoice No has been used.');

            if ( $this->object->isNew() )
            {
                throw new sfValidatorErrorSchema($validator, array( 'project_structure_id' => $sfError ));
            }
            else
            {
                $user = $query->fetchOne();

                if ( $this->object->getId() != $user->getId() )
                {
                    throw new sfValidatorErrorSchema($validator, array( 'project_structure_id' => $sfError ));
                }
            }
        }

        return $values;
    }

}