<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="input state-error">
                {{ Form::text('folder_name', Input::old('folder_name'), array('required' => 'required', 'class' => 'new-folder-name-input form-control', 'style' => 'width:280px;', 'placeholder' => trans('documentManagementFolders.newFolder') )) }}
            </label>
            <input name="parent_id" type="hidden" value="">
            <input name="id" type="hidden" value="">
        </div>
    </div>
</div>