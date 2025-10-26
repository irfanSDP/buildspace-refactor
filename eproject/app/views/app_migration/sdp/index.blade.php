@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>SDP One Drive Migration</li>
    </ol>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fas fa-file-import"></i> SDP One Drive Migration
        </h1>
    </div>
</div>

<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
    <div class="jarviswidget jarviswidget-sortable">
        <header role="heading">
            <span class="widget-icon"> <i class="fa fa-file-upload"></i> </span>
            <h2>Upload Masterlist File</h2>
        </header>

        <!-- widget div-->
        <div role="content">
            <!-- widget content -->
            <div class="widget-body no-padding">
                {{ Form::open(array('route'=>'app.migration.sdp.masterlist.import', 'class' => 'smart-form', 'method' => 'POST', 'enctype' => "multipart/form-data")) }}
                <fieldset>
                    <div class="row">
                        <section class="col col-3">
                            <label class="label">Masterlist Excel</label>
                            {{ Form::file('file',['id' => 'fileToUpload', 'name'=>'masterlist', 'accept'=>'.xlsx , .xls'])}}
                        </section>
                        <section class="col col-2">
                            <label class="label">Status</label>
                            <label class="fill-horizontal">
                                <select class="select2 fill-horizontal" name="status" id="masterlist_status-select">
                                    <option value="{{{\PCK\VendorRegistration\VendorRegistration::STATUS_SUBMITTED}}}" @if($selectedStatus == \PCK\VendorRegistration\VendorRegistration::STATUS_SUBMITTED) selected @endif>Processing</option>
                                    <option value="{{{\PCK\VendorRegistration\VendorRegistration::STATUS_COMPLETED}}}" @if($selectedStatus == \PCK\VendorRegistration\VendorRegistration::STATUS_COMPLETED) selected @endif>Completed</option>
                                </select>
                            </label>
                        </section>
                        <section class="col col-3">
                            <label class="label">Masterlist Type</label>
                            <label class="fill-horizontal">
                                <select class="select2 fill-horizontal" name="type" id="masterlist_type-select">
                                    <option value="1" @if($selectedType == 1) selected @endif>Supplier</option>
                                    <option value="2" @if($selectedType == 2) selected @endif>Contractor</option>
                                    <option value="3" @if($selectedType == 3) selected @endif>Consultant</option>
                                </select>
                            </label>
                        </section>
                    </div>

                    @if($migrationErrors)
                    <hr class="simple"/>
                    <div class="row">
                        <section class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:80px;" class="text-center">Line Number</th>
                                        <th style="width:auto;">Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($migrationErrors as $lineNo => $error)
                                    <tr>
                                        <td class="text-center">{{$lineNo}}</td>
                                        <td>{{$error}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                    @endif
                </fieldset>

                <footer>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-file-upload" aria-hidden="true"></i> Upload</button>
                </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->

        </div>
        <!-- end widget div -->
    </div>
</article>

@endsection