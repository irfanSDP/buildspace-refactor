<?php $showStatus = $showStatus ?? true ?>
<?php $additionalFields = $additionalFields ?? array() ?>
<?php $project = isset($project) ? $project : null ?>
@if(count($verifierRecords) > 0)
    <div class="verifiers" style="margin-top:12px;margin-bottom:12px;">
        <label class="label">{{{ trans('verifiers.assignedVerifiers') }}}:</label>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="text-middle text-center text-nowrap squeeze" style="width:82px;">{{{ trans('verifiers.sequenceNumber') }}}</th>
                    <th class="text-middle text-left text-nowrap" style="min-width:200px;">{{{ trans('users.name') }}}</th>
                    @foreach($additionalFields as $fieldName => $field)
                        <th class="text-middle text-center text-nowrap squeeze">{{{ $fieldName }}}</th>
                    @endforeach
                    @if($showStatus)
                        <th class="text-middle text-center text-nowrap squeeze" style="width:180px;">{{{ trans('verifiers.status') }}}</th>
                        <th class="text-middle text-center text-nowrap squeeze" style="width:120px;">{{{ trans('verifiers.verifiedAt') }}}</th>
                        <th class="text-middle text-center text-nowrap" style="min-width:240px;">{{{ trans('verifiers.remarks') }}}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                <?php $count = 0; ?>
                @foreach($verifierRecords as $record)
                    <?php
                        if( $record->approved === true )
                        {
                            $iconClass = 'fa-thumbs-up';
                            $colour = 'text-success';
                            $statusText = trans("verifiers.approved");
                        }
                        elseif($record->approved === false)
                        {
                            $iconClass = 'fa-thumbs-down';
                            $colour = 'text-danger';
                            $statusText = trans("verifiers.rejected");
                        }
                        else
                        {
                            $iconClass = 'fa-question';
                            $colour = 'text-warning';
                            $statusText = trans("verifiers.unverified");
                        }
                    ?>
                    <tr>
                        <td class="text-middle text-center text-nowrap squeeze">{{{ ++$count }}}</td>
                        <td class="text-middle text-left text-nowrap">
                            {{{ $record->verifier->name }}}
                            @if($record->substitute)
                                ({{{ trans('verifiers.substitutedBy', array('name' => $record->substitute->name)) }}})
                            @endif
                        </td>
                        @foreach($additionalFields as $fieldName => $field)
                            <td class="text-middle {{{ $styling[$fieldName] ?? 'text-center' }}} text-nowrap">{{{ $record->{$field} }}}</td>
                        @endforeach
                        @if($showStatus)
                            <td class="text-middle text-center text-nowrap squeeze {{{ $colour }}}"><i class="fa {{{ $iconClass }}}"></i> <strong>{{{ $statusText }}}</strong></td>
                            <?php $timestamp = $project ? $project->getProjectTimeZoneTime($record->verified_at) : $record->verified_at; ?>
                            <td class="text-middle text-center text-nowrap squeeze">{{{ $record->verified_at ? \Carbon\Carbon::parse($timestamp)->format(Config::get('dates.created_at')) : '-' }}}</td>
                            <td class="text-middle text-left">{{{ $record->remarks }}}</td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-warning fade in">
        {{{ trans('verifiers.noVerifiers') }}}
    </div>
@endif