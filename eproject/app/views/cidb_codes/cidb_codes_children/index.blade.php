@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('cidb_codes.index', trans('cidbCodes.cidb_codes') )}}</li>
        @if ($FirstLevel !== NULL)
        <li>{{ link_to_route('cidb_codes_children.index', $FirstLevel->code, [$FirstLevel->id]) }}</li>
        @endif
        <li>{{$name->code}}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
                <i class="fas fa-building"></i> {{ trans('cidbCodes.cidb_codes') }} @if ($FirstLevel !== NULL)
                &nbsp; >&nbsp; &nbsp;{{$FirstLevel->code}}({{$FirstLevel->description}})
                @endif
                &nbsp; >&nbsp; {{$name->code}}({{$name->description}})
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a href="{{ route('cidb_codes_children.create',array($parentId))}}">
                <button class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i> {{{ trans('cidbCodes.add') }}}
                </button>
            </a>
		</div>
	</div>

    <br>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('cidbCodes.cidb_codes_list') }}}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-hover" id="dt_basic">
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th class="hasinput">
                                            <input type="text" class="form-control" placeholder="Filter Code" />
                                        </th>
                                        <th class="hasinput">
                                            <input type="text" class="form-control" placeholder="Filter Description" />
                                        </th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    <tr>
                                        <th>{{{ trans('cidbCodes.no') }}}</th>
                                        <th>{{{ trans('cidbCodes.code') }}}</th>
                                        <th>{{{ trans('cidbCodes.description') }}}</th>
                                        <th>{{{ trans('cidbCodes.action') }}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $count = 0;
                                    ?>
                                    @foreach ($records as $record)
                                        <tr>
                                            <td>
                                                {{{++$count}}}
                                            </td>
                                            <td>
                                                @if($FirstLevel == null)
                                                <a href="{{ route('cidb_codes_children.index', 
                                                           array($record->id))}}">
                                                {{{$record->code}}}
                                                @else
                                                {{{$record->code}}}
                                                @endif
                                                </a>
                                            </td>
                                            <td>
                                                {{{$record->description}}}
                                            </td>
                                            <td>
                                                
                                                <a href="{{{ route('cidb_codes_children.show', array('parentId'=>$parentId , $record->id))}}}"
                                                    class="btn btn-xs btn-success">
                                                        <i class="fa fa-eye"></i> view
                                                </a>
                                                &nbsp;
                                                <a href="{{{ route('cidb_codes_children.edit', array('parentId'=>$parentId , $record->id))}}}"
                                                    class="btn btn-xs btn-warning">
                                                        <i class="fa fa-edit"></i>
                                                </a>
                                                &nbsp;
                                                 <a href="{{{ route('cidb_codes_children.delete', array('parentId'=>$parentId , $record->id)) }}}"
                                                    class="btn btn-xs btn-danger"
                                                    data-method="delete"
                                                    data-csrf_token="{{{ csrf_token() }}}">
                                                    <i class="fa fa-trash"></i>
                                                 </a>
                                             </td>
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
    
    @endsection
    
    @section('js')
    
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var otable = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false
            });

            $("#dt_basic thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            } );
        });
    </script>

    {{-- <script>
        // Get the modal
        var modal = document.getElementById("myModal");
        
        // Get the button that opens the modal
        var btn = document.getElementById("view");
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        
        // When the user clicks on the button, open the modal
        btn.onclick = function() {
          modal.style.display = "block";
        }
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
          modal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        }
    </script> --}}
@endsection

