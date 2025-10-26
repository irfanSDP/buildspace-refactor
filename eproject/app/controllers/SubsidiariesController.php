<?php
use PCK\Exceptions\ValidationException;
use PCK\Forms\SubsidiaryForm;
use PCK\GeneralSettings\GeneralSetting;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Subsidiaries\Subsidiary;

class SubsidiariesController extends \BaseController {

    private $repository;
    private $form;

    public function __construct(SubsidiaryRepository $repository, SubsidiaryForm $form)
    {
        $this->repository = $repository;
        $this->form       = $form;
    }

    public function index()
    {
        return View::make('subsidiaries.index');
    }

    public function list()
    {
        if(GeneralSetting::count() > 0)
        {
            $filterByCompany = GeneralSetting::first()->view_own_created_subsidiary;

            $records = Subsidiary::select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id",
                'companies.id AS company_id', 'companies.name AS company_name')
                ->join('companies', 'companies.id', '=', 'subsidiaries.company_id')
                ->orderBy('subsidiaries.parent_id', 'asc')
                ->orderBy('subsidiaries.name', 'asc');
                
            if($filterByCompany)
            {
                $companyId = Confide::user()->company_id;

                $records->where('company_id',$companyId);
            }
            
            $records = $this->buildTree($records->get()->toArray());

        }
        else
        {
            $records = Subsidiary::select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id",
                'companies.id AS company_id', 'companies.name AS company_name')
                ->join('companies', 'companies.id', '=', 'subsidiaries.company_id')
                ->orderBy('subsidiaries.parent_id', 'asc')
                ->orderBy('subsidiaries.name', 'asc')
                ->get()
                ->toArray();

            $records = $this->buildTree($records);
        }

        return Response::json($records);
    }

    private function buildTree(array $subsidiaries, $parentId = 0)
    {
        $branch = [];

        foreach ($subsidiaries as $subsidiary)
        {
            if ($subsidiary['parent_id'] == $parentId)
            {
                $children = $this->buildTree($subsidiaries, $subsidiary['id']);
                if ($children)
                {
                    $subsidiary['_children'] = $children;
                }

                $subsidiary['route:edit'] = route('subsidiaries.edit', [$subsidiary['id']]);
                $subsidiary['route:delete'] = route('subsidiaries.delete', [$subsidiary['id']]);

                $branch[] = $subsidiary;
            }
        }

        if(!empty($branch))
        {
            $sort = [];
            foreach($branch as $k=>$v)
            {
                $sort['name'][$k] = $v['name'];
            }
            array_multisort($sort['name'], SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $branch);
        }

        return $branch;
    }

    public function create()
    {
        $filterByCompany = false;
       
        if(GeneralSetting::count() > 0)
        {
            $filterByCompany = GeneralSetting::first()->view_own_created_subsidiary;
        }
        if($filterByCompany)
        {
            $companyId = Confide::user()->company_id;
            
            $parents = Subsidiary::where('company_id',$companyId)
            ->select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id")
            ->orderBy('subsidiaries.name', 'asc')
            ->get()
            ->toArray();
        }
        else
        {
            $parents = Subsidiary::select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id")
            ->orderBy('subsidiaries.name', 'asc')
            ->get()
            ->toArray();
        }

        $parents = $this->buildTree($parents);

        $parents = $this->printTree($parents);
    
        return View::make('subsidiaries.edit', compact('subsidiary', 'parents'));
    }

    public function edit($subsidiaryId)
    {
        
        $subsidiary = Subsidiary::findOrFail($subsidiaryId);

        $filterByCompany = false;
       
        if(GeneralSetting::count() > 0)
        {
            $filterByCompany = GeneralSetting::first()->view_own_created_subsidiary;
        }
        if($filterByCompany)
        {
            $companyId = Confide::user()->company_id;

            $parents = Subsidiary::where('company_id',$companyId)
                ->select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id")
                ->where('subsidiaries.id', '<>', $subsidiary->id)
                ->orderBy('subsidiaries.name', 'asc')
                ->get()
                ->toArray();
        }
        else
        {
            $parents = Subsidiary::select("subsidiaries.id", "subsidiaries.name", "subsidiaries.identifier", "subsidiaries.parent_id")
                ->where('subsidiaries.id', '<>', $subsidiary->id)
                ->orderBy('subsidiaries.name', 'asc')
                ->get()
                ->toArray();
        }

        $parents = $this->buildTree($parents);
    
        $parents = $this->printTree($parents);

        
        return View::make('subsidiaries.edit', compact('subsidiary', 'parents'));
    }

    private function printTree(array $tree, $r = 0, $p = null)
    {
        $data = [
            '-1' => trans('forms.none')
        ];

        foreach ($tree as $i => $t)
        {
            $dash = empty($t['parent_id']) ? '' : str_repeat('-', $r) .' ';
            $data[$t['id']] = $dash." ".trim($t['name'])." (".trim($t['identifier']).")";
            if (isset($t['_children']))
            {
                $children = $this->printTree($t['_children'], $r+1, $t['parent_id']);

                $data += $children;
            }
        }

        return $data;
    }

    public function store()
    {
        $input = array_map(function($val) {
            return trim($val);
        }, Input::all());

        $this->form->validate($input);

        $user       = \Confide::user();
        $subsidiary = Subsidiary::find($input['id']);

        if(!$subsidiary)
        {
            $subsidiary = new Subsidiary();
            $subsidiary->company_id = $user->company->id;
        }

        $subsidiary->name       = trim($input['name']);
        $subsidiary->identifier = mb_strtoupper(trim($input['identifier']));
        $subsidiary->parent_id  = (!empty( $input['parent_id'] ) && (int)$input['parent_id'] > 0) ? $input['parent_id'] : null;

        $subsidiary->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('subsidiaries.index');
    }

    public function delete($subsidiaryId)
    {
        try
        {
            $subsidiary = Subsidiary::findOrFail($subsidiaryId);
            $subsidiary->delete();

            Flash::success(trans('subsidiaries.deleteSuccess'));
        }
        catch(ValidationException $e)
        {
            Flash::error($e->getMessage());
        }
        catch(Exception $e)
        {
            Flash::error(trans('subsidiaries.deleteFailed'));
        }

        return Redirect::route('subsidiaries.index');
    }

}