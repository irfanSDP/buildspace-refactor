<?php namespace PCK\Helpers;

/*
  DataTables Helper Class

  Manual:

  ==============================
   A    Processing Queries
  ==============================

  1.   Create a new QueryBuilder

           $query = \DB::table("<tableName> as <tableNameAlias>")


  2.   Instantiate
           a.  $idColumn

                   $idColumn = '<table.id>';

           b.  $selectColumns

                   $selectColumns = array( $idColumn );

           c.  $allColumns

                   $allColumns = array(
                       "<tableName1>" => array(
                               "<columnName1>" => <datatableColumnIndex1>,
                               "<columnName2>" => <datatableColumnIndex2>
                           ),
                       "<tableName2>" => array(
                               "<columnName1>" => <datatableColumnIndex3>
                           )
                   );

           d.  $query

                   $query = \DB::table("<tableName> as <tableName>");


  3.   Create a DataTables Object

           $dataTable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

       - where $inputs is the request from the datatable


  4.   Join necessary tables using the query property

           $dataTable->properties->query->join('<table2> as <table2Alias>', '<table1Alias.table2_id>', '=', '<table2Alias.id>');


  5.   Add and set custom sort/filter functions for required datatable column, if any

           $customOrdering         = [ <datatableColumnIndex1> => function($query, $selectColumns, $ascDesc){ //order logic } ]
           $customGlobalFilter     = [ <datatableColumnIndex1> => function($query, $searchString){ //filter logic } ]
           $customIndividualFilter = [ <datatableColumnIndex1> => function($query, $searchString){ //filter logic } ]

           $dataTable->setCustomOrder($customOrdering);
           $dataTable->setCustomGlobalFilter($customGlobalFilter);
           $dataTable->setCustomIndividualFilter($customIndividualFilter);


  6.   Call addAllStatements() and getResults()

           $dataTable->addAllStatements();
           $results = $dataTable->getResults();


  7.   Format results as required

           foreach ( $results as $arrayIndex => $stdObject )
           {
               $indexNo = ( $arrayIndex + 1 ) + ( $dataTable->properties->pagingOffset );
               $record  = $this->find($stdObject->id);

               $dataArray[] = array(
                   'indexNo'                => $indexNo,
                   'companyName'            => $record->name,
                   'mainContact'            => $record->main_contact,
                   'email'                  => $record->email,
                   'createdAt'              => $record->created_at,
                   'route:companies.users'  => route('companies.users', array( $record->id )),
                   'route:companies.edit'   => route('companies.edit', array( $record->id )),
                   'route:companies.delete' => route('companies.delete', array( $record->id ))
               );
           }


  8.   Return dataTableResponse()

           return $dataTable->dataTableResponse($dataArray);


  ==========================
   B   Processing Arrays
  ==========================

  1.   For now just use the static helper methods that have the "array-" prefix


 * */

class DataTableProperties {
	public $query;
	public $inputs;
	public $idColumn;
	public $allColumns;
	public $selectColumns;
	public $customOrdering;
	public $customGlobalFiltering;
	public $customIndividualFiltering;
	public $iFilteredTotal;
	public $iTotal;
	public $pagingOffset;

	public function __construct()
	{

	}

	public function getCustomOrdering()
	{
		return isset( $this->customOrdering ) ? $this->customOrdering : array();
	}

	public function getCustomGlobalFiltering()
	{
		return isset( $this->customGlobalFiltering ) ? $this->customGlobalFiltering : array();
	}

	public function getCustomIndividualFiltering()
	{
		return isset( $this->customIndividualFiltering ) ? $this->customIndividualFiltering : array();
	}
}

class DataTables {

	public static function hasSortableColumns(DataTableProperties $properties)
	{
		return isset( $properties->inputs['iSortCol_0'] );
	}

	public static function isDefaultSorting(DataTableProperties $properties)
	{
		return ( $properties->inputs['iSortCol_0'] == 0 );
	}

	public static function columnIsSortable(DataTableProperties $properties, $tableColumnIndex)
	{
		return $properties->inputs['bSortable_' . intval($tableColumnIndex)] == 'true';
	}

