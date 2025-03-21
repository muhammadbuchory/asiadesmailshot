<form class="form-grid" wire:submit="saveMailer" method="POST">
    @csrf

    <x-mailcoach::text-field type="name" :label="__mc('Name')" wire:model.lazy="name" name="name" required />

    <x-mailcoach::select-field
        wire:model="transport"
        name="transport"
        :label="__mc('Email service')"
        :options="$transports"
    />

    <x-mailcoach::form-buttons>
        <x-mailcoach::button :label="__mc('Create new mailer')" />

        <button type="button" class="button-link" x-on:click="$dispatch('close-modal', { id: 'create-mailer' })">
            {{ __mc('Cancel') }}
        </button>
    </x-mailcoach::form-buttons>
</form>
