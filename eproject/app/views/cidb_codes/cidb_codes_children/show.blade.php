@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('cidb_codes.index', trans('cidbCodes.cidb_codes'), []) }}</li>
        @if ($FirstLevel !== NULL)
        <li>{{ link_to_route('cidb_codes_children.index', $FirstLevel->code, [$FirstLevel->id]) }}</li>
        @endif
        <li>{{$name->code}}</li>
        <li>{{$record->code}}</li>
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
            &nbsp; >&nbsp; {{$record->code}}({{$record->description}})
        </h1>
    </div>
</div>

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body no-padding">
                <div class="showtree">
                <ul class="showtree">
                    <li>
                    <details open>
                        <summary>{{$record->code}}&nbsp;({{$record->description}})</summary>
                        @if($children !== null)
                        <ul>
                            @foreach ($children as $child)
                                <li>
                                    {{ $child->code }}&nbsp;({{ $child->description }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    </details>
                    </li>
                </ul>  
                </div>
                <footer class="back">
                    <a href="{{route('cidb_codes_children.index', array($parentId , $name->id))}}}">
                        {{ Form::button(trans('Back'), ['type' => 'button', 'class' => 'btn btn-default', 'name' => 'back'] )  }}
                    </a>
                </footer>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>

<style>
    .showtree{
        padding-left: 35px;
        padding-top: 15px;
        padding-bottom: 20px;
    }

    .showtree ul{
        list-style: none;
        line-height: 2em;
        font-size: 15px;
    }

    .showtree summary{
        cursor: pointer;
    }

    .showtree summary::marker{
        display: none;
    }

    .showtree summary::-webkit-details-marker{
        display: none;
    }

    .showtree ul li{
        position: relative;
    }

    .showtree ul li::before{
        position: absolute;
        left: -10px;
        top: 0px;
        border-left: 2px solid gray;
        border-bottom: 2px solid gray;
        content: "";
        width: 8px;
        height: 1em;
    }

    .showtree ul li::after{
        position: absolute;
        left: -10px;
        bottom: 0px;
        border-left: 2px solid gray;
        content: "";
        width: 8px;
        height: 100%;
    }

    .showtree ul li:last-child::after{
        display: none;
    }

    ul.showtree > li:after, ul.showtree > li:before{
        display: none;
    }

    .showtree ul summary::before{
        position: absolute;
        left: -1.35em;
        top: .50em;
        content: "+";
        background: orange;
        display: block;
        width: 15px;
        height: 15px;
        border-radius: 50em;
        z-index: 999;
        text-align: center;
        line-height: .90em;
    }

    .showtree ul details[open] > summary::before{
        content: "-";
    }

    .back{
        float: right;
        margin-right: 20px;
        margin-bottom: 20px;
    }

</style>
    
@endsection