	/**
	 * Get the ORDER BY direction of the column
	 * where $i is the index of the searchable columns array
	 * [
	 *  $indexInSortableColumns1 => <indexOfSearchableColumnA>,
	 *  $indexInSortableColumns2 => <indexOfSearchableColumnB>
	 * ]
	 *
	 * @param DataTableProperties $properties
	 * @param                     $indexInSortableColumns
	 * @return string
	 */
	public static function getDirection(DataTableProperties $properties, $indexInSortableColumns)
	{
		return (array_key_exists('sSortDir_' . $indexInSortableColumns, $properties->inputs) && $properties->inputs['sSortDir_' . $indexInSortableColumns] === 'asc' ) ? 'asc' : 'desc';
	}

	public static function totalSortableColumns(DataTableProperties $properties)
	{
		return intval($properties->inputs['iSortingCols']);
	}

	public static function columnHasCustomOrdering($columnIndex, DataTableProperties $properties)
	{
		return array_key_exists($columnIndex, $properties->getCustomOrdering());
	}

	public static function columnIsSearchable(DataTableProperties $properties, $columnIndex)
	{
		return ( array_key_exists('bSearchable_' . $columnIndex, $properties->inputs) && ( $properties->inputs['bSearchable_' . $columnIndex] == 'true' ) && ( $properties->inputs['sSearch_' . $columnIndex] != '' ) );
	}

	public static function canPage(DataTableProperties $properties)
	{
		return ( isset( $properties->inputs['iDisplayStart'] ) && $properties->inputs['iDisplayLength'] != '-1' );
	}

	public static function getCount(DataTableProperties $properties)
	{
		$queryForCount = clone $properties->query;
		$results       = $queryForCount->distinct()->get(array( $properties->idColumn ));
		unset( $queryForCount );

		return count($results);
	}

	public static function getColumnIndex(DataTableProperties $properties, $indexInSortableColumns)
	{
		return $properties->inputs['iSortCol_' . $indexInSortableColumns];
	}

	/**
	 * Generic function for ordering server-side DataTables
	 *
	 * customOrdering is an array that must follow the specified format:
	 *      $customOrdering = array(
	 *          columnIndex1 => orderFunction1,
	 *          columnIndex2 => orderFunction2
	 *      );
	 *
	 * Each orderFunction must receive the arguments:
	 *      $query
	 *      $selectColumns
	 *      $ascDesc
	 *
	 * Each orderFunction must return, in an array, the following:
	 *      'query'         => $query
	 *      'selectColumns' => $selectColumns
	 *
	 * @param DataTableProperties $properties
	 * @return DataTableProperties
	 */
	public static function order(DataTableProperties $properties)
	{
		if ( self::isDefaultSorting($properties) )
		{
			$properties->query->orderBy($properties->idColumn, 'desc');
		}
		elseif ( self::hasSortableColumns($properties) )
		{
			$properties = self::definedSorting($properties);
		}

		return $properties;
	}

	public static function definedSorting(DataTableProperties $properties)
	{
		for ( $i = 0; $i < self::totalSortableColumns($properties); $i ++ )
		{
			$tableColumnIndex = self::getColumnIndex($properties, $i);

			if ( !self::columnIsSortable($properties, $tableColumnIndex) )
			{
				continue;
			}

			$ascDesc = self::getDirection($properties, $i);

			if ( self::columnHasCustomOrdering($tableColumnIndex, $properties) )
			{
				$function = $properties->customOrdering[$tableColumnIndex];

				$queryAndSelectColumns     = $function($properties->query, $properties->selectColumns, $ascDesc);
				$properties->query         = $queryAndSelectColumns['query'];
				$properties->selectColumns = $queryAndSelectColumns['selectColumns'];

				break;
			}

			foreach ( $properties->allColumns as $tableName => $arrayTableColumns )
			{
				$inArray = array_search($tableColumnIndex, $arrayTableColumns);
				if ( $inArray !== false )
				{
					$arrayColumnName = $inArray;
					$columnNameRef   = $tableName . '.' . $arrayColumnName;

					$properties->query->orderBy($columnNameRef, $ascDesc);

					// for SELECT DISTINCT, ORDER BY expressions must appear in select list
					// so add to selectedColumns
					$selectColumns             = $properties->selectColumns;
					$selectColumns[]           = $columnNameRef;
					$properties->selectColumns = $selectColumns;

					break 2;
				}
			}
		}

		return $properties;
	}

	public static function filter(DataTableProperties $properties)
	{
		$properties = self::globalFilter($properties);
        return self::individualFilter($properties);
	}

