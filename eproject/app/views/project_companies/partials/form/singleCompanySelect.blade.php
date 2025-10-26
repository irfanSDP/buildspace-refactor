<div class="row">
    <div class="col col-lg-12 col-xs-12 col-md-12">
        <label class="fill-horizontal" data-id="company-name-label">
            <?php $companyName = array_key_exists($group->id, $selectedCompanies) ? $selectedCompanies[$group->id]['name'] : ''; ?>
            <?php $companyId = array_key_exists($group->id, $selectedCompanies) ? $selectedCompanies[$group->id]['id'] : 0; ?>
            <span data-id="group-{{ $group->id }}-company-name">{{ $companyName }}</span>
            {{ Form::hidden("group_id[{$group->id}]", $companyId) }}
        </label>
    </div>
</div>