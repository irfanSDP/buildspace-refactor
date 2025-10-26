<?php

class ProcurementMethodController extends \BaseController {

    private $form;

    public function __construct(\PCK\Forms\ProcurementMethodForm $form)
    {
        $this->form = $form;
    }

    public function index()
    {
        return View::make('procurementMethods.index', array(
            'procurementMethods' => \PCK\ProcurementMethod\ProcurementMethod::orderBy('id', 'desc')->get(),
        ));
    }

    public function store()
    {
        $this->form->validate(Input::all());

        \PCK\ProcurementMethod\ProcurementMethod::create(array( 'name' => Input::get('name') ));
        Flash::success(trans('forms.saved'));

        return Redirect::back();
    }

    public function update($id)
    {
        $this->form->ignoreUnique($id);

        $this->form->validate(Input::all());

        $resource = \PCK\ProcurementMethod\ProcurementMethod::find($id);

        $resource->update(array( 'name' => Input::get('name') ));

        Flash::success(trans('forms.saved'));

        return Redirect::back();
    }

    public function destroy($id)
    {
        $resource = \PCK\ProcurementMethod\ProcurementMethod::find($id);

        try
        {
            $resource->delete();
            Flash::success(trans('forms.deleted'));
        }
        catch(Exception $exception)
        {
            Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }


}
