<?php /** @var \Spatie\Mailcoach\Domain\Campaign\Models\Campaign $campaign */ ?>
<x-mailcoach::card class="px-0">
    <div class="grid grid-cols-3 divide-x divide-snow">
        <x-mailcoach::statistic
            :href="route('mailcoach.campaigns.outbox', $campaign)"
            :stat="$recipient"
            :label="__mc('Recipients')"
        />

        <x-mailcoach::statistic
            :href="route('mailcoach.campaigns.unsubscribes', $campaign)"
            :stat="$sent"
            :label="__mc('Sent')"
        />

        <x-mailcoach::statistic
            :href="route('mailcoach.campaigns.unsubscribes', $campaign)"
            :stat="$failed"
            :label="__mc('Failed')"
        />


    </div>
</x-mailcoach::card>