	public static function globalFilter(DataTableProperties $properties)
	{
		$searchString        = $properties->inputs['sSearch'];
		$searchStringPattern = '%' . $searchString . '%';

		if ( ( isset( $searchString ) ) && ( $searchString != '' ) )
		{
			$query = $properties->query;
			$query->where(function ($query) use ($properties, $searchString, $searchStringPattern)
			{
				foreach ( $properties->allColumns as $tableName => $tableColumns )
				{
					foreach ( $tableColumns as $columnName => $columnIndex )
					{
						$customFiltering = $properties->getCustomGlobalFiltering();
						if ( array_key_exists($columnIndex, $customFiltering) )
						{
							$function = $customFiltering[$columnIndex];
							$query    = $function($query, $searchString);
							continue;
						}

						$columnNameRef = $tableName . '.' . $columnName;
						$query->orWhere(\DB::raw('CAST(' . $columnNameRef . ' AS TEXT)'), 'ILIKE', $searchStringPattern);
					}

				}
			});
			$properties->query = $query;
		}

		return $properties;
	}

	public static function individualFilter(DataTableProperties $properties)
	{
		foreach ( $properties->allColumns as $tableName => $tableColumns )
		{
			foreach ( $tableColumns as $columnName => $columnIndex )
			{
				if ( !self::columnIsSearchable($properties, $columnIndex) )
				{
					continue;
				}

				$searchString = $properties->inputs['sSearch_' . $columnIndex];

				$customFiltering = $properties->getCustomIndividualFiltering();
				if ( array_key_exists($columnIndex, $customFiltering) )
				{
					$function          = $customFiltering[$columnIndex];
					$properties->query = $function($properties->query, $searchString);
					continue;
				}

				$searchStringPattern = '%' . $searchString . '%';
				$columnNameRef       = $tableName . '.' . $columnName;
				$properties->query->where(\DB::raw('CAST(' . $columnNameRef . ' AS TEXT)'), 'ILIKE', $searchStringPattern);
			}
		}

		return $properties;
	}

	public static function page(DataTableProperties $properties)
	{
		if ( self::canPage($properties) )
		{
			$properties->query->skip($properties->inputs['iDisplayStart'])->take($properties->inputs['iDisplayLength']);
		}

		return $properties;
	}

	/**
	 * Generic function for custom ordering
	 * where records are sorted by data not found in the database.
	 * This function sorts records by the nameText associated with the id
	 *
	 * The $idAndNameArray passed to this function should already be in the desired order
	 *
	 * @param       $query
	 * @param array $idAndNameArray
	 * @param       $ascDesc
	 * @param       $columnName
	 * @param       $selectColumns
	 * @return array
	 */
	public static function genericCustomOrderingFunction($query, array $idAndNameArray, $ascDesc, $columnName, $selectColumns)
	{
		$caseStatement  = '(CASE';
		$sequenceNumber = 0;
		foreach ( $idAndNameArray as $id => $nameText )
		{
			$sequenceNumber ++;
			$caseStatement .= ' when ' . $columnName . ' = ' . $id . ' then ' . $sequenceNumber;
		}
		$caseStatement .= ' END)';

		array_unshift($selectColumns, \DB::raw($caseStatement));
		$query->orderBy(\DB::raw('1'), $ascDesc);

		return array(
			'query'         => $query,
			'selectColumns' => $selectColumns
		);
	}

	/**
	 * Generic function for custom individual column filtering
	 * where records are filtered by data not found in the database.
	 * This function matches the input (searchString) with the nameText
	 *
	 * @param       $query
	 * @param       $searchString
	 * @param array $idAndNameArray
	 * @param       $columnName
	 * @return mixed
	 */
	public static function genericCustomGlobalFilteringFunction($query, $searchString, array $idAndNameArray, $columnName)
	{
        $searchString = trim($searchString);

		if ( $searchString == '' )
		{
			return $query;
		}
		foreach ( $idAndNameArray as $id => $nameText )
		{
			if ( stristr($nameText, $searchString) !== false )
			{
				$query->orWhere($columnName, '=', $id);
			}
		}

		return $query;
	}

