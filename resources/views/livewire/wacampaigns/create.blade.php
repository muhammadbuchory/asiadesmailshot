<form class="form-grid" wire:submit="saveWacampaigns" method="POST">
    @csrf

    @if (count($emailListOptions))
        <x-mailcoach::text-field :label="__mc('Name')" 
        name="name" 
        :placeholder="__mc('Name')"
        wire:model="name" 
        required />

        <x-mailcoach::select-field
            :label="__mc('Email list')"
            :options="$emailListOptions"
            wire:model.lazy="email_list_id"
            name="email_list_id"
            required
        />

        @if(count($templateOptions) > 1)
            <x-mailcoach::select-field
                :label="__mc('Template')"
                :options="$templateOptions"
                wire:model.lazy="template_id"
                position="top"
                name="template_id"
                required
            />
        @endif

        <div class="form-buttons">
            <x-mailcoach::button :label="__mc('Create')" />

            <x-mailcoach::button-tertiary :label="__mc('Cancel')" x-on:click="$dispatch('close-modal', { id: 'create-wacampaings' })" />
        </div>
    @else
    <div class="flex flex-col items-center gap-6">
        <div class="bg-sand-extra-light rounded-full w-16 h-16 flex items-center justify-center">
            <x-heroicon-s-user-group class="w-8 text-sand" />
        </div>
        <div class="text-center">
            <h2 class="text-xl font-medium mb-2">{{ __mc('No lists') }}</h2>
            <p class="">{{ __mc('You need at least one list to collect subscribers and send out wa campaigns.') }}</p>
        </div>
    </div>
    @endif

</form>
