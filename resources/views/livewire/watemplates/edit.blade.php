@push('endHead')
<style>
    .form-buttons{
        position: relative !important;
    }
</style>
@endpush
<div class="card-grid">
    <x-mailcoach::card>
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


                          <x-mailcoach::text-field :label="__mc('Name')" name="name" wire:model="name" required />
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
                              <span class="ml-1 text-xs text-gray-700">
                                File Support<br>
                                Max Size : 15mb<br>
                                Type file : JPG,JPEG,WEBP,GIF,MP4,WEBM
                              </span>
                          </div>

                          <div class="flex items-center gap-3">
                            <x-mailcoach::button class="" :label="__mc('Save Wa Template')"/>
                            <x-mailcoach::confirm-button
                                danger
                                on-confirm="() => $wire.call('deleteWatemplates')"
                            >
                                <span class="button-link text-red hover:text-red-dark">{{ __mc('Delete') }}</span>
                            </x-mailcoach::confirm-button>
                          </div>
                          {{-- <x-mailcoach::form-buttons>
                              <x-mailcoach::button :label="__mc('Save Wa Templates')" />
                          </x-mailcoach::form-buttons> --}}
    
                      
              </form>
            </div>    
            <div class="w-1/2">
            <h1 class="mt-6 mb-4 font-medium">Preview </h1>
            @if ($data->file)
            <div class="relative">
              <button class="absolute z-40 p-2" x-tooltip="{
                content: 'Delete file',
                theme: $store.theme,}"
                x-on:click.prevent="
                    confirmText = @js(__mc('Are you sure?'));
                    confirmLabel = @js(__mc('Confirm'));
                    danger = @js(false);
                    onConfirm = () => $wire.call('deleteWatemplatesfiles');
                    $dispatch('open-modal', { id: 'confirm' });
                ">
                <svg width="22px" height="22px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7M6 7H5M6 7H8M18 7H19M18 7H16M10 11V16M14 11V16M8 7V5C8 3.89543 8.89543 3 10 3H14C15.1046 3 16 3.89543 16 5V7M8 7H16" stroke="#ed5e58" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              @if (strstr($data->type, "video/"))
                <video class="w-1/2" src="{{ asset('storage/store/' . $data->file) }}" controls></video>
              @else
                <img class="w-1/2" src="{{ asset('storage/store/' . $data->file) }}" alt="Gambar">  
              @endif
            </div>
            @endif
              <p class="w-1/2 mt-3 break-words whitespace-pre-wrap">{{ $data->content }}</p>
            </div>    
        </div>
        
    
    </x-mailcoach::card>
</div>


