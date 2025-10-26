<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tools"></i>&nbsp;&nbsp;Rejected Material
        </h1>
    </div>
    @if(!$show)
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('site-management-site-diary-rejected_material.create',array($project->id,$siteDiaryId))}}">
                <button id="createForm" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i>&nbsp;Add Rejected Material
                </button>
            </a>
        </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Rejected Material</th>
                                    @if(!$show)
                                    <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($rejectedMaterialForms as $record)
                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$record->rejectedMaterial->name}}}
                                        </td>
                                        @if(!$show)
                                        <td>
                                            <a href="{{{ route('site-management-site-diary-rejected_material.edit', 
                                                            array($project->id, $siteDiaryId, $record->id)) }}}" class="btn btn-xs btn-success">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            &nbsp;
                                            <a href="{{{ route('site-management-site-diary-rejected_material.delete', 
                                                    array($project->id,$siteDiaryId, $record->id)) }}}" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>