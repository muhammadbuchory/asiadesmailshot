<form
    class="form-grid"
    wire:submit="saveSubscriber"
    @keydown.prevent.window.cmd.s="$wire.call('saveSubscriber')"
    @keydown.prevent.window.ctrl.s="$wire.call('saveSubscriber')"
    method="POST"
>
    @csrf
    <x-mailcoach::text-field :label="__mc('Email')" wire:model.lazy="email" name="email" type="email" required />
    <x-mailcoach::text-field :label="__mc('First name')" wire:model.lazy="first_name" name="first_name" />
    <x-mailcoach::text-field :label="__mc('Last name')" wire:model.lazy="last_name" name="last_name" />

    <div class="flex items-center gap-x-3">
        <x-mailcoach::button :label="__mc('Add subscriber')" />
        <x-mailcoach::button-tertiary :label="__mc('Cancel')" x-on:click="$dispatch('close-modal', { id: 'create-subscriber' })" />
    </div>
</form>
