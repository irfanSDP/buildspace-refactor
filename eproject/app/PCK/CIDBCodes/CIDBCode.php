<?php namespace PCK\CIDBCodes;

use Company;
use Illuminate\Database\Eloquent\Model;

class CIDBCode extends Model
{
    protected $table = 'cidb_codes';

    public static function getCidbCodes()
	{
		$cidbCodeArray = [];
		$cidbCodes  = CIDBCode::all();
		$cidbCodeParents  = CIDBCode::whereNull("parent_id")->get();
		$count = 0;

		foreach($cidbCodeParents as $cidbCodeParent)
		{
			$cidbCodeParent->parent = true;
			$cidbCodeParent->child  = false;
			$cidbCodeParent->subChild  = false;

			$cidbCodeArray[] = $cidbCodeParent;
			$cidbCodes  	 = CIDBCode::where("parent_id", $cidbCodeParent->id)->get();

			foreach($cidbCodes as $cidbCode)
			{
				$cidbCode->parent = false;
				$cidbCode->child  = true;
				$cidbCode->subChild  = false;

				$cidbCodeArray[] = $cidbCode;
				$subCidbCodes  	 = CIDBCode::where("parent_id", $cidbCode->id)->get();

				if($subCidbCodes && count($subCidbCodes) > 0)
				{
					$cidbCode->parent = true;
					
					foreach($subCidbCodes as $subCidbCode)
					{
						$subCidbCode->parent = false;
						$subCidbCode->child  = false;
						$subCidbCode->subChild  = true;

						$cidbCodeArray[] = $subCidbCode;
					}
				}
			}
		}

		return $cidbCodeArray;
	}

	public function companies()
	{
		return $this->belongsToMany('PCK\Companies\Company', 'company_cidb_code', 'cidb_code_id', 'company_id');
	}

}