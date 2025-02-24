<div class="card-grid">
    @include('mailcoach::app.configuration.mailers.wizards.wizardNavigation')

    <x-mailcoach::card>
        <x-mailcoach::alert type="help">
            AWS can be configured to track bounces and complaints. It will send webhooks to Mailcoach, that will be used
            to
            automatically unsubscribe people.<br/><br/>Optionally, AWS can also send webhooks to inform Mailcoach of
            opens and
            clicks.
        </x-mailcoach::alert>

        <x-mailcoach::select-field
                wire:model="configurationType"
                name="configuration"
                :label="__mc('Setup type')"

                :options="['automatic' => 'Automatic', 'manual' => 'Manual']"
        />

        @if($configurationType === 'manual')
            <x-mailcoach::alert type="info">
                {!! __mc('Learn how to configure :provider by reading <a target="_blank" href=":docsLink">this section of the Mailcoach docs</a>.', ['provider' => 'SES', 'docsLink' => 'https://mailcoach.app/resources/learn-mailcoach/getting-started/configuring-mail-providers#content-amazon-ses']) !!}
                <br/>
                {!! __mc('You must set a webhook to: <code class="markup-code">:webhookUrl</code>', ['webhookUrl' => url(action(\Spatie\Mailcoach\Http\Api\Controllers\Vendor\Ses\SesWebhookController::class, $mailer->configName()))]) !!}
            </x-mailcoach::alert>

            <form class="form-grid" wire:submit="setupFeedbackManually">
                <x-mailcoach::text-field
                        wire:model.defer="configurationName"
                        :label="__mc('Configuration name')"
                        name="configurationName"
                        type="text"
                />

                <x-mailcoach::form-buttons>
                    <x-mailcoach::button :label="__mc('Continue')"/>
                </x-mailcoach::form-buttons>
            </form>
        @else
            <x-mailcoach::alert type="info">
                We will automatically set up SES and SNS for you.
            </x-mailcoach::alert>

            <form class="form-grid" wire:submit="setupFeedbackAutomatically">
                <x-mailcoach::text-field
                        wire:model.defer="configurationName"
                        :label="__mc('Configuration name')"
                        name="configurationName"
                        type="text"
                />

                <x-mailcoach::checkbox-field
                        :label="__mc('Enable open tracking')"
                        name="trackOpens"
                        wire:model.defer="trackOpens"
                />

                <x-mailcoach::checkbox-field
                        :label="__mc('Enable click tracking')"
                        name="trackClicks"
                        wire:model.defer="trackClicks"
                />

                <x-mailcoach::form-buttons>
                    <x-mailcoach::button :label="__mc('Automatically configure AWS')"/>
                </x-mailcoach::form-buttons>
            </form>
        @endif
    </x-mailcoach::card>
</div>
