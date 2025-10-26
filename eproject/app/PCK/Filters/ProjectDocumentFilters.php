<?php namespace PCK\Filters;

use Illuminate\Routing\Route;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\DocumentManagementFolders\ProjectDocumentFile;
use PCK\Base\Upload;
use PCK\Exceptions\InvalidAccessLevelException;

class ProjectDocumentFilters {

    public function folderAccess(Route $route)
    {
        $folder = DocumentManagementFolder::find($route->getParameter('folderId'));

        if( ! $folder->hasAccess(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function folderModify(Route $route)
    {
        $folder = DocumentManagementFolder::find($route->getParameter('folderId'));

        if( ! $folder->isEditor(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function fileAccess(Route $route)
    {
        $file = ProjectDocumentFile::find($route->getParameter('fileId'));

        if( ! $file->folder->hasAccess(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function fileModify(Route $route)
    {
        $file = ProjectDocumentFile::find($route->getParameter('fileId'));

        if( ! $file->folder->isEditor(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

    public function uploadModify(Route $route)
    {
        $file = ProjectDocumentFile::where('cabinet_file_id', '=', $route->getParameter('uploadId'))->first();

        if( ! $file->folder->isEditor(\Confide::user()) ) throw new InvalidAccessLevelException(trans('filter.userPermissionDenied'));
    }

}