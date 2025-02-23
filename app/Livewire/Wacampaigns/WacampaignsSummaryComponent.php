<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Wa_campaigns;
use App\Models\Wa_outbox;

class WacampaignsSummaryComponent extends Component
{

    public Wa_campaigns $wacampaigns;
    public Bool $isReady = false; 
    public int $recipient = 0;
    public int $sent = 0;
    public int $failed = 0;
    public $progress;

    protected $listeners = [
        'send-wacampaign' => 'send',
    ];
   
    public function mount($id)
    {
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        $this->wacampaigns = $post;
        $this->recipient = Wa_outbox::where('wa_campaigns_id','=', $post->id)->count();
        $this->sent = Wa_outbox::where('wa_campaigns_id','=', $post->id)
                ->where('status','=','sent')
                ->count();
        $this->failed = Wa_outbox::where('wa_campaigns_id','=', $post->id)
                ->where('status','=','failed')
                ->count();
        $total = (( $this->sent + $this->failed) / $this->recipient) * 100;
        $this->progress = ($total == 100 ? NULL : $total);
        if ($this->wacampaigns->status->getlabel() == 'Draft' || $this->wacampaigns->status->getlabel() == '') {
            return redirect()->route('wacampaigns.settings', $this->wacampaigns);
        }
    }

    public function render()
    {
        return view('livewire.wacampaigns.summary',[
            'campaign' => $this->wacampaigns
        ])->
        layout('livewire.wacampaigns.layouts.campaign', 
            [
                'campaign' => $this->wacampaigns,
                'title' => __mc('Summary'),
            ]
        );
    }
}
