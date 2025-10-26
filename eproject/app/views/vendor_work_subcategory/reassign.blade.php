@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkCategories.index', trans('contractGroupCategories.vendorWorkCategories'), array()) }}</li>
        <li>{{ link_to_route('vendorWorkSubcategories.index', $vendorWorkSubcategory->vendorWorkCategory->name, array($vendorWorkSubcategory->vendor_work_category_id)) }}</li>
        <li>{{{ trans('general.reassign') }}} {{{ $vendorWorkSubcategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('contractGroupCategories.vendorWorkSubcategories') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('general.reassign') }}} {{{ $vendorWorkSubcategory->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorWorkSubcategories.reassign.store', $vendorWorkSubcategory->vendor_work_category_id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <div class="col col-lg-3">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('contractGroupCategories.code') }}:</dt>
                                    <dd>{{{ $vendorWorkSubcategory->code }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-9">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('contractGroupCategories.name') }}:</dt>
                                    <dd>{{{ $vendorWorkSubcategory->name }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                        </div>

                        <h5>{{{ trans('contractGroupCategories.vendorWorkCategories') }}}</h5>
                        <hr class="simple"/>

                        <div class="row">
                            <div class="col col-lg-3">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('contractGroupCategories.code') }}:</dt>
                                    <dd>{{{ $vendorWorkSubcategory->vendorWorkCategory->code }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                            <div class="col col-lg-9">
                                <dl class="dl-horizontal no-margin">
                                    <dt>{{ trans('contractGroupCategories.name') }}:</dt>
                                    <dd>{{{ $vendorWorkSubcategory->vendorWorkCategory->name }}}</dd>
                                    <dt>&nbsp;</dt>
                                    <dd>&nbsp;</dd>
                                </dl>
                            </div>
                        </div>

                        <h5>{{{trans('general.reassign')}}} {{{ trans('contractGroupCategories.vendorWorkCategories') }}}</h5>
                        <hr class="simple"/>

                        <div class="row">
                            <section class="col col-6">
                                <label class="label">{{{ trans('contractGroupCategories.vendorWorkCategories') }}}</label>
                                <label class="fill-horizontal">
                                    <select class="select2 fill-horizontal" name="vendor_work_category_id" id="vendor_work_categories-select">
                                        <option value="">{{trans('forms.select')}} {{{ trans('contractGroupCategories.vendorWorkCategories') }}}</option>
                                        @foreach($vendorWorkCategories as $vendorWorkCategory)
                                        <option value="{{$vendorWorkCategory->id}}" @if(Input::old('vendor_work_category_id') == $vendorWorkCategory->id) selected @endif>({{$vendorWorkCategory->code}}) {{$vendorWorkCategory->name}}</option>
                                        @endforeach
                                    </select>
                                    {{ $errors->first('vendor_work_category_id', '<em class="invalid">:message</em>') }}
                                </label>
                            </section>
                        </div>

                        <footer>
                            {{ Form::hidden('id', $vendorWorkSubcategory->id) }}
                            {{ link_to_route('vendorWorkSubcategories.index', trans('forms.back'), array($vendorWorkSubcategory->vendor_work_category_id), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection