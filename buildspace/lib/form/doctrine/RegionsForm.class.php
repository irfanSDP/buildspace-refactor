<?php

/**
 * Regions form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class RegionsForm extends BaseRegionsForm
{
    public function configure()
    {
        parent::configure();

        $this->setValidators(array(
            
            'iso' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=> 2,
                    'min_length'=> 2
                ),
                array(
                    'max_length'=> 'ISO must be %max_length% characters long',
                    'min_length'=> 'ISO must be %min_length% characters long'
                )
            ),
            
            'iso3' => new sfValidatorString(
                array(
                    'required' => false,
                    'min_length'=>3,
                    'max_length'=>3
                ),
                array(
                    'max_length'=> 'ISO3 must be %max_length% characters long',
                    'min_length'=> 'ISO3 must be %min_length% characters long'
                )
            ),
            
            
            'fips' => new sfValidatorString(
                array(
                    'required' => false,
                    'min_length'=>2,
                    'max_length'=>2
                ),
                array(
                    'max_length'=> 'Fips must be %max_length% characters long',
                    'min_length'=> 'Fips must be %min_length% characters long'
                )
            ),
            
            'country' => new sfValidatorString(
                array(
                    'required'=>true,
                    'max_length'=> 255
                ),
                array(
                    'required'=>'This field needs an input',
                    'max_length'=> 'Country is too long (%max_length% characters max)'
                )
            ),
            
            'continent' => new sfValidatorString(
                array(
                    'required' => false,
                    'min_length'=>2,
                    'max_length'=>255
                ),
                array(
                    'max_length'=> 'Continent is too long (%max_length% characters max)',
                    'min_length'=> 'Continent is too short (%max_length% characters min)'
                )
            ),
            
            'currency_code' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=>3
                ),
                array(
                    'max_length'=> 'Currency code is too long(%max_length% characters max)'
                )
            ),
            
            'currency_name' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=> 60
                ),
                array(
                    'max_length'=>'Currency name is too long (%max_length% characters max)'
                )
            ),
            
            'phone_prefix' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=>20
                ),
                array(
                    'max_length'=>'Phone prefix is too long (%max_length% characters max)'
                )
            ),
            
            'postal_code' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length' => 20
                ),
                array(
                    'max_length'=> 'Postal code is too long (%max_length% characters max)'
                )
            ),
            
            'languages' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=> 50
                ),
                array(
                    'max_length'=> 'Languages is too long (%max_length% characters max)'
                )
            ),
            
            'geonameid' => new sfValidatorString(
                array(
                    'required' => false,
                    'max_length'=> 10
                ),
                array(
                    'max_length'=> 'Geonameid is too long (%max_length% characters max)'
                )
            )
        ));
        
        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'validateUniqueness')))
        );
    }
    
    public function validateUniqueness(sfValidatorCallback $validator, array $values)
    {
        $query = DoctrineQuery::create()->select('r.id, r.country, r.iso')->from('Regions r');
        $query->where('LOWER(r.country) = ?', strtolower($values['country']));

        if($query->count() > 0)
        {
            $sfError = new sfValidatorError($validator, 'There is already another region with that name.');

            if($this->object->isNew())
            {
                throw new sfValidatorErrorSchema($validator, array('country' => $sfError));
            }
            else
            {
                $region = $query->fetchOne();
                if($this->object->id != $region->id)
                {
                    throw new sfValidatorErrorSchema($validator, array('country' => $sfError));
                }
            }
        }
        return $values;
    }
}
