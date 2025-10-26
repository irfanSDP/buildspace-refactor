<?php

class TranslationController extends \BaseController
{
    public function translate()
    {
        $success      = false;
        $translation  = [];
        $errorMessage = null;
        $locale       = Input::get('locale');
        $params       = Input::get('params') ?? [];

        if( ! is_array( $ids = Input::get('ids') ) )
        {
            $ids    = [$ids];
            $params = [$params];
        }

        try
        {
            if( $user = \Confide::user() ) $locale = $user->settings->language->code;

            foreach($ids as $key => $id)
            {
                $translation[$id] = trans($id, $params[$key] ?? [], null, $locale);
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            $errorMessage = $e->getMessage();
        }

        return [
            'success'      => true,
            'translation'  => $translation,
            'errorMessage' => $errorMessage,
        ];
    }
}