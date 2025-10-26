<?php $modalId = 'viewCidbCodesModal' ?>
<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg fill-horizontal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-eye"></i>
                        {{{ trans('companies.viewCidbCode') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="showtree">
                @if (isset($cidbCodeParents))
                    <ul class="showtree">
                        @foreach ($cidbCodeParents as $cidbCodeParent)
                            <li>
                                <details open>
                                    <summary>{{ $cidbCodeParent->code }}&nbsp;({{ $cidbCodeParent->description }})</summary>
                                    @if ($cidbCodeParent->children)
                                        <ul>
                                            @foreach ($cidbCodeParent->children as $cidbCodeChildren)
                                                <li>
                                                    <details open>
                                                        @if(in_array($cidbCodeChildren->id, $selectedCidbCodeIds))
                                                            <summary style="color:blue">{{ $cidbCodeChildren->code }}&nbsp;({{ $cidbCodeChildren->description }})</summary>
                                                        @else
                                                            <summary>{{ $cidbCodeChildren->code }}&nbsp;({{ $cidbCodeChildren->description }})</summary>
                                                        @endif
                                                            @if ($cidbCodeChildren->subChildren)
                                                                <ul>
                                                                    @foreach ($cidbCodeChildren->subChildren as $cidbCodeSubChildren)
                                                                        <li>
                                                                            @if(in_array($cidbCodeSubChildren->id, $selectedCidbCodeIds))
                                                                            <span style="color:blue">{{ $cidbCodeSubChildren->code }}&nbsp;({{ $cidbCodeSubChildren->description }})</span>
                                                                            @else
                                                                            <span>{{ $cidbCodeSubChildren->code }}&nbsp;({{ $cidbCodeSubChildren->description }})</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                    </details>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif 
                                </details>
                            </li>
                        @endforeach
                    </ul>  
                @endif
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ trans('forms.close') }}</button>

            </div>
        </div>
    </div>
</div>

<style>

    .showtree{
        padding-left: 30px;
        padding-top: 1px;
        padding-bottom: 15px;
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