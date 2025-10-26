<?php

use PCK\Buildspace\CostData;
use PCK\Buildspace\CostDataType;
use PCK\Forms\CostDataTypeForm;

class CostDataTypesController extends Controller {

    private $form;

    public function __construct(CostDataTypeForm $form)
    {
        $this->form = $form;
    }

    public function index()
    {
        $user = Confide::user();

        return View::make('cost_data_types.index');
    }

    public function list()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = CostDataType::select('id', 'name');

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
                            $model->where('name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        $usedRecords = CostData::lists('cost_data_type_id');

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'deletable'    => ! in_array($record->id, $usedRecords),
                'route:delete' => route('costDataTypes.destroy', array($record->id)),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function update()
    {
        $request = Request::instance();

        $this->form->validate($input = $request->all());

        if($this->form->success)
        {
            if($input['id'] == -1)
            {
                unset($input['id']);

                CostDataType::create($input);
            }
            else
            {
                $object = CostDataType::find($input['id']);

                unset($input['id']);

                $object->update($input);
            }
        }

        return array(
            'success' => $this->form->success,
            'errors'  => $this->form->getErrorMessages(),
        );
    }

    public function destroy($costDataTypeId)
    {
        try
        {
            $usedRecords = CostData::lists('cost_data_type_id');

            $costDataType = CostDataType::find($costDataTypeId);

            if(!in_array($costDataTypeId, $usedRecords))
            {
                $costDataType->delete();

                \Flash::success(trans('forms.deleted'));
            }
            else
            {
                \Flash::error(trans('forms.cannotBeDeleted:inUse'));
            }
        }
        catch(\Exception $e)
        {
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }

}