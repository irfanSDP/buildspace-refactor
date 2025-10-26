<?php

class UnauthenticatedFormsController extends \BaseController {

    /**
     * Returns a generic view for when the user has successfully submitted a reply.
     *
     * @return \Illuminate\View\View
     */
    public function replySent()
    {
        return View::make('unauthenticated_forms.replySent');
    }
}