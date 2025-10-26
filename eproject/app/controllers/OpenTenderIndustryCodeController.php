<?php

use PCK\Tenders\OpenTenderIndustryCode;
use PCK\Forms\OpenTenderIndustryCodeForm;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroups\ContractGroup;
use PCK\CIDBCodes\CIDBCode;
use PCK\CIDBGrades\CIDBGrade;
use Illuminate\Support\Facades\DB;
use PCK\ContractGroups\Types\Role;

class OpenTenderIndustryCodeController extends \BaseController {

	public function __construct(OpenTenderIndustryCodeForm $openTenderIndustryCodeForm)
	{
		$this->openTenderIndustryCodeForm = $openTenderIndustryCodeForm;
	}

	public function create($project,$tenderId)
	{
		$user = Confide::user();

		$vendorCategories = $this->getVendorCategories($project);

		$cidbGrades = CIDBGrade::all();

		$cidbCodes = CIDBCode::getCidbCodes();

		return View::make('tenders.open_tender_industry_code.create', array('project'=>$project, 'tenderId' => $tenderId, 'vendorCategories' => $vendorCategories, 'cidbGrades' => $cidbGrades, 'cidbCodes' => $cidbCodes));
	}

	public function store($project,$tenderId)
	{
		$input = Input::all();
		$user = Confide::user();

		try
		{
			$this->openTenderIndustryCodeForm->validate($input);
			$input["tender_id"]  =  $tenderId;
            $input["created_by"] =  $user->id;

		    OpenTenderIndustryCode::create($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is created successfully!");

		return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId,'industryCode'));
	}

	public function edit($project,$tenderId,$id)
	{
		$user = Confide::user();

		$industryCode = OpenTenderIndustryCode::find($id);

		$vendorCategories = $this->getVendorCategories($project);

		$vendorWorkCategories = $this->getVendorWorkCategories($industryCode->vendor_category_id);

		$cidbGrades = CIDBGrade::all();
		$cidbCodes = CIDBCode::getCidbCodes();

		return View::make('tenders.open_tender_industry_code.edit', array('industryCode' => $industryCode, 'vendorCategories' => $vendorCategories, 'vendorWorkCategories' => $vendorWorkCategories, 'cidbGrades' => $cidbGrades, 'cidbCodes' => $cidbCodes, 'project'=>$project, 'tenderId' => $tenderId));
	}

	public function update($project,$tenderId,$id)
	{
		$input = Input::all();

		try
		{
			$this->openTenderIndustryCodeForm->validate($input);
			$input = $this->processInput($input);

			OpenTenderIndustryCode::where("id", $id)->update($input);
		}
		catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

        Flash::success("Record is updated successfully!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'industryCode'));
	}

	public function destroy($project, $tenderId, $id)
	{
		try
		{
			$record = OpenTenderIndustryCode::find($id);
			$record->delete();
		}
		catch(Exception $e)
		{
			Flash::error("This object cannot be deleted.");

            return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'industryCode'));
		}

		Flash::success("This object is successfully deleted!");

        return Redirect::route('projects.tender.open_tender.get', array($project->id,$tenderId, 'industryCode'));
	}

	public function getVendorCategories($project)
	{
		$vendorCategoryArray = [];

		$contractGroup = ContractGroup::where("group", ROLE::CONTRACTOR)->first(); // get contractor contract group

		$records = DB::table("contract_group_contract_group_category")->where("contract_group_id", $contractGroup->id)->get(); 

		foreach($records as $record)
		{
			$vendorCategories = VendorCategory::where("contract_group_category_id", $record->contract_group_category_id)->get();

			foreach($vendorCategories as $vendorCategory)
			{
				array_push($vendorCategoryArray,$vendorCategory);
			}
		}

		return $vendorCategoryArray;
	}

	public function getVendorWorkCategories($vendorCategoryId)
	{
		$vendorWorkCategoryArray = [];
		$records = DB::table("vendor_category_vendor_work_category")->where("vendor_category_id",$vendorCategoryId)->get();

		foreach($records as $record)
		{
			$vendorWorkCategoryArray[] = VendorWorkCategory::where("id", $record->vendor_work_category_id)->first();
		}

		return $vendorWorkCategoryArray;
	}

	public function getVendorWorkCategoriesOnDropdownSelect($project,$tenderId)
	{
		$input = Input::all();

		$vendorWorkCategoryArray = [];

		$vendorCategoryId = $input["vendor_category_id"];

		return $this->getVendorWorkCategories($vendorCategoryId);

	}

	public function processInput($input)
	{
		foreach($input as $key => $value)
		{
			if($input[$key] == "")
			{
				$input[$key] = NULL;
			}

			if($key == "_method" || $key == "_token" || $key == "Submit" || $key == "form_type")
			{
				unset($input[$key]);
			}
		}

		return $input;
	}



}
