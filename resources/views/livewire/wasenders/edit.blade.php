@push('endHead')
<style>
    .form-buttons{
        position: relative !important;
    }
</style>
@endpush
<div class="card-grid">
    <x-mailcoach::card>
       
              <form
                      class="form-grid mt-6"
                      wire:submit="save"
                      @keydown.prevent.window.cmd.s="$wire.call('save')"
                      @keydown.prevent.window.ctrl.s="$wire.call('save')"
                      method="POST"
                  >
                      @csrf
                      @method('PUT')


                          <x-mailcoach::text-field :label="__mc('Name')" name="name" wire:model="name" required />
                          <x-mailcoach::text-field :label="__mc('Token')" name="token" wire:model="token" required />
                          {{-- <x-mailcoach::text-area-editor :label="__mc('Content')" name="content" wire:model="content" required /> --}}
                         
                          <x-mailcoach::form-buttons>
                              <x-mailcoach::button :label="__mc('Save Wa Senders')" />
                          </x-mailcoach::form-buttons>
              </form>
            
        
    
    </x-mailcoach::card>
</div>


