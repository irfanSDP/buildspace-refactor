<?php namespace PCK\TenderDocumentFolders;

use PCK\Projects\Project;
use PCK\Users\UserRepository;
use PCK\StructuredDocument\StructuredDocumentRepository;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroups\Types\Role;
use Illuminate\Database\Eloquent\Model;

class TenderDocumentFolderRepository extends BaseModuleRepository {

	private $tenderDocumentFolder;

	protected $events;

	private $userRepo;
	private $structuredDocumentRepository;

	public function __construct(TenderDocumentFolder $tenderDocumentFolder, Dispatcher $events, UserRepository $userRepo, StructuredDocumentRepository $structuredDocumentRepository)
	{
		$this->tenderDocumentFolder = $tenderDocumentFolder;
		$this->events               = $events;
		$this->userRepo             = $userRepo;
		$this->structuredDocumentRepository             = $structuredDocumentRepository;
	}

	public function find(Project $project, $folderId)
	{
		return $this->tenderDocumentFolder
			->where('project_id', '=', $project->id)
			->findOrFail($folderId);
	}

	public function findByFolderName(Project $project, $folderName, $isSystemGeneratedFolder = null)
	{
		$query = $this->tenderDocumentFolder
			->where('project_id', '=', $project->id)
			->where('name', '=', $folderName);

		if( ! is_null($isSystemGeneratedFolder) ) $query->where('system_generated_folder', '=', $isSystemGeneratedFolder);

		return $query->first();
	}

	public function createNewSystemFolderUnderBQFiles(Project $project, $newFolderNameToBeCreated)
	{
		$bqFilesFolder = $this->findByFolderName($project, TenderDocumentFolder::DEFAULT_BQ_FILES_FOLDER_NAME, true);

		$node                          = $this->tenderDocumentFolder->newInstance();
		$node->name                    = $newFolderNameToBeCreated;
		$node->project_id              = $project->id;
		$node->root_id                 = $bqFilesFolder->root_id;
		$node->priority                = $bqFilesFolder->priority;
		$node->parent_id               = $bqFilesFolder->id;
		$node->system_generated_folder = true;

		$node->save();

		$node->makeChildOf($bqFilesFolder);

		return $node;
	}

	public function sendUploadedFileNotification(TenderDocumentFolder $folder)
	{
		$project  = $folder->project;
		$viewName = 'tender_document_new_upload';
		$route    = 'projects.tenderDocument.myFolder';
		$role     = array( Role::PROJECT_OWNER, Role::GROUP_CONTRACT );
		$users    = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, $role);

		$this->sendSystemNotificationByUsers($project, $folder, $users->toArray(), $viewName, $route);

		if ( $project->inCallingTender() )
		{
			// get latest tender selected final contractor
			$contractors       = $project->latestTender->selectedFinalContractors->lists('id');
			$companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds($contractors);

			$this->sendEmailNotificationByUsers($project, $folder, $companyAdminUsers->toArray(), $viewName, $route);
		}
	}

	public function sendDeleteFolderNotification(TenderDocumentFolder $folder)
	{
		$project  = $folder->project;
		$viewName = 'tender_document_folder_delete';
		$route    = 'projects.tenderDocument.index';

		$this->sendSystemNotification($project, $folder, [ Role::PROJECT_OWNER ], $viewName, $route);

		$this->sendTenderDocumentSystemNotification($project, $folder, [ Role::CONTRACTOR ], $viewName, $route);
	}

	private function sendTenderDocumentSystemNotification(Project $project, Model $model, array $roles, $viewName, $routeName, $tabId = null)
	{
		$this->checkEventsProperty();

		return $this->events->fire('system.sendSystemNotificationToCompanyAdminOnly', compact(
			'project', 'model', 'roles', 'viewName', 'routeName', 'tabId'
		));
	}

	public function getTemporaryFileAbsolutePathByDocumentFolderPath(TenderDocumentFolder $targetFolder)
	{
		$files = array();

		$folders = $targetFolder->descendantsAndSelf()->get();

		foreach($folders as $folder)
		{
			$ancestorsAndSelf = $folder->getAncestorsAndSelf();

			$pathToFolder = array();

			foreach($ancestorsAndSelf as $ancestorFolder)
			{				
				if($ancestorFolder->depth < $targetFolder->depth) continue;

				$pathToFolder[] = $ancestorFolder->name;
			}

			$pathToFolder = implode('/', $pathToFolder);

			if($folder->folder_type == TenderDocumentFolder::TYPE_FOLDER)
			{
				foreach($folder->firstRevisionFiles as $file)
				{
					$file = $file->getLatestRevisionFile();

					$outputFileAbsolutePath = "{$pathToFolder}/{$file->filename}.{$file->fileProperties->extension}";

					\PCK\Helpers\Files::copy($file->fileProperties->getFullFilePath(), $downloadPath = \PCK\Helpers\Files::getTmpFileUri());

					$files[$outputFileAbsolutePath] = $downloadPath;
				}
			}
			elseif($folder->folder_type == TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT)
			{
				$outputFileAbsolutePath = "{$pathToFolder}/{$folder->name}".'.'.\PCK\Helpers\Files::EXTENSION_PDF;
				$files[$outputFileAbsolutePath] = $this->structuredDocumentRepository->getStructuredDocumentPhysicalFile($folder);
			}	
		}

		return $files;
	}

}