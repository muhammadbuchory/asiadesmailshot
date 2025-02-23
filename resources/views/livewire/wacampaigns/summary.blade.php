
<div class="card-grid" id="wacampaign-summary" wire:poll.5s.keep-alive>
    @if (!is_null($progress))
        @include('livewire.wacampaigns.partials.campaignStatus', [
            'type' => 'help',
            'status' => __mc('is preparing to send to'),
            'sync' => true,
            'cancelable' => false,
            'progress' => $progress,
        ])
    @endif

    {{-- @if ($campaign->isCancelled())
        @include('mailcoach::app.campaigns.partials.campaignStatus', [
            'type' => 'error',
            'status' => __mc('sending is cancelled.') . ' ' . __mc('It was sent to :sendsCount/:sentToNumberOfSubscribers :subscriber of', [
                'sendsCount' => number_format($campaign->sendsCount()),
                'sentToNumberOfSubscribers' => number_format($campaign->sentToNumberOfSubscribers()),
                'subscriber' => __mc_choice('subscriber|subscribers', $campaign->sentToNumberOfSubscribers())
            ]),
            'progress' => $campaign->sentToNumberOfSubscribers()
                ? $campaign->sendsCount() / $campaign->sentToNumberOfSubscribers() * 100
                : null,
            'progressClass' => 'bg-red-700'
        ])
        @endif --}}

    {{-- @if(($campaign->isSending() && $campaign->sentToNumberOfSubscribers()))
        @php($total = $campaign->sentToNumberOfSubscribers() * 2)

        @if ($campaign->isSplitTested() && !$campaign->hasSplitTestWinner() && $campaign->sendsCount() === $campaign->sentToNumberOfSubscribers())
            @php($status = __mc('is waiting to choose a winning split test. Sending to '))
        @else
            @php($status = $campaign->sendsCount() === $campaign->sentToNumberOfSubscribers()
                ? __mc('is finishing up sending to')
                : __mc('is sending to :sentToNumberOfSubscribers :subscriber of', [
                'sentToNumberOfSubscribers' => number_format($campaign->sentToNumberOfSubscribers()),
                'subscriber' => __mc_choice('subscriber|subscribers', $campaign->sentToNumberOfSubscribers())
            ]))
        @endif

        @include('mailcoach::app.campaigns.partials.campaignStatus', [
            'status' => $status,
            'sync' => true,
            'cancelable' => true,
            'progress' => $campaign->sentToNumberOfSubscribers()
                ? (($campaign->contentItems->sum(fn ($contentItem) => $contentItem->sends()->count()) + $campaign->sendsCount()) / $total) * 100
                : null,
        ])
    @endif --}}

    @if($campaign->status->getLabel() == "Sent")
        
            {{-- @include('mailcoach::app.campaigns.partials.campaignStatus', [
                'status' => __mc('is retrying <strong>:sendsCount :sends</strong> to', [
                    'sendsCount' => number_format($pendingCount),
                    'sends' => __mc_choice('send|sends', $pendingCount)
                ]),
                'sync' => true,
                'progress' => $campaign->sendsCount()
                    ? (($campaign->sendsCount() - $pendingCount) / $campaign->sendsCount()) * 100
                    : 0,
            ]) --}}
   

     
        @include('livewire.wacampaigns.partials.campaignStatus', [
            'type' => 'success',
            'status' => __mc_choice('was delivered successfully to :count subscriber of|was delivered successfully to :count subscribers of', $recipient),
        ])

        {{-- @if($failedSendsCount)
            <x-mailcoach::alert type="error" full>
                {{ __mc('Delivery failed for') }} <strong>{{ number_format($failedSendsCount) }}</strong> {{ __mc_choice('subscriber|subscribers', $failedSendsCount) }}.
                <a class="underline" href="{{ route('mailcoach.campaigns.outbox', $campaign) . '?filter[type]=failed' }}">{{ __mc('Check the outbox') }}</a>.
            </x-mailcoach::alert>
        @endif --}}
    @endif

    @include('livewire.wacampaigns.partials.statistics')

</div>
