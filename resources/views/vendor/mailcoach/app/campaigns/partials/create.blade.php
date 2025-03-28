<form
    class="form-grid"
    wire:submit="saveCampaign"
    @keydown.prevent.window.cmd.s="$wire.call('saveCampaign')"
    @keydown.prevent.window.ctrl.s="$wire.call('saveCampaign')"
    method="POST"
>
    
    @if (count($emailListOptions))
        <div class="grid grid-cols-2 gap-4">
            <x-mailcoach::text-field
                :label="__mc('Name')"
                wire:model.lazy="name"
                name="name"
                :placeholder="__mc('Newsletter #1')"
                required
            />
            <x-mailcoach::select-field
                :label="__mc('Email list')"
                :options="$emailListOptions"
                wire:model.lazy="email_list_id"
                name="email_list_id"
                required
            />
        </div>

    

        {{-- @if(count($templateOptions) > 1)
            <x-mailcoach::select-field
                :label="__mc('Template')"
                :options="$templateOptions"
                wire:model.lazy="template_id"
                position="top"
                name="template_id"
            />
        @endif --}}

        <h1>Template</h1>
        <div class="list-template grid grid-cols-3 gap-4 overflow-y-auto" style="max-height: 500px">
            @foreach($templateOptions as $key => $val)
              @if($key != 0)
                @php(  $img = Storage::exists('public/template/'.Str::slug($val).'.jpg') 
                ? asset('storage/template/'.Str::slug($val).'.jpg')
                : url('images/notfound.png')  )  
                <div class="is-template h-96 border-2 p-1 cursor-pointer overflow-hidden" data-id="{{ $key }}">
                  <h4 class="mb-2">{{ $val }}</h4>
                  <img class="max-w-full" src="{{$img}}" alt="Mailshot">
                </div>
              @endif
            @endforeach
        </div>

        <input wire:model.lazy="template_id"
        name="template_id"
        type="hidden">

        <div class="flex items-center justify-end gap-x-3">
            <x-mailcoach::button :label="__mc('Create campaign')" />
            <x-mailcoach::button-tertiary :label="__mc('Cancel')" x-on:click="$dispatch('close-modal', { id: 'create-campaign' })" />
        </div>
    @else
        <div class="flex flex-col items-center gap-6">
            <div class="bg-sand-extra-light rounded-full w-16 h-16 flex items-center justify-center">
                <x-heroicon-s-user-group class="w-8 text-sand" />
            </div>
            <div class="text-center">
                <h2 class="text-xl font-medium mb-2">{{ __mc('No lists') }}</h2>
                <p class="">{{ __mc('You need at least one list to collect subscribers and send out campaigns.') }}</p>
            </div>
            <a href="{{ route('mailcoach.emailLists') }}" wire:navigate>
                <x-mailcoach::button-tertiary>
                    {{ __mc('Go to lists') }}
                </x-mailcoach::button-tertiary>
            </a>
        </div>
    @endif
</form>

<script>
// document.addEventListener('DOMContentLoaded', function() {
   

//     const templateElements = document.querySelectorAll('.is-template');
//     const templateIdField = document.querySelector('input[name="template_id"]');

//     console.log("click run")

//     templateElements.forEach(element => {
//         element.addEventListener('click', function() {
//             templateElements.forEach(el => {
//                 el.classList.remove('selected-template');
//                 el.style.borderColor = '';
//             });

//             this.classList.add('selected-template');
//             this.style.borderColor = '#10B981';


//             const selectedTemplateId = this.getAttribute('data-id');
//             templateIdField.value = selectedTemplateId;
//             @this.set('template_id', selectedTemplateId, false);
            
//         });
//     });
// });

function initializeTemplateSelection() {
    const templateElements = document.querySelectorAll('.is-template');
    const templateIdField = document.querySelector('input[name="template_id"]');

    console.log("Template selection initialized");

    templateElements.forEach(element => {
        // Hapus event listener lama jika ada untuk menghindari duplikasi
        element.removeEventListener('click', handleTemplateClick);
        
        // Tambahkan event listener baru
        element.addEventListener('click', handleTemplateClick);
    });

    function handleTemplateClick() {
        templateElements.forEach(el => {
            el.classList.remove('selected-template');
            el.style.borderColor = '';
        });

        this.classList.add('selected-template');
        this.style.borderColor = '#10B981';

        const selectedTemplateId = this.getAttribute('data-id');
        if (templateIdField) {
            templateIdField.value = selectedTemplateId;
        }
        
        // Update Livewire property
        @this.set('template_id', selectedTemplateId, false);
    }
}

// Inisialisasi pertama kali
document.addEventListener('livewire:init', function() {
    initializeTemplateSelection();
});

// Setelah Livewire selesai update DOM
document.addEventListener('livewire:update', function() {
    initializeTemplateSelection();
});

// Setelah navigasi halaman
document.addEventListener('livewire:navigated', function() {
    initializeTemplateSelection();
});
</script>
