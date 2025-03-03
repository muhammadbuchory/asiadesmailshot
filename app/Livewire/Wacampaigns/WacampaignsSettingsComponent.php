<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Collection;
use Spatie\Mailcoach\Domain\Audience\Models\EmailList;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use App\Models\Wa_campaigns;
use App\Models\Wa_senders;
// use Spatie\Mailcoach\Livewire\Campaigns\Forms\CampaignSettingsForm;


class WacampaignsSettingsComponent extends Component
{
    use UsesMailcoachModels;
    public Wa_campaigns $wacampaigns;
    // public CampaignSettingsForm $form;
    public Collection $emailLists;
    public Collection $segmentsData;
    public string $segment;
    
    public string $senders_class;
    public int|string|null $senders_id = null;
    public int|string|null $email_list_id = null;
    public int|string|null $segment_id = null;
    public array $sendersOptions;

    public ?string $name;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            // 'senders_id' => ['required','not_in:0'],
        ];
    }

    public function mount($id)
    {
        
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        if($post){
            $this->wacampaigns = $post;
            $this->fill($this->wacampaigns->toArray());
        }else{
            return abort(404);
        }

        if ($post->status->getLabel() != "" && $post->status->getLabel() !="Draft") {
            return $this->redirect(route('wacampaigns.summary', $this->wacampaigns));
        }
        
        $this->emailLists = self::getEmailListClass()::with('segments')->get();
        $this->segmentsData = $this->emailLists->map(fn (EmailList $emailList) => [
            'id' => $emailList->id,
            'name' => $emailList->name,
            'segments' => $emailList->segments()->orderBy('name')->pluck('name', 'id')->toArray(),
            'createSegmentUrl' => route('mailcoach.emailLists.segments', $emailList),
        ]);

        $this->segment = $this->wacampaigns->segment_id == "" ? 'entire_list' : 'segment';

        $this->senders_class = $this->wacampaigns->senders_class;

        $this->sendersOptions = Wa_senders::orderBy('id')->get()
        ->mapWithKeys(fn (Wa_senders $wasenders) => [$wasenders->id => $wasenders->name])
        ->toArray();

        $this->senders_id = ($this->wacampaigns->senders_id != 0 || $this->wacampaigns->senders_id != "" ? $this->wacampaigns->senders_id : array_key_first($this->sendersOptions));

        $this->email_list_id = $this->wacampaigns->email_list_id;
        $this->segment_id = $this->wacampaigns->segment_id;

    }

    public function save(): void
    {
        $this->validate();
        
        if($this->senders_class == "selection"){
            $this->wacampaigns->senders_class = "selection";
            $this->wacampaigns->senders_id = $this->senders_id;
        }else{
            $this->wacampaigns->senders_class = "all";
            $this->wacampaigns->senders_id = NULL;
        }
        $this->wacampaigns->email_list_id = $this->email_list_id;
        if($this->segment == "segment"){
            $this->wacampaigns->segment_id = $this->segment_id;
            $this->wacampaigns->segment_class = 'Spatie\Mailcoach\Domain\Audience\Support\Segments\SubscribersWithTagsSegment';
        }else{
            $this->wacampaigns->segment_id = NULL;
            $this->wacampaigns->segment_class = 'Spatie\Mailcoach\Domain\Audience\Support\Segments\EverySubscriberSegment';
        }
        $this->wacampaigns->save();
        notify(__mc('The Wa campaigns has been updated.'));
    }

    public function render()
    {
        return view('livewire.wacampaigns.settings',[
            'campaign' => $this->wacampaigns
        ])->
        layout('livewire.wacampaigns.layouts.campaign', 
            [
                'campaign' => $this->wacampaigns,
                'title' => __mc('Settings'),
            ]
        );
    }
}
