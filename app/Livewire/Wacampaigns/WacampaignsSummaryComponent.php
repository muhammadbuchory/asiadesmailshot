<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Wa_campaigns;
use App\Models\Wa_outbox;

class WacampaignsSummaryComponent extends Component
{

    use AuthorizesRequests;

    public Wa_campaigns $wacampaigns;
    // public Bool $isReady = false; 
    // public int $recipient = 0;
    // public int $sent = 0;
    // public int $failed = 0;
    // public $progress;

    protected $listeners = [
        'send-wacampaign' => 'send',
    ];
   
    public function mount($id)
    {   
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        $this->wacampaigns = $post;

        if ($this->wacampaigns->status->getlabel() == 'Draft' || $this->wacampaigns->status->getlabel() == '') {
            return redirect()->route('wacampaigns.settings', $this->wacampaigns);
        }

        
        $this->setLoad($post);
    }

    public function setLoad($post){
        $recipient = Wa_outbox::where('wa_campaigns_id','=', $post->id)->count();
        $sent = Wa_outbox::where('wa_campaigns_id','=', $post->id)
        ->where('status','=','sent')
        ->count();
        $failed = Wa_outbox::where('wa_campaigns_id','=', $post->id)
        ->where('status','=','failed')
        ->count();
        $total = (( $sent + $failed) / $recipient) * 100;
        $progress = ($total == 100 ? NULL : $total);
        
        $data = [
            "recipient" => $recipient,
            "sent" => $sent,
            "failed" => $failed,
            "progress" => $progress,
        ];

        return json_encode($data,JSON_FORCE_OBJECT);;
    }

    public function render()
    {
        return view('livewire.wacampaigns.summary',[
            'campaign' => $this->wacampaigns,
            'load' => json_decode($this->setLoad($this->wacampaigns)),
        ])->
        layout('livewire.wacampaigns.layouts.campaign', 
            [
                'campaign' => $this->wacampaigns,
                'title' => __mc('Summary'),
            ]
        );
    }
}
