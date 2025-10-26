<?php

/**
 * ProjectStructure form.
 *
 * @package    buildspace
 * @subpackage form
 * @author     1337 developers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectStructureForm extends BaseProjectStructureForm
{

    public function configure()
    {
        unset($this['groups_list'], $this['priority'], $this['root_id'], $this['lft'], $this['rgt'], $this['level'], $this['created_at'], $this['updated_at'], $this['created_by'], $this['updated_by']);
    }

}