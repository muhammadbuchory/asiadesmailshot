
<div class="flex flex-col gap-8">
    <x-mailcoach::card>
        <h2 class="text-xl font-medium">{{ __mc('Checklist') }}</h2>


            @if (!$isReady)
                <x-mailcoach::alert type="error">
                    {{ __mc('You need to check some settings before you can deliver this wa campaign.') }}
                </x-mailcoach::alert>
            @else
                <x-mailcoach::alert type="success">
                    {!! __mc('Campaign <strong>:wa campaign</strong> is ready to be sent.', ['campaign' => $campaign->name]) !!}
                </x-mailcoach::alert>
            @endif

            {{-- @if($campaign->scheduled_at)
                <x-mailcoach::alert type="success">
                {!! __mc('Scheduled for delivery <span class="font-medium">:diff - :scheduledAt</span>.', [
                    'diff' => $campaign->scheduled_at->diffForHumans(),
                    'scheduledAt' => $campaign->scheduled_at->toMailcoachFormat(),
                ]) !!}
                </x-mailcoach::alert>
            @endif --}}

            <section>
                <h3 class="text-lg mb-6">{{ __mc('Settings') }}</h3>
                <table class="w-full">
                    <!-- Email list -->
                    @if ($campaign->email_list_id)
                        @php($subscribersCount = $campaign->segmentSubscriberCount())
                        <x-mailcoach::checklist-item
                            :label="__mc('Sent To')"
                            :test="$subscribersCount > 0"
                        >
                            <x-slot:value>
                                @if($subscribersCount)
                                    {{ $campaign->emailList->name }}
                                    @if($campaign->usesSegment())
                                        ({{ $campaign->getSegment()->description() }})
                                    @endif
                                    <x-mailcoach::tag neutral size="xs" class="ml-2">
                                        {{ $subscribersCount ?? '...' }}
                                        @if (!is_null($subscribersCount))
                                            <span class="ml-1 font-normal">
                                                {{ __mc_choice('subscriber|subscribers', $subscribersCount) }}
                                            </span>
                                        @endif
                                    </x-mailcoach::tag>
                                @else
                                    {{ __mc('Selected list has no subscribers') }}
                                @endif
                            </x-slot:value>
                        </x-mailcoach::checklist-item>
                    @else <!-- No email list -->
                        <x-mailcoach::checklist-item
                            :test="false"
                            :label="__mc('Setting Wa Campaing')"
                            :value="__mc('Check again')"
                        />
                    @endif

                    @if ($senders != "unknown")
                        <x-mailcoach::checklist-item
                            warning
                            :label="__mc('Wa Senders')"
                            :value="$senders"
                        />
                    @else
                        <x-mailcoach::checklist-item
                        warning
                        :test="false"
                        :label="__mc('Wa Senders')"
                        :value="__mc('Check and setup wa senders')"
                    />
                    @endif
                </table>
            </section>
       
    </x-mailcoach::card>


    <x-mailcoach::card>
        <header>
            <h2 class="text-xl font-medium">{{ __mc('Send campaign') }}</h2>
        </header>

        {{-- @if (count($validateErrors = $campaign->validateRequirements()))
            @foreach ($validateErrors as $error)
                <x-mailcoach::alert type="error" full>{!! $error !!}</x-mailcoach::alert>
            @endforeach
        @endif --}}
        <div>
            @if ($isReady)
                <div class="w-full flex flex-col" x-init="schedule = '{{ $campaign->scheduled_at || $errors->first('scheduled_at') ? 'future' : 'now' }}'"
                     x-data="{ schedule: '' }" x-cloak>
                    {{-- @if($campaign->scheduled_at)
                        <x-mailcoach::alert type="success" class="w-full" full>
                            <p class="mb-3">
                                {{ __mc('This campaign is scheduled to be sent at') }}

                                <strong>{{ $campaign->scheduled_at->toMailcoachFormat() }}</strong>.
                            </p>
                        </x-mailcoach::alert>
                        <x-mailcoach::button :label="__mc('Unschedule')" class="mt-4 mr-auto" type="submit" wire:click.prevent="unschedule">
                            <x-slot:icon>
                                <x-heroicon-s-stop class="w-4" />
                            </x-slot:icon>
                        </x-mailcoach::button>
                    @else
                        <div class="grid gap-3 items-start mb-6">
                            <x-mailcoach::radio-field
                                name="schedule"
                                option-value="now"
                                :label="__mc('Send immediately')"
                                x-model="schedule"
                            />
                            <x-mailcoach::radio-field
                                name="schedule"
                                option-value="future"
                                :label="__mc('Schedule for delivery in the future')"
                                x-model="schedule"
                            />
                        </div>

                        <form
                            method="POST"
                            wire:submit="schedule"
                            x-show="schedule === 'future'"
                        >
                            @csrf

                            <x-mailcoach::date-time-field
                                name="scheduled_at"
                                :value="$scheduled_at_date"
                                required
                            />
                            <p class="mt-2 text-xs text-gray-400">
                                {{ __mc('All times in :timezone', ['timezone' => config('mailcoach.timezone') ?? config('app.timezone')]) }}
                            </p>

                            <x-mailcoach::button type="submit" :label="__mc('Schedule delivery')" class="mt-6 button">
                                <x-slot:icon>
                                    <svg class="h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 12"><path fill="#fff" d="M.007 12 14 6 .007 0 0 4.667 10 6 0 7.333.007 12Z"/></svg>
                                </x-slot:icon>
                            </x-mailcoach::button>

                        </form>
                    @endif --}}

                    <div x-show="schedule === 'now'">
                        <x-mailcoach::button
                            x-on:click="$dispatch('open-modal', { id: 'send-wacampaign' })"
                            :label="__mc('Send now')"
                        >
                            <x-slot:icon>
                                <svg class="h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 12"><path fill="#fff" d="M.007 12 14 6 .007 0 0 4.667 10 6 0 7.333.007 12Z"/></svg>
                            </x-slot:icon>
                        </x-mailcoach::button>
                    </div>
                    <x-mailcoach::modal name="send-wacampaign" :dismissable="true">
                        <div class="grid gap-8 p-6">
                            <p class="text-lg">
                                {{ __mc('Are you sure you want to send this campaign to') }}
                                <strong class="font-semibold">
                                    @if ($subscribersCount = $campaign->segmentSubscriberCount())
                                        {{ number_format($subscribersCount) }}
                                        {{ $subscribersCount === 1 ? __mc('subscriber') : __mc('subscribers') }}
                                    @endif
                                </strong>?
                            </p>

                            <x-mailcoach::button
                                x-on:click.prevent="$dispatch('send-wacampaign')"
                                class="button button-red"
                                :label="__mc('Yes, send now!')"
                            />
                        </div>
                    </x-mailcoach::modal>
                </div>
            @else
                <x-mailcoach::alert type="error">
                    {{ __mc('You need to check some settings before you can deliver this wa campaign.') }}
                </x-mailcoach::alert>
            @endif
        </div>
    </x-mailcoach::card>
</div>
