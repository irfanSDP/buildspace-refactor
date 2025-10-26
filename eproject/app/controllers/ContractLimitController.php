<?php

use PCK\ContractLimits\ContractLimit;
use PCK\Forms\ContractLimitForm;

class ContractLimitController extends \BaseController {

    private $contractLimitForm;

    public function __construct(ContractLimitForm $contractLimitForm)
    {

        $this->contractLimitForm = $contractLimitForm;
    }

    public function index()
    {
        $contractLimits = ContractLimit::orderBy('limit', 'asc')->get();

        return View::make('contract_limits.index', array(
            'contractLimits' => $contractLimits,
        ));
    }

    public function create()
    {
        return View::make('contract_limits.create');
    }

    public function store()
    {
        $input = Input::all();
        $this->contractLimitForm->validate($input);

        ContractLimit::create(array( 'limit' => $input['limit'] ));

        Flash::success('forms.saved');

        return Redirect::route('contractLimit.index');
    }

    public function edit($id)
    {
        $contractLimit = ContractLimit::find($id);

        return View::make('contract_limits.edit', compact('contractLimit'));
    }

    public function update($id)
    {
        $input = array_map('trim', Input::all());

        $this->contractLimitForm->ignoreUnique($id);

        $this->contractLimitForm->validate($input);

        ContractLimit::find($id)->update(array( 'limit' => $input['limit'] ));

        Flash::success(trans('forms.saved'));

        return Redirect::route('contractLimit.index');
    }

    public function destroy($id)
    {
        $contractLimit = ContractLimit::find($id);

        Flash::error(trans('forms.cannotBeDeleted'));

        if( $contractLimit->canBeDeleted() )
        {
            $contractLimit->delete();

            Flash::success(trans('forms.deleted'));
        }

        return Redirect::route('contractLimit.index');
    }
}
