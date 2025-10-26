<?php namespace PCK\WorkCategories;

class WorkCategoryRepository {

    public function find($id)
    {
        return WorkCategory::find($id);
    }

    public function getAll()
    {
        return WorkCategory::orderBy('id', 'ASC')->get();
    }

    public function store($input)
    {
        $resource = new WorkCategory();

        $resource->name       = trim($input['name']);
        $resource->identifier = trim($input['identifier']);
        $resource->save();

        return $this->find($resource->id);
    }

    public function update($input)
    {
        $resource = WorkCategory::where('id', '=', $input['id'])->first();

        $resource->name       = trim($input['name']);
        $resource->identifier = trim($input['identifier']);
        $resource->save();

        return $this->find($resource->id);
    }

}