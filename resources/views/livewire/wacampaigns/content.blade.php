@push('endHead')
<style>
    .form-buttons{
        position: relative !important;
    }
</style>
@endpush
<div class="card-grid">
    <x-mailcoach::card>
        <div class="ml-auto flex items-center gap-x-2 pointer-events-auto -mr-1.5">
          <button class="bg-transparent hover:bg-sand-extra-light p-1.5 rounded-full transition-colors duration-300" type="button"
              x-tooltip="'{{ __mc('Save & send test') }}'"
              x-on:click="$wire.call('save'); $dispatch('open-modal', { id: 'send-watest-{{ $data->uuid }}' })"
          >
              <x-icon class="w-5 h-5" name="heroicon-o-paper-airplane" />
          </button>
        </div>
        <div class="flex gap-4">
            <div class="w-1/2">
              <form
                      class="form-grid mt-6"
                      wire:submit="save"
                      @keydown.prevent.window.cmd.s="$wire.call('save')"
                      @keydown.prevent.window.ctrl.s="$wire.call('save')"
                      method="POST"
                  >
                      @csrf
                      @method('PUT')


                          {{-- <x-mailcoach::text-field :label="__mc('Name Template')" name="name" wire:model="name" disabled /> --}}
                          {{-- <x-mailcoach::text-area-editor :label="__mc('Content')" name="content" wire:model="content" required /> --}}
                          <div class="form-field">
                          <label class="label" for="content">
                                  Content
                              </label>
                              <textarea name="content" id="content"  class="input" rows="10" spellcheck="false" wire:model="content" required></textarea>
                          </div>

                          <div class="form-field">
                              <label class="fw-bold">File</label>
                              <input type="file" class="input" wire:model="file">
                              @error('file')
                                <p class="form-error" role="alert">
                                  {{ $message }}
                                </p>
                              @enderror
                              <div wire:loading wire:target="file">
                                <span class="ml-1 text-gray-700">Uploading...</span>
                              </div>
                          </div>

                          <x-mailcoach::form-buttons>
                              <x-mailcoach::button :label="__mc('Save Wa Content')" />
                          </x-mailcoach::form-buttons>
              </form>
            </div>    
            <div class="w-1/2">
              <h1 class="mt-6 mb-4 font-medium">Preview </h1>
              @if ($data->file)
                @if (strstr($data->type, "video/"))
                  <video class="w-1/2" src="{{ asset('storage/store/' . $data->file) }}" controls></video>
                @else
                  <img class="w-1/2" src="{{ asset('storage/store/' . $data->file) }}" alt="Gambar">  
                @endif
              @endif
                <p class="w-1/2 mt-3 break-words whitespace-pre-wrap">{{ $data->content }}</p>
            </div>
        </div>
        
        <x-mailcoach::modal :title="__mc('Send Test')" name="send-watest-{{ $data->uuid }}" :dismissable="true">
          {{-- @include('livewire.wacampaigns.partials.test') --}}
          <livewire:Wacampaigns.WacampaignsSendtestComponent :model="$data"/>
        </x-mailcoach::modal>
    
    </x-mailcoach::card>
</div>


