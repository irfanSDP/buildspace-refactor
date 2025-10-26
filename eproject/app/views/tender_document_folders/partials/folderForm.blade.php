<div class="row">
    <div class="col col-md-12">
        <section>
            <label class="input state-error col col-md-12">
                {{ Form::text('folder_name', Input::old('folder_name'), array('required' => 'required', 'class' => 'form-control', 'style' => 'width:100%;', 'placeholder' => trans('documentManagementFolders.newFolder') )) }}
            </label>
            <input name="parent_id" type="hidden" value="">
            <input name="folder_type" type="hidden" value="">
            <input name="id" type="hidden" value="">
        </section>
    </div>
</div>