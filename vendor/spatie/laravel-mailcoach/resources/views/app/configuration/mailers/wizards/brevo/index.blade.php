<div>
    <livewire:mailcoach::brevo-configuration :mailer="$mailer" />
    <x-mailcoach::modal name="send-test">
        <livewire:mailcoach::mailer-send-test mailer="{{ $mailer->configName() }}" />
    </x-mailcoach::modal>
</div>
