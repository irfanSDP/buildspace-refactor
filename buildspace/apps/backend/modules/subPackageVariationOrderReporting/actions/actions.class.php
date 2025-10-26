<?php

/**
 * subPackageVariationOrderReporting actions.
 *
 * @package    buildspace
 * @subpackage subPackageVariationOrderReporting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subPackageVariationOrderReportingActions extends BaseActions {

	public function executeGetSubPackageAffectedItems(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('vo_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('spid'))
		);

		$data  = array();
		$voIds = json_decode($request->getParameter('vo_ids'), true);

		if ( !empty( $voIds ) )
		{
			$voItems = Doctrine_Query::create()
				->select('id, sub_package_variation_order_id')
				->from('SubPackageVariationOrderItem')
				->whereIn('sub_package_variation_order_id', $voIds)
				->fetchArray();

			foreach ( $voItems as $voItem )
			{
				$data[$voItem['sub_package_variation_order_id']][] = $voItem['id'];
			}

			// return empty array of variationOfOrderId's information so that the frontend can process it
			foreach ( $voIds as $variationOfOrderId )
			{
				if ( isset( $data[$variationOfOrderId] ) )
				{
					continue;
				}

				$data[$variationOfOrderId] = array();
			}
		}

		return $this->renderJson($data);
	}

	public function executeGetSubPackageAffectedVOS(sfWebRequest $request)
	{
		$this->forward404Unless(
			$request->isXmlHttpRequest() AND
			$request->hasParameter('item_ids') AND
			$subPackage = Doctrine_Core::getTable('SubPackage')->find($request->getParameter('spid'))
		);

		$data    = array();
		$itemIds = json_decode($request->getParameter('item_ids'), true);

		if ( !empty( $itemIds ) )
		{
			$voItems = Doctrine_Query::create()
				->select('id, sub_package_variation_order_id')
				->from('SubPackageVariationOrderItem')
				->whereIn('id', $itemIds)
				->fetchArray();

			foreach ( $voItems as $voItem )
			{
				$data[$voItem['sub_package_variation_order_id']][] = $voItem['id'];
			}
		}

		return $this->renderJson($data);
	}

}