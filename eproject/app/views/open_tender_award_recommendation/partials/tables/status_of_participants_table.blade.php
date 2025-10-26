<div id="status_of_participants_table" style="display:none;">
    <div class="jarviswidget " data-widget-editbutton="false">
        <header>
            <span class="widget-icon" style="font-size:12px;"> <i class="fa fa-users"></i> </span>
            <h2>{{ trans('openTenderAwardRecommendation.statusOfParticipants') }}</h2>
        </header>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th class="text-middle text-center" width="64px;">{{ trans('openTenderAwardRecommendation.rank') }}</th>
                        <th class="text-middle text-left">{{ trans('openTenderAwardRecommendation.tendererName') }}</th>
                        <th class="text-middle text-center" width="160px;">{{ trans('openTenderAwardRecommendation.statusOfParticipant') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $number = 1; ?>
                    @foreach ($participantStatusDetails as $participantStatusDetail)
                        <tr>
                            <td class="text-middle text-center squeeze">{{{ $number++ }}}</td>
                            <td class="text-middle text-left">{{ $participantStatusDetail['participantName'] }}</td>
                            <td class="text-middle text-center">{{{ $participantStatusDetail['commitmentStatus'] }}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>