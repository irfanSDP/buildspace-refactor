@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ trans('vendorManagement.supplierCreditFacilitySettings') }}</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-building"></i> {{ trans('vendorManagement.supplierCreditFacilitySettings') }}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
            <header>
                <h2>{{{ trans('vendorManagement.supplierCreditFacilitySettings') }}}</h2>				
            </header>
            <div>
                <div class="widget-body">
                    <form class="smart-form" action="{{ route('supplier.credit.facility.settings.update') }}" method="POST">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <fieldset>
                            <div class="row">
                                <section>
                                    <div class="row">
                                        <div class="col col-3">
                                            <label class="checkbox">
                                                <input type="checkbox" name="has_attachments" @if($setting->has_attachments) checked="true" @endif>
                                                <i></i>{{ trans('general.allowAttachments') }}</label>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </fieldset>
                        <footer>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection