<?php

/**
 * StockInDeliveryOrder form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class StockInDeliveryOrderForm extends BaseStockInDeliveryOrderForm
{

    public function configure()
    {
        parent::configure();

        unset($this['created_at'], $this['updated_at']);

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array( 'callback' => array( $this, 'validateDeliveryOrderNoUniqueness' ) ))
        );
    }

    public function validateDeliveryOrderNoUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('u.id')->from('StockInDeliveryOrder u');
        $query->where('u.stock_in_invoice_id = ?', $values['stock_in_invoice_id']);
        $query->andWhere('u.delivery_order_no = ?', $values['delivery_order_no']);
        $query->andWhere('u.deleted_at IS NULL');

        if ( $query->count() > 0 )
        {
            $sfError = new sfValidatorError($validator, 'Sorry, currently entered Delivery Order No has been used.');

            if ( $this->object->isNew() )
            {
                throw new sfValidatorErrorSchema($validator, array( 'stock_in_invoice_id' => $sfError ));
            }
            else
            {
                $user = $query->fetchOne();

                if ( $this->object->getId() != $user->getId() )
                {
                    throw new sfValidatorErrorSchema($validator, array( 'stock_in_invoice_id' => $sfError ));
                }
            }
        }

        return $values;
    }

}