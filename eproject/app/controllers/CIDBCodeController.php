<?php

use PCK\CIDBCodes\CIDBCode;
use PCK\Forms\CIDBCodeForm;

class CIDBCodeController extends Controller
{
    private $CIDBCodeForm;

	public function __construct(CIDBCodeForm $CIDBCodeForm)
	{
		$this->CIDBCodeForm = $CIDBCodeForm; 
	}

    public function index()
    {

        $user = Confide::user();

		$records = CIDBCode::where("parent_id", NULL)->orderBy('id', 'ASC')->get();

		return View::make('cidb_codes.index', array('records'=>$records,'user'=>$user));
        
    }

    public function show($Id)
    {

        $user = Confide::user();

		$records = CIDBCode::find($Id);

        $children = CIDBCode::where("parent_id", $Id)->orderBy('id', 'ASC')->get();

        foreach ($children as $child)
        {
            $child->subChildren = CIDBCode::where("parent_id", $child->id)->orderBy('id', 'ASC')->get();

        }

		return View::make('cidb_codes.show', array('records'=>$records,'user'=>$user , 'children'=>$children));
        
    }

    public function create()
	{

		return View::make('cidb_codes.create');
	}

    public function edit($Id)
	{
		$record = CIDBCode::find($Id);

		return View::make('cidb_codes.edit', array('record' => $record));
	}

    public function list()
    {
        $data = [];

        foreach(CIDBCode::orderBy('id', 'ASC')->get() as $record)
        {
            array_push($data, [
                'id'           => $record->id,
                'code'         => $record->code,
                'description'  => $record->description,
                'route_update' => $record->route('cidb_codes.update', [$record->id]),
                'route_delete' => $record->route('cidb_codes.delete', [$record->id]),
            ]);
        }

        return Response::json($data);
    }

    public function store()
    {
        $inputs  = Input::all();
        

        try
        {
            $this->CIDBCodeForm->validate($inputs);

            $record              = new CIDBCode();
            $record->code        = $inputs['code'];
            $record->description = $inputs['description'];
            $record->parent_id   = NULL;
            $record->save();

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['code']} is successfully added!");
        
        return Redirect::route('cidb_codes.index');
    }

    public function update($Id)
    {
        $record = CIDBCode::find($Id);
        $inputs  = Input::all();

        try
        {
            $this->CIDBCodeForm->ignoreId($record->id);
			$this->CIDBCodeForm->validate($inputs);

            // $record             = CIDBcodes::find($Id);

            if($record)
            {
                $record->code         = $inputs['code'];
                $record->description  = $inputs['description'];
                $record->save();
            }

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['code']} is successfully updated!");
        
        return Redirect::route('cidb_codes.index');

    }

    private function deleteChildren($record)
    {
        $children = CIDBCode::where("parent_id", $record->id)->get();

        foreach ($children as $child)
        {
            $this->deleteChildren($child); // Recursively delete sub-children

            try
            {
                $child->delete();
            }
            catch(Exception $e)
            {
                Flash::error("This object cannot be deleted because it is used in other module.");

                return Redirect::route('cidb_codes.index');
            }
        }
    }

    public function destroy($Id)
    {
        $record = CIDBCode::find($Id);

        if($record)
        {
            $this->deleteChildren($record);
        }

        try
        {
            $record->delete();
        }
        catch(Exception $e)
        {
            Flash::error("This object cannot be deleted because it is used in other module.");

            return Redirect::route('cidb_codes.index');
        }

        Flash::success("This object is successfully deleted!");

        return Redirect::route('cidb_codes.index');
    }

    private function findTopLevelParent($name)
    {
        $parent = $name;

        while ($parent->parent_id !== null) 
        {
            $parent = CIDBCode::find($parent->parent_id);
        }
        
        return $parent;

    }

    public function childrenIndex($parentId)
    {

		$records = CIDBCode::where("parent_id", $parentId)->orderBy('id', 'ASC')->get();

        $name = CIDBCode::find($parentId);

        $nameId = $name ? $name->id : null;

        $FirstLevel = null;

        if ($name && $name->parent_id !== null) 
        {
            $FirstLevel = $this->findTopLevelParent($name);
        }

		return View::make('cidb_codes.cidb_codes_children.index' , array('parentId'=>$parentId , 'records'=>$records , 'name'=>$name , 'FirstLevel'=>$FirstLevel , 'nameId'=>$nameId));
        
    }

