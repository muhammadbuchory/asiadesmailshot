<?php

namespace App\Livewire\Wacampaigns;

use App\Models\Wa_campaigns;
use App\Models\Wa_templates;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;

class WacampaignsCreateComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use UsesMailcoachModels;

    public string $name = '';
    public int|string|null $email_list_id = null;
    public int|string|null $template_id = null;
    public array $emailListOptions;
    public array $templateOptions;

    public function mount(?EmailList $emailList){
        $this->emailListOptions = static::getEmailListClass()::orderBy('name')->get()
            ->mapWithKeys(fn (EmailList $list) => [$list->id => $list->name])
            ->toArray();

        $this->templateOptions = Wa_templates::orderBy('created_at','desc')->get()
        ->mapWithKeys(fn (Wa_templates $watemplates) => [$watemplates->id => $watemplates->name])
        ->prepend('-- None --', 0)
        ->toArray();

        $this->email_list_id = $emailList?->id ?? array_key_first($this->emailListOptions);
        $this->template_id = array_key_first($this->templateOptions);
    }

    public function saveWacampaigns()
    {
        $validated = $this->validate([
            'name' => 'required|string',
            'email_list_id' => 'required',
            'template_id' => 'required|numeric|min:0|not_in:0',

        ]);

        $wacampaigns = Wa_campaigns::make();
        $wacampaigns->name = $validated['name'];
        $wacampaigns->uuid = Str::uuid()->toString();;
        $wacampaigns->email_list_id = $validated['email_list_id'];
        $wacampaigns->wa_templates_id = $validated['template_id'];
        $wacampaigns->senders_class = "all";
        $wacampaigns->segment_class = 'Spatie\Mailcoach\Domain\Audience\Support\Segments\EverySubscriberSegment';
        $wacampaigns->status = "draft";
        $wacampaigns->save();

        // try {
        //     $wacampaigns->sendWelcomeNotification(now()->addDay());

        //     notify(__mc('The user has been created. A mail with login instructions has been sent to :email', ['email' => $wacampaigns->email]));

        // } catch (\Throwable $e) {
        //     report($e);
        //     notifyError(__mc('The user has been created. A mail with setup instructions could not be sent: '.$e->getMessage()));
        // }

        notify(__mc('Campaign :Wa campaign was created.', ['campaign' => $wacampaigns->name]));

        return redirect()->route('wacampaigns.settings', $wacampaigns);
    }

    public function render()
    {
        return view('livewire.wacampaigns.create');
    }
}
