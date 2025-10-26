<li>
    {{ $clause->content }}
</li>
<br/>
<ol class="sub-clause">
    @foreach($clause->children as $clause)
        @include('structured_documents.clausePrintLayout', array('clause' => $clause))
    @endforeach
</ol>