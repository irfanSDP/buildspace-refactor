<?php namespace PCK\Base;

class UploadRepository {

	private $upload;

	public function __construct(Upload $upload)
	{
		$this->upload = $upload;
	}

	public function createNew()
	{
		return $this->upload;
	}

	public function find($fileId)
	{
		return $this->upload->findOrFail($fileId);
	}

	public function findByIds($fileIds)
	{
		return $this->upload->whereIn('id', $fileIds)->get();
	}

}