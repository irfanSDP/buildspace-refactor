<?php

use PCK\Base\UploadRepository;

class GeneralUploadsController extends \BaseController {

	private $uploadRepo;

	public function __construct(UploadRepository $uploadRepo)
	{
		$this->uploadRepo = $uploadRepo;
	}

	public function download($fileId, $objectId = null)
	{
		$cabinetInfo = $this->uploadRepo->find($fileId);

		if ( !$cabinetInfo )
		{
			App::abort(404);
		}

		return Response::download(
			$cabinetInfo->physicalPath() . $cabinetInfo->filename,
			$cabinetInfo->filename, array(
				'Content-Type: ' . $cabinetInfo->mimetype,
			)
		);
	}

	/**
	 * Store a newly created Module Upload in storage.
	 *
	 * @param $objectId
	 * @return Response
	 */
	public function store($objectId = null)
	{
		$file = Input::file('file');

		$upload = $this->uploadRepo->createNew();

		try
		{
			$upload->process($file);
		}
		catch (Exception $exception)
		{
			// Something went wrong. Log it.
			Log::error($exception);

			$errors = array(
				'name'  => $file->getClientOriginalName(),
				'size'  => $file->getSize(),
				'error' => $exception->getMessage()
			);

			// Return error
			return Response::json($errors, 400);
		}

		// this creates the response structure for jquery file upload
		$success               = new stdClass();
		$success->name         = $upload->filename;
		$success->size         = $upload->size;
		$success->url          = $upload->download_url;
		$success->thumbnailUrl = $upload->generateThumbnailURL();
		$success->deleteUrl    = isset( $objectId ) ? $upload->generateDeleteURL($objectId) : $upload->generateGeneralDeleteURL();
		$success->deleteType   = 'POST';
		$success->fileID       = $upload->id;
		$success->created_at   = $upload->created_at;

		return Response::json(array( 'files' => array( $success ) ));
	}

	/**
	 * Remove the specified Module Upload from storage.
	 *
	 * @param $objectId
	 * @param $fileId
	 * @return Response
	 */
	public function destroy($fileId, $objectId = null)
	{
		$upload = $this->uploadRepo->find($fileId);

		$upload->delete();

		$success                      = new stdClass();
		$success->{$upload->filename} = true;

		return Response::json(array( 'files' => array( $success ) ));
	}

}