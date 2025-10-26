<?php

use PCK\Base\Upload;
use PCK\ContractGroups\ContractGroup;
use PCK\Forms\DocumentManagementFolderForm;
use PCK\DocumentManagementFolders\ProjectDocumentFile;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\DocumentManagementFolders\DocumentManagementFolderRepository;
use PCK\Projects\Project;
use PCK\Helpers\Hierarchy\AdjacencyListsAndNestedSets;
use PCK\Notifications\EmailNotifier;

class DocumentManagementsController extends \BaseController {

    private $documentManagementFolderForm;
    private $user;
    private $documentManagementFolderRepo;
    private $emailNotifier;

    public function __construct(
        DocumentManagementFolderForm $documentManagementFolderForm,
        DocumentManagementFolderRepository $documentManagementFolderRepo,
        EmailNotifier $emailNotifier
    )
    {
        $this->documentManagementFolderForm = $documentManagementFolderForm;
        $this->user                         = Confide::user();
        $this->documentManagementFolderRepo = $documentManagementFolderRepo;
        $this->emailNotifier                = $emailNotifier;
    }

    /**
     * Display a listing of the resource.
     *
     * @param $project
     * @param $folderId
     *
     * @return Response
     */
    public function index($project, $folderId)
    {
        $root = DocumentManagementFolder::find($folderId);
        $user = $this->user;

        $sharedFolderIds = array();

        if( $user->isSuperAdmin() )
        {
            $descendants     = $root->getDescendants()->toArray();
            $myContractGroup = null;
            $sharedFolders   = array();
        }
        else
        {
            $myContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
            $descendants     = $root->getDescendantsByContractGroup($myContractGroup);
            $sharedFolders   = DocumentManagementFolder::getSharedFoldersByContractGroup($myContractGroup, $project, $root->folder_type);

            $pivotResults = DB::table('contract_group_document_management_folder AS xref')
                ->join($root->getTable() . " AS f", 'f.id', '=', 'xref.document_management_folder_id')
                ->select('f.id AS folder_id')
                ->where('f.' . $root->getLeftColumnName(), '>=', $root->getLeft())
                ->where('f.' . $root->getLeftColumnName(), '<', $root->getRight())
                ->where('f.project_id', '=', $project->id)
                ->where('f.folder_type', '=', $root->folder_type)
                ->where('f.contract_group_id', '=', $myContractGroup->id)
                ->distinct()
                ->get();

            foreach($pivotResults as $record)
            {
                $sharedFolderIds[ $record->folder_id ] = $record->folder_id;
            }

            unset( $pivotResults );
        }

        $contractGroups = ContractGroup::orderBy('id', 'desc')->get();

        $groupNames = array();

        foreach($contractGroups as $group)
        {
            $groupNames[ $group->id ] = $project->getRoleName($group->group);
        }

        if( $sharedFolderIds )
        {
            foreach($descendants as $key => $descendant)
            {
                if( array_key_exists($descendant['id'], $sharedFolderIds) )
                {
                    $descendants[ $key ]['been_shared'] = true;
                    unset( $sharedFolderIds[ $descendant['id'] ] );
                }
            }
        }

        $folderToCount = $this->documentManagementFolderRepo->getFolderCount($sharedFolders, $descendants);

        $converter = new AdjacencyListsAndNestedSets();

        $nestedSetArray = $this->documentManagementFolderRepo->getNestedSetArray($descendants);
        $converter->setNestedSet($root->id, $nestedSetArray);
        $adjacencyListDescendants = $converter->convertNestedSetToAdjacencyList();

        return View::make('document_management_folders.index', array(
                'descendants'     => $adjacencyListDescendants,
                'sharedFolders'   => $sharedFolders,
                'user'            => $user,
                'folderToCount'   => $folderToCount,
                'root'            => $root,
                'project'         => $project,
                'contractGroups'  => $contractGroups,
                'groupNames'      => $groupNames,
                'myContractGroup' => $myContractGroup
            )
        );
    }

