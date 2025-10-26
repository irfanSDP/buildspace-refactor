<?php namespace PCK\SystemEvents;

use PCK\Projects\Project;
use PCK\Projects\ProjectRepository;

class ProjectEvents {

	private $projectRepo;

	public function __construct(ProjectRepository $projectRepo)
	{
		$this->projectRepo = $projectRepo;
	}

	public function updateProjectStatus(Project $project, $status)
	{
		$this->projectRepo->updateProjectStatus($project, $status);
	}

}