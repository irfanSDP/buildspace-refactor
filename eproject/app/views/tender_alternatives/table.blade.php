<table class="table  table-bordered">
    <thead>
        <tr>
            <th colspan="2">{{ trans('tenders.tenderAlternatives') }}</th>
        </tr>
    </thead>
    <tbody>
    <?php $isFirstClause = true; ?>
    @foreach ( $data as $key => $tenderAlternatives )
        <?php
        $p = ($key - 1) % 26;
        $key = intval(($key - $p) / 26);
        $alphabet = chr(65 + $p);
        ?>
        <tr>
            <?php $displayText = $isFirstClause ? 'Base Tender' : 'Tender Alternative ' . $alphabet; ?>
            <td style="width:15%; text-align:center;"><strong>{{{ $displayText }}}</strong></td>
            <td>
                @foreach($tenderAlternatives as $tenderAlternative)
                    <p>{{ $tenderAlternative['description'] }}</p>
                    @include('tender_alternatives.partials.amount', array('currencySymbol' => $currencySymbol, 'amount' => $tenderAlternative['amount'], 'amountInText' => $tenderAlternative['amountInText']))
                    <br />
                @endforeach
            </td>
        </tr>
        <?php $isFirstClause = false; ?>
    @endforeach
    </tbody>
</table>