<form
    class="form-grid gap-4"
    wire:submit="sendTest"
>
    
        <x-mailcoach::text-field
            :label="__mc('Phone Number')"
            :placeholder="__mc('Phone(s) comma separated')"
            name="phone"
            wire:model="phone"
            :required="true"
            type="text"
        />

        @if(count($sendersOptions) > 0)
            <x-mailcoach::select-field
                :label="__mc('senders')"
                :options="$sendersOptions"
                wire:model.live="senders_id"
                position="bottom"
                name="senders_id"
                required
            />
        @endif

      <x-mailcoach::button :label="__mc('Send test')"/>

</form>