    public function childrenCreate($parentId)
	{
        $records = CIDBCode::where("parent_id", $parentId)->orderBy('id', 'ASC')->get();

        $name = CIDBCode::find($parentId);

        $nameId = $name ? $name->id : null;

        $FirstLevel = null;

        if ($name && $name->parent_id !== null) 
        {
            $FirstLevel = $this->findTopLevelParent($name);
        }

		return View::make('cidb_codes.cidb_codes_children.create' , array('parentId'=>$parentId , 'records'=>$records , 'name'=>$name , 'FirstLevel'=>$FirstLevel , 'nameId'=>$nameId));

	}
    public function childrenEdit($parentId , $Id)
    {
        $record = CIDBCode::find($Id);

        $name = CIDBCode::find($parentId);

        $nameId = $name ? $name->id : null;

        $FirstLevel = null;

        if ($name && $name->parent_id !== null) 
        {
            $FirstLevel = $this->findTopLevelParent($name);
        }

		return View::make('cidb_codes.cidb_codes_children.edit', array('record' => $record , 'parentId'=>$parentId , 'name'=>$name , 'id'=>$Id , 'FirstLevel'=>$FirstLevel , 'nameId'=>$nameId));
        
    }

    public function childrenShow($parentId , $Id)
    {

        $record = CIDBCode::find($Id);

        $name = CIDBCode::find($parentId);

        $nameId = $name ? $name->id : null;

        $FirstLevel = null;

        if ($name && $name->parent_id !== null) 
        {
            $FirstLevel = $this->findTopLevelParent($name);
        }

        $children = CIDBCode::where("parent_id", $Id)->orderBy('id', 'ASC')->get();

		return View::make('cidb_codes.cidb_codes_children.show', array('record'=>$record , 'children'=>$children , 'parentId'=>$parentId , 'name'=>$name , 'FirstLevel'=>$FirstLevel , 'id'=>$Id , 'nameId'=>$nameId));
        
    }

    public function childrenStore($parentId)
    {
        $inputs  = Input::all();
        

        try
        {
            $this->CIDBCodeForm->validateChildren($inputs);

            $record              = new CIDBCode();
            $record->code        = $inputs['code'];
            $record->description = $inputs['description'];
            $record->parent_id   = $parentId;
            $record->save();

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['code']} is successfully added!");
        
        return Redirect::route('cidb_codes_children.index' , array('parentId'=>$parentId));
    }

    public function childrenUpdate($parentId , $Id)
    {
        $record = CIDBCode::find($Id);
        $inputs  = Input::all();

        try
        {
            $this->CIDBCodeForm->ignoreIdChildren($record->id);
			$this->CIDBCodeForm->validateChildren($inputs);
			

            // $record             = CIDBcodes::find($Id);

            if($record)
            {
                $record->code         = $inputs['code'];
                $record->description  = $inputs['description'];
                $record->save();
            }

            
        }
        catch (\PCK\Exceptions\ValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($inputs);
        }

        Flash::success("{$inputs['code']} is successfully updated!");
        
        return Redirect::route('cidb_codes_children.index' , array('parentId'=>$parentId , 'id'=>$Id));

    }

    private function childrenDeleteChildren($record)
    {
        $children = CIDBCode::where("parent_id", $record->id)->get();

        foreach ($children as $child)
        {
            try
            {
                $child->delete();
            }
            catch (\PCK\Exceptions\ValidationException $e)
            {
                Flash::error("This object cannot be deleted because it is used in other module.");
                return Redirect::route('cidb_codes_children.index' , array('parentId'=>$record->id));
            }
        }
    }

    public function childrenDestroy($parentId , $Id)
    {
        $record = CIDBCode::find($Id);

        try
        {
            $record->delete();
        }
		catch(Exception $e)
        {
            Flash::error("This object cannot be deleted because it is used in other module.");

            return Redirect::route('cidb_codes_children.index' , array('parentId'=>$parentId));
        }

        Flash::success("This object is successfully deleted!");

        return Redirect::route('cidb_codes_children.index' , array('parentId'=>$parentId , 'id'=>$Id));
    }



}
