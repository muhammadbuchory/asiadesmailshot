<form class="form-grid" wire:submit="saveWatemplates" method="POST">
    @csrf
    <x-mailcoach::text-field :label="__mc('Name')" 
    name="name" 
    :placeholder="__mc('Wa templates')"
    wire:model="name" 
    required />

    <div class="form-buttons">
        <x-mailcoach::button :label="__mc('Create Wa Templates')" />

        <x-mailcoach::button-tertiary :label="__mc('Cancel')" x-on:click="$dispatch('close-modal', { id: 'create-watemplates' })" />
    </div>
</form>
