<?php

class defaultActions extends sfActions
{
    public function executeNoAccess(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
    }

    public function executeGetAllUnits(sfWebRequest $request)
    {
        $options = array();
        $values  = array();

        array_push($values, '-1');
        array_push($options, '---');

        $query = DoctrineQuery::create()->select('u.id, u.symbol')
            ->from('UnitOfMeasurement u')
            ->where('u.display IS TRUE')
            ->addOrderBy('u.symbol ASC');

        $query->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

        $records = $query->execute();

        foreach($records as $record)
        {
            array_push($values, (string)$record['id']); //damn, dojo store handles ids in string format
            array_push($options, $record['symbol']);
        }

        unset( $records );

        return $this->renderJson(array(
            'values'  => $values,
            'options' => $options
        ));
    }
}