    /**
     * Saves the new position (hierarchy) of the folders.
     *
     * Todo: Consider locking related folder records before this process starts so that an added folder somewhere within the set will not corrupt the data.
     *
     * @param $project
     * @param $rootFolderId
     *
     * @return string
     */
    public function saveNewFolderPosition($project, $rootFolderId)
    {
        // Repositions folders by group so that it is easier to do rearranging,
        // in the sense that we don't have to care about folders from other groups that may be in between/surrounding the folders we want to rearrange.
        if( ! $this->documentManagementFolderRepo->repositionFoldersByRootId($rootFolderId) )
        {
            return json_encode(array(
                'success' => false,
                'message' => 'Could not reposition folders'
            ));
        }

        $foldersJson = Input::get('folders');

        $converter = new AdjacencyListsAndNestedSets();
        $converter->setAdjacencyList($foldersJson);

        $contractGroup     = $this->user->getAssignedCompany($project)->getContractGroup($project);
        $foldersStartIndex = $this->documentManagementFolderRepo->getFolderStartIndexByGroup($rootFolderId, $contractGroup);
        $folders           = $converter->convertAdjacencyListToNestedSet($rootFolderId, $foldersStartIndex);

        $success = $this->documentManagementFolderRepo->saveNewFolderPositions($folders);

        $this->documentManagementFolderRepo->updateRootFolderRgt($rootFolderId);

        return json_encode(array( 'success' => $success ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $project
     *
     * @return Response
     */
    public function folderCreate($project)
    {
        $inputs                = Input::all();
        $parent                = DocumentManagementFolder::find($inputs['parent_id']);
        $inputs['project_id']  = $project->id;
        $inputs['folder_type'] = $parent->folder_type;
        $errors                = array();
        $contractGroup         = $this->user->getAssignedCompany($project)->getContractGroup($project);

        try
        {
            $this->documentManagementFolderForm->validate($inputs);

            $node = new DocumentManagementFolder();

            $node->name              = $inputs['name'];
            $node->project_id        = $project->id;
            $node->folder_type       = $parent->folder_type;
            $node->priority          = $parent->priority;
            $node->parent_id         = $parent->id;
            $node->root_id           = $parent->root_id;
            $node->contract_group_id = $contractGroup->id;

            $node->save();

            $node->makeChildOf($parent);

            $success = true;
        }
        catch(Exception $e)
        {
            $errors  = $e->getErrors();
            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    public function folderUpdate($project)
    {
        $inputs                = Input::all();
        $node                  = DocumentManagementFolder::find($inputs['id']);
        $inputs['project_id']  = $project->id;
        $inputs['parent_id']   = $node->parent_id;
        $inputs['folder_type'] = $node->folder_type;
        $errors                = array();

        try
        {
            $this->documentManagementFolderForm->validate($inputs);

            $node->name = $inputs['name'];

            $node->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors  = $e->getErrors();
            $success = false;
        }

        return Response::json(compact('success', 'errors'));
    }

    public function folderDelete()
    {
        if( Request::ajax() )
        {
            $inputs = Input::all();
            $node   = DocumentManagementFolder::find($inputs['id']);

            try
            {
                $this->documentManagementFolderRepo->sendDeleteFolderNotification($node);

                $node->delete();

                $success = true;
            }
            catch(Exception $e)
            {
                $success = false;
            }

            return Response::json(compact('success'));
        }

        App::abort(404);
    }

    public function folderShare()
    {
        if( Request::ajax() )
        {
            $inputs  = Input::all();
            $node    = DocumentManagementFolder::find($inputs['folderId']);
            $success = true;

            $checkedList = array_key_exists('checked', $inputs) ? $inputs['checked'] : array();

            $node->shareToContractGroups($checkedList);

            return Response::json(compact('success'));
        }

        App::abort(404);
    }

    public function folderInfo($project, $folderId)
    {
        if( Request::ajax() )
        {
            $folder = DocumentManagementFolder::find($folderId);

            return Response::json($folder);
        }

        App::abort(404);
    }

    public function sharedFolderInfo($project, $folderId)
    {
        if( Request::ajax() )
        {
            $folder = DocumentManagementFolder::find($folderId);

            $contractGroupIds = array();

            foreach($folder->contractGroups as $contractGroup)
            {
                $contractGroupIds[] = $contractGroup->id;
            }

            return Response::json($contractGroupIds);
        }

        App::abort(404);
    }

    public function myFolder($project, $folderId)
    {
        $node            = DocumentManagementFolder::find($folderId);
        $children        = $node->children()->get()->toArray();
        $user            = $this->user;
        $myContractGroup = null;
        $isSharedFolder  = false;

        if( ! $user->isSuperAdmin() )
        {
            $myContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
        }

        if( ! $node || $node->depth == 0 )
        {
            App::abort(404);
        }

        return View::make('document_management_folders.view', compact('user', 'node', 'project', 'children', 'isSharedFolder', 'myContractGroup'));
    }

    public function mySharedFolder($project, $folderId)
    {
        $node = DocumentManagementFolder::find($folderId);
        $user = $this->user;

        $isSharedFolder  = true;
        $sharedFolderIds = false;

        if( $user->isSuperAdmin() )
        {
            $myContractGroup = null;
            $children        = $node->children->toArray();
        }
        else
        {
            $myContractGroup = $user->getAssignedCompany($project)->getContractGroup($project);
            $children        = $node->getSharedChildrenByContractGroup($myContractGroup)->toArray();

            $root         = DocumentManagementFolder::find($node->root_id);
            $pivotResults = DB::table('contract_group_document_management_folder AS xref')
                ->join($root->getTable() . " AS f", 'f.id', '=', 'xref.document_management_folder_id')
                ->select('f.id AS folder_id')
                ->where('f.' . $root->getLeftColumnName(), '>=', $root->getLeft())
                ->where('f.' . $root->getLeftColumnName(), '<', $root->getRight())
                ->where('f.project_id', '=', $project->id)
                ->where('f.folder_type', '=', $root->folder_type)
                ->where('f.contract_group_id', '<>', $myContractGroup->id)
                ->distinct()
                ->get();

            foreach($pivotResults as $record)
            {
                $sharedFolderIds[ $record->folder_id ] = $record->folder_id;
            }

            unset( $pivotResults );
        }

        if( ! $node || $node->depth == 0 || ( ! $user->isSuperAdmin() && ! $node->isSharedForContractGroup($myContractGroup) ) )
        {
            App::abort(404);
        }

        return View::make('document_management_folders.view', compact('user', 'node', 'project', 'children', 'isSharedFolder', 'sharedFolderIds'));
    }

    public function upload(Project $project, $folderId)
    {
        $node = DocumentManagementFolder::find($folderId);

        if( ! $node || $node->depth == 0 )
        {
            App::abort(404);
        }

        $file = Input::file('file');

        $upload = new Upload;

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
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

        // If it now has an id, it should have been successful.
        if( $upload->id )
        {
            $fileParts = pathinfo($upload->filename);

            //save project doc file and links it to cabinet file
            $projectDocumentFile                             = new ProjectDocumentFile();
            $projectDocumentFile->filename                   = $fileParts['filename'];
            $projectDocumentFile->cabinet_file_id            = $upload->id;
            $projectDocumentFile->project_document_folder_id = $node->id;

            $projectDocumentFile->save();

            $newUrl = asset($upload->publicPath() . $fileParts['filename'] . '.' . $fileParts['extension']);

            $supportedImage = array(
                'gif',
                'jpg',
                'jpeg',
                'png'
            );

            $thumbnail = in_array(strtolower($fileParts['extension']), $supportedImage) ? $upload->publicPath() . $fileParts['filename'] . '_84x64.' . $fileParts['extension'] : 'img/default-file.png';
            // this creates the response structure for jquery file upload
            $success               = new stdClass();
            $success->name         = $fileParts['filename'] . '.' . $fileParts['extension'];
            $success->size         = $upload->size;
            $success->url          = $newUrl;
            $success->thumbnailUrl = asset($thumbnail);
            $success->deleteUrl    = action('DocumentManagementsController@uploadDelete', array( $project->id, $upload->id ));
            $success->deleteType   = 'POST';
            $success->fileID       = $upload->id;

            $this->documentManagementFolderRepo->sendUploadedFileNotification($node);

            return Response::json(array( 'files' => array( $success ) ));
        }

        return Response::json('Error', 400);
    }

    public function uploadDelete(Project $project, $id)
    {
        if( Request::ajax() )
        {
            $upload = Upload::find($id);
            $upload->delete();

            $success                      = new stdClass();
            $success->{$upload->filename} = true;

            return Response::json(array( 'files' => array( $success ) ));
        }

        App::abort(404);
    }

    public function fileList(Project $project, $folderId)
    {
        $data = array();
        $node = DocumentManagementFolder::find($folderId);

        if( ! Request::ajax() || ! $node || $node->depth == 0 )
        {
            App::abort(404);
        }

        $projectDocumentFileObj = new ProjectDocumentFile();

        $files = DB::table($projectDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.cabinet_file_id", "f.filename AS document_filename", "f.revision", "f.description", DB::raw("f.created_at AS date_issued"), "users.name AS issued_by", "uploads.filename", "uploads.extension", "uploads.path")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.project_document_folder_id', '=', $node->id)
            ->whereRaw('f.id IN (SELECT f2.id FROM ' . $projectDocumentFileObj->getTable() . ' f2  WHERE (parent_id, revision) IN
				(SELECT f3.parent_id, MAX(f3.revision) FROM ' . $projectDocumentFileObj->getTable() . ' f3 WHERE f3.id NOT IN
				(SELECT f4.parent_id FROM ' . $projectDocumentFileObj->getTable() . ' f4 WHERE f4.parent_id IS NOT NULL AND f4.revision <> 0 AND
				f4.project_document_folder_id = ' . $node->id . ' GROUP BY f4.parent_id)
				GROUP BY f3.parent_id) AND f2.project_document_folder_id = ' . $node->id . ')')
            ->orderBy('f.created_at', 'desc')
            ->get();

        foreach($files as $file)
        {
            $data[] = array(
                'filename'           => $file->document_filename . '.' . $file->extension,
                'description'        => $file->description,
                'revision'           => $file->revision,
                'date_issued'        => $project->getProjectTimeZoneTime($file->date_issued)->format(\Config::get('dates.standard')),
                'issued_by'          => $file->issued_by,
                'id'                 => $file->id,
                'physicalFileExists' => ProjectDocumentFile::find($file->id)->fileExists()
            );
        }

        return Response::json(compact('data'));
    }

    public function fileInfo(Project $project, $fileId)
    {
        $file         = ProjectDocumentFile::with('fileProperties.createdBy')->find($fileId);
        $cabinetInfo  = $file->fileProperties;
        $thumbNailSrc = 'img/default-file.png';

        if( ! Request::ajax() || ! $file )
        {
            App::abort(404);
        }

        $thumbnail = Upload::where('filename', '=', str_replace('.' . $cabinetInfo->extension, '', $cabinetInfo->filename) . '_84x64.' . $cabinetInfo->extension)
            ->where('parent_id', '=', $cabinetInfo->id)
            ->first();

        if( $thumbnail )
        {
            $thumbNailSrc = $thumbnail->path;
        }

        return Response::json(array(
            'filename'           => $file->filename,
            'description'        => $file->description,
            'revision'           => $file->revision,
            'parent_id'          => $file->parent_id,
            'date_issued'        => date('d/m/Y', strtotime($project->getProjectTimeZoneTime($file->created_at))),
            'issued_by'          => $cabinetInfo->createdBy->name,
            'thumbnail_src'      => $thumbNailSrc,
            'id'                 => $file->id,
            'physicalFileExists' => $file->fileExists()
        ));
    }

    public function revisionList(Project $project, $fileId)
    {
        $file = ProjectDocumentFile::find($fileId);

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        $projectDocumentFileObj = new ProjectDocumentFile();

        $files = DB::table($projectDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.filename", "uploads.extension")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.id', '<>', $file->id)
            ->where('f.project_document_folder_id', '=', $file->project_document_folder_id)
            ->whereRaw('f.id IN (SELECT f2.id FROM ' . $projectDocumentFileObj->getTable() . ' f2  WHERE (parent_id, revision) IN
				(SELECT f3.parent_id, MAX(f3.revision) FROM ' . $projectDocumentFileObj->getTable() . ' f3 WHERE f3.id NOT IN
				(SELECT f4.parent_id FROM ' . $projectDocumentFileObj->getTable() . ' f4 WHERE f4.parent_id IS NOT NULL AND f4.revision <> 0 AND
				f4.project_document_folder_id = ' . $file->project_document_folder_id . ' GROUP BY f4.parent_id) GROUP BY f3.parent_id)
				AND f2.project_document_folder_id = ' . $file->project_document_folder_id . ' AND f2.id <> ' . $file->id . ')')
            ->orderBy('f.filename')
            ->get();

        if( $file->revision > 0 )
        {
            $previousRevision = DB::table($projectDocumentFileObj->getTable() . ' AS f')
                ->select('f.id', 'f.filename', 'uploads.extension')
                ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
                ->where('f.revision', '=', $file->revision - 1)
                ->where('f.parent_id', '=', $file->parent_id)
                ->where('f.project_document_folder_id', '=', $file->project_document_folder_id)
                ->first();

            array_push($files, $previousRevision);
        }
        else
        {
            $previousRevision = $file;
        }

        return Response::json(array(
            'data'     => $files,
            'selected' => $previousRevision->id
        ));
    }

    public function uploadUpdate(Project $project)
    {
        $data = Input::all();

        $file = ProjectDocumentFile::find($data['id']);

        if( Request::ajax() && $file )
        {
            $messages = array();
            $success  = true;

            // validation rules
            $rules = array(
                'filename' => 'required|max:200'
            );

            $validator = Validator::make($data, $rules);

            if( $validator->fails() )
            {
                $success  = false;
                $messages = $validator->messages();
            }
            else
            {
                $file->filename    = $data['filename'];
                $file->description = $data['description'];

                if( array_key_exists('revision_to', $data) && $data['revision_to'] > 0 && $revisedFile = ProjectDocumentFile::find($data['revision_to']) )
                {
                    $file->setAsNewRevisionToFile($revisedFile);
                }
                else
                {
                    $file->detachFromCurrentRevision();
                }

                $file->save();
            }

            return Response::json(array(
                'success'  => $success,
                'messages' => $messages
            ));
        }

        App::abort(404);
    }

    public function fileRevisions(Project $project, $fileId)
    {
        $file = ProjectDocumentFile::find($fileId);

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        return View::make('document_management_folders.fileRevisionsView', compact('project', 'file'));
    }

    public function fileRevisionList(Project $project, $fileId)
    {
        $file = ProjectDocumentFile::find($fileId);
        $data = array();

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        $projectDocumentFileObj = new ProjectDocumentFile();

        $files = DB::table($projectDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.cabinet_file_id", "f.filename AS document_filename", "f.revision", "f.description", DB::raw("f.created_at AS date_issued"), "users.name AS issued_by", "uploads.filename", "uploads.extension", "uploads.path")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.project_document_folder_id', '=', $file->project_document_folder_id)
            ->where('f.parent_id', '=', $file->parent_id)
            ->orderBy('f.revision')
            ->get();
   
        foreach($files as $file)
        {
            $data[] = array(
                'filename'    => $file->document_filename . '.' . $file->extension,
                'description' => $file->description,
                'revision'    => $file->revision,
                'date_issued' => $project->getProjectTimeZoneTime($file->date_issued)->format(\Config::get('dates.standard')),
                'issued_by'   => $file->issued_by,
                'id'          => $file->id
            );
        }

        return Response::json(compact('data'));
    }

    public function fileDownload(Project $project, $fileId)
    {
        $file        = ProjectDocumentFile::find($fileId);
        $cabinetInfo = $file->fileProperties;

        if( ! $file )
        {
            App::abort(404);
        }

        return \PCK\Helpers\Files::download(
            $cabinetInfo->physicalPath() . $cabinetInfo->filename,
            $file->filename . '.' . $cabinetInfo->extension, array(
            'Content-Type: ' . $cabinetInfo->mimetype,
        ));
    }

    /**
     * Deletes a file by its id.
     *
     * @param $fileId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileDelete(Project $project, $fileId)
    {
        if( Request::ajax() )
        {
            $file = ProjectDocumentFile::find($fileId);

            try
            {
                $file->delete();
            }
            catch(\Symfony\Component\Filesystem\Exception\FileNotFoundException $e)
            {
                // If physical file is non-existant
                // We respond as though we just deleted it.
                return Response::json(array( 'success' => true ));
            }
            catch(Exception $e)
            {
                // Physical file cannot be deleted.
                return Response::json(array( 'success' => false, 'message' => 'File could not be deleted' ));
            }

            return Response::json(array( 'success' => true ));
        }

        App::abort(404);

        return Response::json(array( 'success' => false, 'message' => 'Request Not Allowed' ));
    }

    /**
     * Compares the current start index value and the descendant's lft,
     * returning the lower value.
     *
     * @param $startIndex
     * @param $descendant
     */
    public function getLeftMost($startIndex, $descendant)
    {
        if( $startIndex == null )
        {
            $startIndex = $descendant['lft'];
        }
        else
        {
            if( $startIndex > $descendant['lft'] )
            {
                $startIndex = $descendant['lft'];
            }
        }

        return $startIndex;
    }

    public function sendNotifications($project, $folderId)
    {
        $errors = null;

        try
        {
            $this->emailNotifier->sendDocumentManagementFolderNotifications($folderId);

            $success = true;
        }
        catch(\Exception $e)
        {
            $errors  = $e->getMessage();
            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

}