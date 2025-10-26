<?php

use PCK\Forms\MasterCostDataForm;
use PCK\Buildspace\MasterCostData;

class MasterCostDataController extends Controller {

    private $form;

    public function __construct(MasterCostDataForm $form)
    {
        $this->form = $form;
    }

    public function index()
    {
        $costDataRecords = MasterCostData::orderBy('created_at', 'desc')->get();

        return View::make('costData.master.index', compact("costDataRecords"));
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = MasterCostData::select('bs_master_cost_data.id', 'bs_master_cost_data.name', 'bs_sf_guard_user_profile.name as created_by')
        ->join('bs_sf_guard_user', 'bs_sf_guard_user.id', '=', 'bs_master_cost_data.created_by')
        ->join('bs_sf_guard_user_profile', 'bs_sf_guard_user_profile.user_id', '=', 'bs_master_cost_data.created_by');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('bs_master_cost_data.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'created_by':
                        if(strlen($val) > 0)
                        {
                            $model->where('bs_sf_guard_user_profile.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('bs_master_cost_data.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'counter'           => $counter,
                'name'              => $record->name,
                'app_link'          => MasterCostData::generateAppLink($record->id),
                'route:edit'        => route('costData.master.edit', array($record->id)),
                'route:delete'      => route('costData.master.delete', array($record->id)),
                'created_by'        => $record->created_by,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function create()
    {
        return View::make('costData.master.create');
    }

    public function store()
    {
        $this->form->validate(Input::all());

        $user = Confide::user();

        MasterCostData::create(array( 'name' => Input::get('name'), 'created_by' => $user->getBsUser()->id, 'updated_by' => $user->getBsUser()->id ));

        return Redirect::route('costData.master');
    }

    public function edit($masterCostDataId)
    {
        $masterCostData = MasterCostData::find($masterCostDataId);

        return View::make('costData.master.edit', compact('masterCostData'));
    }

    public function update($masterCostDataId)
    {
        $this->form->ignoreUnique($masterCostDataId);

        $this->form->validate(Input::all());

        $masterCostData       = MasterCostData::find($masterCostDataId);
        $masterCostData->name = Input::get('name');
        $masterCostData->save();

        return Redirect::route('costData.master');
    }

    public function delete($masterCostDataId)
    {
        $masterCostData = MasterCostData::find($masterCostDataId);

        Flash::error(trans('costData.cannotBeDeleted', array( 'costData' => $masterCostData->name )));

        if( $masterCostData->canBeDeleted() )
        {
            $masterCostData->delete();

            Flash::success(trans('costData.deleted', array( 'costData' => $masterCostData->name )));
        }

        return Redirect::back();
    }

}