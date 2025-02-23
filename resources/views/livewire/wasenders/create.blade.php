<form class="form-grid" wire:submit="saveWasenders" method="POST">
    @csrf

    <x-mailcoach::text-field :label="__mc('Name')" 
    name="name" 
    :placeholder="__mc('Senders')"
    wire:model="name" 
    required />

    <x-mailcoach::text-field :label="__mc('Token')" 
    name="token" 
    :placeholder="__mc('Get Key')"
    wire:model="token" 
    required />

    <div class="form-buttons">
        <x-mailcoach::button :label="__mc('Create')" />

        <x-mailcoach::button-tertiary :label="__mc('Cancel')" x-on:click="$dispatch('close-modal', { id: 'create-wasenders' })" />
    </div>
</form>
