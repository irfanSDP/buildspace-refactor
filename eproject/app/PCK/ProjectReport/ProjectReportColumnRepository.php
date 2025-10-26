<?php namespace PCK\ProjectReport;

class ProjectReportColumnRepository
{
    public function getColumnSelections()
    {
        $selections = array(
            ProjectReportColumn::COLUMN_CUSTOM                         => trans('projectReport.customColumn'),
            ProjectReportColumn::COLUMN_GROUP                          => trans('projectReport.columnGroup'),
            ProjectReportColumn::COLUMN_SYSTEM_COMPANY_NAME            => trans('companies.name'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_TITLE           => trans('projects.projectTitle'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_STATUS          => trans('projects.projectStatus'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_CONTRACT_SUM    => trans('projects.contractSum'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_TOTAL_WORK_DONE => trans('finance.totalWorkDone'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_BILL_TOTAL      => trans('finance.billTotal'),
            ProjectReportColumn::COLUMN_SYSTEM_PROJECT_VO_TOTAL        => trans('finance.totalVoAmount'),
            ProjectReportColumn::COLUMN_NUMBER                         => trans('projectReport.columnNumber'),
            ProjectReportColumn::COLUMN_DATE                           => trans('projectReport.columnDate'),
            ProjectReportColumn::COLUMN_WORK_CATEGORY                  => trans('projectReport.columnWorkCategory'),
            ProjectReportColumn::COLUMN_SUBSIDIARY                     => trans('projectReport.columnSubsidiary'),
            ProjectReportColumn::COLUMN_PROJECT_PROGRESS               => trans('projectReport.columnProjectProgress'),
        );
        asort($selections);
        return $selections;
    }

    public function getProjectProgressSelections()
    {
        return array(
            ProjectReportColumn::PROJECT_PROGRESS_AHEAD => trans('projectReport.projectProgressAhead'),
            ProjectReportColumn::PROJECT_PROGRESS_ONTRACK => trans('projectReport.projectProgressOntrack'),
            ProjectReportColumn::PROJECT_PROGRESS_DELAY => trans('projectReport.projectProgressDelay'),
            ProjectReportColumn::PROJECT_PROGRESS_DELAY_2 => trans('projectReport.projectProgressDelay2'),
            ProjectReportColumn::PROJECT_PROGRESS_COMPLETED => trans('projectReport.projectProgressCompleted'),
        );
    }

    public static function getProjectProgressLabel($projectProgress)
    {
        return ProjectReportColumn::getProjectProgressLabel($projectProgress);
    }

    public function getColumns(ProjectReport $projectReport)
    {
        $rootColumns = $projectReport->columns()->whereNull('parent_id')->orderBy('priority', 'ASC')->get();
        $data        = array();

        foreach($rootColumns as $column)
        {
            $temp = array(
                'id'            => $column->id,
                'title'         => $column->getColumnTitle(),
                'type'          => $column->type,
                'typeLabel'     => $column->getColumnTypeLabel(),
                'singleEntry'   => $column->single_entry,
                'priority'      => $column->priority,
                'depth'         => $column->depth,
                'children'      => $this->getChildColumnsRecursively($column),
            );

            if(!$column->isColumnGroup())
            {
                $temp['content'] = $column->getColumnContent();

                if($column->isCustomColumn())
                {
                    $temp['name'] = ProjectReportColumn::COLUMN_NAME_PREFIX . '[' . $column->id . ']';
                }
            }

            if ($projectReport->isTemplate() && $projectReport->isDraft())
            {
                $temp['route:update'] = route('projectReport.template.column.update', [$projectReport->id, $column->id]);
                $temp['route:delete'] = route('projectReport.template.column.delete', [$projectReport->id, $column->id]);
                $temp['route:swap']   = route('projectReport.template.column.swap', [$projectReport->id, $column->id]);
            } else {
                if ($column->isSingleEntryColumn() && $column->single_entry)
                {
                    if (ProjectReportColumn::getSingleEntryCount($column->reference_id, $projectReport->project_report_type_mapping_id, $projectReport->project_id) > 0)
                    {
                        $temp['doneSingleEntry'] = true;
                    }
                }
            }

            $data[] = $temp;
        }

        return $data;
    }

    public function getChildColumnsRecursively(ProjectReportColumn $column)
    {
        $childColumns = $column->children()->orderBy('priority', 'ASC')->get();
        $data         = [];

        foreach($childColumns as $childColumn)
        {
            $temp = [
                'id'                 => $childColumn->id,
                'title'              => $childColumn->getColumnTitle(),
                'content'            => $childColumn->getColumnContent(),
                'type'               => $childColumn->type,
                'typeLabel'          => $childColumn->getColumnTypeLabel(),
                'singleEntry'        => $childColumn->single_entry,
                'priority'           => $childColumn->priority,
                'depth'              => $childColumn->depth,
                'name'               => ProjectReportColumn::COLUMN_NAME_PREFIX . '[' . $childColumn->id . ']', // for html grouping
                'unique_column_name' => $childColumn->uniqueColumnName, // for dashboard unique field name
                'children'           => $this->getChildColumnsRecursively($childColumn),
                'parent'             => [
                    'id'    => $column->id,
                    'type'  => $column->type,
                ],
            ];

            if($column->projectReport->isTemplate() && $column->projectReport->isDraft())
            {
                $temp['route:update'] = route('projectReport.template.column.update', [$column->projectReport->id, $childColumn->id]);
                $temp['route:delete'] = route('projectReport.template.column.delete', [$column->projectReport->id, $childColumn->id]);
                $temp['route:swap']   = route('projectReport.template.column.swap', [$column->projectReport->id, $childColumn->id]);
            } else {
                if ($childColumn->isSingleEntryColumn() && $childColumn->single_entry)
                {
                    if (ProjectReportColumn::getSingleEntryCount($childColumn->reference_id, $childColumn->projectReport->project_report_type_mapping_id, $childColumn->projectReport->project_id) > 0)
                    {
                        $temp['doneSingleEntry'] = true;
                    }
                }
            }

            $data[] = $temp;
        }

        return $data;
    }

    public function getColumnDefinitions(ProjectReport $projectReport)
    {
        $rootColumns = $projectReport->columns()->whereNull('parent_id')->orderBy('priority', 'ASC')->get();
        $data        = [];

        $identifier = 1;

        foreach($rootColumns as $column)
        {
            $temp = [
                'title' => $column->getColumnTitle(),
                'type'  => $column->type,
                'depth' => $column->depth,
                'identifier' => ProjectReportColumn::generateUniqueIdentifier($identifier),
            ];

            if($column->isColumnGroup())
            {
                $temp['children'] = $this->getColumnDefinitionsRecursively($column, $identifier);
            }

            $data[] = $temp;

            ++$identifier;
        }

        return $data;
    }

    public function getColumnDefinitionsRecursively(ProjectReportColumn $parentColumn, &$identifier)
    {
        $columns = $parentColumn->children()->orderBy('priority', 'ASC')->get();
        $data    = [];

        foreach($columns as $column)
        {
            ++$identifier;

            $temp = [
                'title' => $column->getColumnTitle(),
                'type'  => $column->type,
                'depth' => $column->depth,
                'identifier' => ProjectReportColumn::generateUniqueIdentifier($identifier),
            ];

            if($column->isColumnGroup())
            {
                $temp['children'] = $this->getColumnDefinitionsRecursively($column, $identifier);
            }

            $data[] = $temp;
        }

        return $data;
    }

    public function getDashboardColumnContents(ProjectReport $projectReport, $convertLineBreakToHTML)
    {
        $rootColumns = $projectReport->columns()->whereNull('parent_id')->orderBy('priority', 'ASC')->get();
        $data        = [];

        $identifier = 1;

        foreach($rootColumns as $column)
        {
            if(! $column->isColumnGroup())
            {
                $options = array('number_comma_separated' => true);
                $columnContent = $column->getColumnContent($options);
                $data[ProjectReportColumn::generateUniqueIdentifier($identifier)] = $convertLineBreakToHTML ? nl2br($columnContent) : $columnContent;
            }
            else
            {
                $this->getDashboardColumnContentsRecursively($column, $identifier, $data, $convertLineBreakToHTML);
            }

            ++$identifier;
        }

        return $data;
    }

    public function getDashboardColumnContentsRecursively(ProjectReportColumn $parentColumn, &$identifier, &$data, $convertLineBreakToHTML)
    {
        $columns = $parentColumn->children()->orderBy('priority', 'ASC')->get();

        foreach($columns as $column)
        {
            ++$identifier;

            if (! $column->isColumnGroup())
            {
                $options = array('number_comma_separated' => true);
                $columnContent = $column->getColumnContent($options);
                $data[ProjectReportColumn::generateUniqueIdentifier($identifier)] = $convertLineBreakToHTML ? nl2br($columnContent) : $columnContent;
            }
            else
            {
                $this->getDashboardColumnContentsRecursively($column, $identifier, $data, $convertLineBreakToHTML);
            }
        }
        
        return $identifier;
    }

    public function createNewColumn(ProjectReport $template, $inputs)
    {
        $parentId = isset($inputs['columnId']) ? $inputs['columnId'] : null;

        $column                    = new ProjectReportColumn();
        $column->project_report_id = $template->id;
        $column->title             = trim($inputs['title']) === '' ? null : trim($inputs['title']);
        $column->type              = $inputs['columnType'];
        $column->single_entry      = $inputs['singleEntry'];
        $column->parent_id         = $parentId;
        $column->priority          = ProjectReportColumn::getNextFreePriority($template, $parentId);
        $column->depth             = is_null($parentId) ? 0 : $inputs['depth'] + 1;
        $column->save();

        $column->reference_id = $column->id;
        $column->save();

        return ProjectReportColumn::find($column->id);
    }

    public function updateColumn(ProjectReportColumn $column, $inputs)
    {
        // originally a column group, but type has changed
        if($column->isColumnGroup() && $inputs['columnType'] != ProjectReportColumn::COLUMN_GROUP)
        {
            foreach($column->children as $col)
            {
                $col->delete();
            }
        }

        $column->title = $inputs['title'] === '' ? null : $inputs['title'];
        $column->type  = $inputs['columnType'];
        $column->single_entry  = $inputs['singleEntry'];
        $column->save();

        return ProjectReportColumn::find($column->id);
    }

    public function swap(ProjectReportColumn $draggedColumn, ProjectReportColumn $swappedColumn)
    {
        $draggedColumnPriority = $draggedColumn->priority;
        $swappedColumnPriority = $swappedColumn->priority;

        $draggedColumn->priority = $swappedColumnPriority;
        $draggedColumn->save();

        $swappedColumn->priority = $draggedColumnPriority;
        $swappedColumn->save();
    }

    public function saveColumnContents($columnContents)
    {
        foreach($columnContents as $id => $content) {
            $column = ProjectReportColumn::find($id);
            $column->content = $content;
            $column->save();
        }
    }
}