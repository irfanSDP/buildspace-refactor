<?php

class ProjectManagementGroupsAssignmentForm extends BaseProjectStructureForm
{
    public function configure()
    {
        unset($this['title'], $this['tender_origin_id'], $this['type'], $this['priority'], $this['bill_refreshed'], $this['root_id'], $this['lft'], $this['rgt'], $this['level'], $this['project_groups_list'], $this['tendering_groups_list'], $this['post_contract_groups_list'], $this['created_at'], $this['updated_at'], $this['created_by'], $this['updated_by']);
    }
}