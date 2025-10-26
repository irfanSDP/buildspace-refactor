@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('contractGroupCategories.contractGroupRoles') }}}</li>
    </ol>
@endsection

@section('content')

{{ Form::open(array('route'=>'contractGroupCategories.match.update')) }}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title">
                <i class="fa fa-link"></i> {{{ trans('contractGroupCategories.contractGroupRoles') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget well">
                <div class="widget-body">
                    <div class="table-responsive" style="overflow:hidden;">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th style="width: 25%;" class="text-right">{{{ trans('contractGroupCategories.contractGroup') }}}</th>
                                <th style="width: auto;" class="text-center">{{{ trans('contractGroupCategories.contractGroupCategories') }}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $order = array(
                                    \PCK\ContractGroups\Types\Role::PROJECT_OWNER,
                                    \PCK\ContractGroups\Types\Role::GROUP_CONTRACT,
                                    \PCK\ContractGroups\Types\Role::PROJECT_MANAGER,
                                    \PCK\ContractGroups\Types\Role::INSTRUCTION_ISSUER,
                                    \PCK\ContractGroups\Types\Role::CLAIM_VERIFIER,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_1,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_2,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_3,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_4,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_5,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_6,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_7,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_8,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_9,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_10,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_11,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_12,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_13,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_14,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_15,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_16,
                                    \PCK\ContractGroups\Types\Role::CONSULTANT_17,
                                    \PCK\ContractGroups\Types\Role::CONTRACTOR,
                            );
                            $groups = \PCK\Helpers\Sorter::sortCollectionWithDefinedOrder($groups, $order, 'group');
                            ?>
                            @foreach ($groups as $group)
                                <tr>
                                    <td class="text-middle text-right">
                                        <div class="{{{ $errors->has('group_names.'.$group->group) ? 'has-error' : null }}}">
                                            <input type="text" value="{{{ Session::get('array_input')['group_names'][$group->group] ?? $group->name }}}" class="form-control text-right bold txt-color-greenDark" name="group_names[{{{$group->group}}}]" placeholder="{{{ \PCK\ContractGroups\ContractGroup::getSystemDefaultGroupName($group->group) }}}"/>
                                        </div>
                                        {{ $errors->first('group_names.'.$group->group, '<em class="invalid pull-right">:message</em>') }}
                                    </td>
                                    <td class="text-middle" style="padding: 25px;">
                                        @if(in_array($group->group, $excludedGroups))
                                            {{{ $group->contractGroupCategories->first()->name }}}
                                        @else
                                            <div class="row">
                                                <div class="col col-lg-12 col-xs-12 col-md-12">
                                                    <fieldset>
                                                        <section>
                                                            <label class="fill-horizontal">
                                                                <select name="group_id[{{{ $group->id }}}][]" class="select2 fill-horizontal" multiple>
                                                                    @foreach($categories as $category)
                                                                        <option value="{{{ $category->id }}}" {{{ $category->includesContractGroups($group->id) ? 'selected' : '' }}} >
                                                                            {{{ $category->name }}}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </label>
                                                        </section>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <footer class="pull-right">
                            {{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary')) }}
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{ Form::close() }}

@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
@endsection