<form
    class="card-grid"
    method="POST"
    wire:submit="save"
    @keydown.prevent.window.cmd.s="$wire.call('save')"
    @keydown.prevent.window.ctrl.s="$wire.call('save')"
>
    @csrf

    <x-mailcoach::fieldset card :legend="__mc('Campaign')" :description="__mc('General information about your campaign')">
        <x-mailcoach::text-field :label="__mc('Name')" name="name" wire:model="name" required :disabled="true" />
    </x-mailcoach::fieldset>

    @if ($campaign->status->getLabel() == "" || $campaign->status->getLabel() == "Draft")
        @include('mailcoach::app.campaigns.partials.emailListFields', ['segmentable' => $campaign])
    @else
        <x-mailcoach::fieldset card :legend="__mc('Audience')">
            <div>
                @if($campaign->emailList)
                    Sent to list <a href="{{ route('mailcoach.emailLists.subscribers', $campaign->emailList) }}"><strong>{{ $campaign->emailList->name }}</strong></a>
                @else
                    Sent to list <strong>{{ __mc('audience') }}</strong>
                @endif

            @if($campaign->tagSegment)
                , used segment <strong>{{ $campaign->tagSegment->name }}</strong>
            @endif
            </div>
        </x-mailcoach::fieldset>
    @endif

    <x-mailcoach::fieldset card :legend="__mc('Wa Sender')">

    <div class="grid gap-3 items-start">
      <x-mailcoach::radio-field
        name="senders"
        option-value="all"
        wire:model.live="senders_class"
        :label="__mc('All Sales')"
      />
      <x-mailcoach::radio-field
        name="senders"
        option-value="selection"
        wire:model.live="senders_class"
        :label="__mc('Selection')"
      />

      @if($senders_class == "selection" && count($sendersOptions) > 0)
        <x-mailcoach::select-field
            :label="__mc('senders')"
            :options="$sendersOptions"
            wire:model.live="senders_id"
            position="bottom"
            name="senders_id"
            required
        />
      @endif


    </div>
    </x-mailcoach::fieldset>



    <x-mailcoach::card class="flex items-center gap-6" buttons>
        <x-mailcoach::button :label="__mc('Save settings')" />
        {{-- @if ($form->dirty)
            <x-mailcoach::alert class="text-xs sm:text-base" type="info">{{ __mc('You have unsaved changes') }}</x-mailcoach::alert>
        @else
            <div wire:key="dirty" wire:dirty>
                <x-mailcoach::alert class="text-xs sm:text-base" type="info">{{ __mc('You have unsaved changes') }}</x-mailcoach::alert>
            </div>
        @endif --}}
    </x-mailcoach::card>
</form>
