<?php
use Illuminate\Database\Eloquent\Model;

class BaseController extends Controller {
    /**
    * Setup the layout used by the controller.
    *
    * @return void
    */
    protected function setupLayout()
    {
        if ( ! is_null($this->layout) )
        {
            $this->layout = View::make($this->layout);
        }
    }

    public function getAttachmentDetails(Model $object = null)
    {
        $uploadedFiles = array();
        $uploadRepo    = App::make('PCK\Base\UploadRepository');

        if ( Input::old('uploaded_files') )
        {
            $uploadedFileIds = Input::old('uploaded_files');
        }
        elseif ( is_object($object) )
        {
            foreach ( $object->attachments as $attachment )
            {
                $uploadedFileIds[] = $attachment->upload_id;
            }
        }

        if ( ! empty( $uploadedFileIds ) )
        {
            $uploadedFiles = $uploadRepo->findByIds($uploadedFileIds);
        }

        return $uploadedFiles;
    }

    public function noScript()
    {
        return View::make('errors.no_access', [
            'title'   => 'No Javascript',
            'content' => 'For full functionality of this system it is necessary to enable JavaScript.'
        ]);
    }
}
