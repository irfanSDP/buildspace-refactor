<?php

/**
 * resourceImport actions.
 *
 * @package    buildspace
 * @subpackage resourceImport
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class resourceImportActions extends BaseActions {

	public function executeGetResourceList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$records = DoctrineQuery::create()->select('r.id, r.name')
			->from('Resource r')
			->addOrderBy('r.id ASC')
			->fetchArray();

		$form = new BaseForm();

		foreach ( $records as $key => $record )
		{
			$records[$key]['can_be_deleted'] = ResourceTable::linkToSoR($record['id']) ? false : true;
			$records[$key]['_csrf_token']    = $form->getCSRFToken();

			unset( $record );
		}

		$records[] = array(
			'id'             => Constants::GRID_LAST_ROW,
			'name'           => null,
			'can_be_deleted' => false,
			'_csrf_token'    => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'label'      => 'name',
			'items'      => $records
		));
	}

	public function executeImportResourceItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('trade_id'))
		);

		$conn     = $resourceTrade->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
			$withRate = $request->getParameter('with_rate') == 'true' ? true : false;

			ResourceItemTable::importResourceItems($conn, $request->getParameter('id'), $resourceTrade, $ids, $withRate);

			$conn->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
	}

	public function executeGetScheduleOfRateList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$records = DoctrineQuery::create()->select('r.id, r.name')
			->from('ScheduleOfRate r')
			->addOrderBy('r.id ASC')
			->fetchArray();

		$form = new BaseForm();

		foreach ( $records as $key => $record )
		{
			$records[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		$records[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'name'        => null,
			'_csrf_token' => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'label'      => 'name',
			'items'      => $records
		));
	}

	public function executeImportSORItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('trade_id'))
		);

		$conn     = $resourceTrade->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
			$withRate = $request->getParameter('with_rate') == 'true' ? true : false;

			ResourceItemTable::importSORItems($conn, $request->getParameter('id'), $resourceTrade, $ids, $withRate);

			$conn->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
	}

	public function executeGetBQLibraryList(sfWebRequest $request)
	{
		$this->forward404Unless($request->isXmlHttpRequest());

		$records = DoctrineQuery::create()->select('r.id, r.name')
			->from('BQLibrary r')
			->addOrderBy('r.id ASC')
			->fetchArray();

		$form = new BaseForm();

		foreach ( $records as $key => $record )
		{
			$records[$key]['_csrf_token'] = $form->getCSRFToken();
		}

		$records[] = array(
			'id'          => Constants::GRID_LAST_ROW,
			'name'        => null,
			'_csrf_token' => $form->getCSRFToken(),
		);

		return $this->renderJson(array(
			'identifier' => 'id',
			'label'      => 'name',
			'items'      => $records
		));
	}

	public function executeImportBQLibraryItems(sfWebRequest $request)
	{
		$request->checkCSRFProtection();

		$this->forward404Unless(
			$request->isXmlHttpRequest() and
			$request->isMethod('post') and
			$resourceTrade = Doctrine_Core::getTable('ResourceTrade')->find($request->getParameter('trade_id'))
		);

		$conn     = $resourceTrade->getTable()->getConnection();
		$errorMsg = null;

		try
		{
			$conn->beginTransaction();

			$ids      = Utilities::array_filter_integer(explode(',', $request->getParameter('ids')));
			$withRate = $request->getParameter('with_rate') == 'true' ? true : false;

			ResourceItemTable::importBQLibraryItems($conn, $request->getParameter('id'), $resourceTrade, $ids, $withRate);

			$conn->commit();

			$success = true;
		}
		catch (Exception $e)
		{
			$conn->rollback();

			$errorMsg = $e->getMessage();
			$success  = false;
		}

		return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg ));
	}

}