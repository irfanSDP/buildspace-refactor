@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tenderDocument.index', trans('tenderDocumentFolders.tenderDocuments'), array($project->id)) }}</li>
        <li>{{{ $folder->name }}}</li>
    </ol>
@endsection

@section('content')
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="glyphicon glyphicon-file"></i>
                    {{{ $folder->name }}} ({{ trans('structuredDocuments.structuredDocument') }})
                </h1>
            </div>
        </div>
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well" id="wid-id-0">

            <!-- widget div-->
            <div>
                <!-- widget content -->
                <div class="widget-body">

                    {{ Form::model($document, array('route'=>array('structured_documents.update', $project->id, $folder->id, $document->id), 'method' => 'POST', 'class' => 'form-horizontal')) }}
                        <div class="form-group">
                            <label class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.clauses') }}</label>
                            <div class="col-sm-8 col-md-8 col-lg-8">
                                <a href="{{ route('structured_documents.clauses.edit', array($project->id, $folder->id, $document->id)) }}" class="btn btn-warning fill-horizontal">{{ trans('structuredDocuments.clauses') }}</a>
                            </div>
                            {{ $errors->first('heading', '<em class="required">:message</em>') }}
                        </div>
                        @include('structured_documents.partials.form')
                        <footer class="form-group pull-right">
                            <div class="col-sm-12">
                                <a href="{{ route('structured_documents.print', array($project->id, $folder->id, $document->id)) }}" target="_blank" class="btn btn-success"><i class="fa fa-lg fa-fw fa-print"></i> Print</a>
                                <button class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
                                <a href="{{ route('projects.tenderDocument.index', array($project->id)) }}" class="btn btn-default">{{ trans('forms.back') }}</a>
                            </div>
                        </footer>
                    {{ Form::close() }}
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

    </article>

@endsection