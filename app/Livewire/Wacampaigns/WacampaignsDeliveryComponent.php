<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Wa_campaigns;
use App\Models\Wa_senders;
use App\Models\Wa_outbox;
// use Spatie\Mailcoach\Livewire\Campaigns\Forms\CampaignSettingsForm;
// use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
// use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class WacampaignsDeliveryComponent extends Component
{
    // use UsesMailcoachModels;
    public Wa_campaigns $wacampaigns;
    public String $senders;
    public Bool $isReady = false; 

    protected $listeners = [
        'send-wacampaign' => 'send',
    ];
   
    public function mount($id)
    {
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        $this->wacampaigns = $post;

        if($post->senders_id != ""){
            $wasenders = Wa_senders::where('id','=',$post->senders_id)->first();
            $this->senders = $wasenders->name; 
        }else{
            $this->senders = "unknown";
        }   

        if($this->wacampaigns->senders_id != null){
            $this->isReady = true;
        }
    }

    public function send()
    {
        if(!$this->senders){
            notifyError(__mc('Complete setting wa campaings'));
            return;
        }

        $subscribersQuery = $this->wacampaigns->baseSubscribersQuery();
        $segment = $this->wacampaigns->getSegment();
        $segment->subscribersQuery($subscribersQuery);
        $list = $subscribersQuery->orderBy('id')->get();

        foreach ($list as $list) {
            $waoutbox = Wa_outbox::make();
            $waoutbox->wa_campaigns_id = $this->wacampaigns->id;
            $waoutbox->subscriber_id = $list->id;
            if($list->extra_attributes['phone']){
                $waoutbox->phone = $list->extra_attributes['phone'];
                $waoutbox->status = "waiting";
            }else{
                $waoutbox->response = "Not a valid phone number";
                $waoutbox->status = "failed";
            }
            $waoutbox->save();
        }

        $this->wacampaigns->status = "sending";
        $this->wacampaigns->save();
        return redirect()->route('wacampaigns.summary', $this->wacampaigns);
    }

    public function render()
    {
        return view('livewire.wacampaigns.delivery',[
            'campaign' => $this->wacampaigns
        ])->
        layout('livewire.wacampaigns.layouts.campaign', 
            [
                'campaign' => $this->wacampaigns,
                'title' => __mc('Devlivery'),
            ]
        );
    }
}