	/**
	 * Generic function for custom individual column filtering
	 * where records are filtered by data not found in the database.
	 * This function matches the input (searchString) with the nameText
	 *
	 * @param       $query
	 * @param       $searchString
	 * @param array $idAndNameArray
	 * @param       $columnName
	 * @return mixed
	 */
	public static function genericCustomIndividualFilteringFunction($query, $searchString, array $idAndNameArray, $columnName)
	{
		return $query->where(function ($query) use ($idAndNameArray, $searchString, $columnName)
		{
			if(empty($searchString)) return;

			foreach ( $idAndNameArray as $id => $nameText )
			{
				if ( stristr($nameText, $searchString) !== false )
				{
					$hasMatch = true;
					$query->orWhere($columnName, '=', $id);
				}
			}
			if ( !isset( $hasMatch ) )
			{
				$query->where(\DB::raw('FALSE'));
			}
		});
	}

	public $properties;

	public function __construct($query, $inputs, $allColumns, $idColumn, $selectColumns)
	{

		$this->properties = new DataTableProperties();

		$this->properties->query         = $query;
		$this->properties->inputs        = $inputs;
		$this->properties->allColumns    = $allColumns;
		$this->properties->idColumn      = $idColumn;
		$this->properties->selectColumns = $selectColumns;
		$this->properties->pagingOffset  = $inputs['iDisplayStart'];

		$this->properties->iTotal = $this->getCountThis();
	}

	public function setCustomOrder($customOrdering)
	{
		$this->properties->customOrdering = $customOrdering;
	}

	public function setCustomGlobalFilter($customFilter)
	{
		$this->properties->customGlobalFiltering = $customFilter;
	}

	public function setCustomIndividualFilter($customFilter)
	{
		$this->properties->customIndividualFiltering = $customFilter;
	}

	public function orderThis()
	{
		$this->properties = self::order($this->properties);
	}

	public function filterThis()
	{
		$this->properties = self::filter($this->properties);
	}

	public function pageThis()
	{
		$this->properties = self::page($this->properties);
	}

	public function getCountThis()
	{
		return self::getCount($this->properties);
	}

	public function applySelect()
	{
		$this->properties->query->select($this->properties->selectColumns);
	}

	public function getResults()
	{
		return $this->properties->query->distinct()->get(array( $this->properties->idColumn ));
	}

	public function addAllStatements()
	{
		$this->orderThis();
		$this->filterThis();
		$this->applySelect();
		$this->properties->iFilteredTotal = $this->getCountThis();
		$this->pageThis();
	}

	/**
	 * Formats all necessary data for the DataTableResponse, other than $dataArray
	 *
	 * @param $dataArray
	 * @return array
	 */
	public function dataTableResponse(array $dataArray)
	{
		return array(
			'sEcho'                => isset( $this->properties->inputs['sEcho'] ) ? intval($this->properties->inputs['sEcho']) : '',
			'iTotalRecords'        => $this->properties->iTotal,
			'iTotalDisplayRecords' => $this->properties->iFilteredTotal,
			'aaData'               => $dataArray
		);
	}

	/**
	 * Removes all elements from the array up to the offset index
	 *
	 * @param       $inputs
	 * @param array $items
	 * @return array
	 */
	public static function arrayPage($inputs, array $items)
	{
		return array_slice($items, $inputs['iDisplayStart'], $inputs['iDisplayLength']);
	}

	/**
	 * Generic ordering of elements in the array
	 *
	 * @param       $inputs
	 * @param array $items
	 * @param       $customOrdering
	 * @return array
	 */
	public static function arrayOrder($inputs, array $items, $customOrdering)
	{
		$properties         = new DataTableProperties();
		$properties->inputs = $inputs;
		if ( self::isDefaultSorting($properties) )
		{
			krsort($items);
		}
		elseif ( self::hasSortableColumns($properties) )
		{
			for ( $i = 0; $i < self::totalSortableColumns($properties); $i ++ )
			{
				$tableColumnIndex = self::getColumnIndex($properties, $i);

				if ( !self::columnIsSortable($properties, $tableColumnIndex) )
				{
					continue;
				}

				$function = $customOrdering[$tableColumnIndex];

				usort($items, $function);

				$ascDesc = self::getDirection($properties, $i);

				if ( $ascDesc === 'desc' )
				{
					$items = array_reverse($items);
				}

				break;
			}
		}

		return $items;
	}

    public static function getSearchString($input, $index = null)
    {
        if( $index ) $index = "_{$index}";

        return trim(isset( $input["sSearch{$index}"] ) ? $input["sSearch{$index}"] : '');
    }
}