<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Validation\Rule;
use Carbon\CarbonInterface;
use Livewire\Component;
use App\Models\Wa_campaigns;
use App\Models\Wa_senders;
use App\Models\Wa_outbox;
use Spatie\Mailcoach\Domain\Campaign\Rules\DateTimeFieldRule;
// use Spatie\Mailcoach\Livewire\Campaigns\Forms\CampaignSettingsForm;
// use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
// use Spatie\Mailcoach\Domain\Audience\Models\Subscriber;

class WacampaignsDeliveryComponent extends Component
{
    // use UsesMailcoachModels;
    public Wa_campaigns $wacampaigns;
    public String $senders;
    public String $senders_class;
    public Bool $isReady = false; 

    public ?CarbonInterface $schedule_at_date;
    public array $schedule_at;

    protected $listeners = [
        'send-wacampaign' => 'send',
    ];

    protected function rules(): array
    {
        return [
            'schedule_at' => ['required', new DateTimeFieldRule()],
        ];
    }

   
    public function mount($id)
    {
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        $this->wacampaigns = $post;

        $this->senders_class = $this->wacampaigns->senders_class;

        if($post->senders_id != ""){
            $wasenders = Wa_senders::where('id','=',$post->senders_id)->first();
            $this->senders = $wasenders->name; 
        }else{
            $this->senders = "All";
        }   

        if($this->wacampaigns->senders_class != null){
            $this->isReady = true;
        }

        $this->schedule_at_date = $this->wacampaigns->schedule_at ?? now()->setTimezone(config('mailcoach.timezone') ?? config('app.timezone'))->addHour()->startOfHour();

        $this->schedule_at = [
            'date' => $this->schedule_at_date->format('Y-m-d'),
            'hours' => $this->schedule_at_date->format('H'),
            'minutes' => $this->schedule_at_date->format('i'),
        ];
    }

    public function updatedScheduleAt()
    {
        $this->schedule_at_date = (new DateTimeFieldRule())->parseDateTime($this->schedule_at);
    }

    public function unschedule()
    {
        $this->wacampaigns->schedule_at = NULL;
        $this->wacampaigns->save();

        notify(__mc('Campaign :Wa campaign was unscheduled', ['campaign' => $this->wacampaigns->name]));
    }

    public function schedule()
    {

        // dd($this->schedule_at_date);
        // if (! $this->campaign->isPending()) {
        //     notify(__mc('Campaign :campaign could not be schedule because it has already been sent.', ['campaign' => $this->campaign->name]), 'error');
        //     return;
        // }
        // $this->campaign->scheduleToBeSentAt($this->schedule_at_date->setTimezone(config('mailcoach.timezone') ?? config('app.timezone')));
        
        $this->wacampaigns->schedule_at = $this->schedule_at_date;
        $this->wacampaigns->save();

        notify(__mc('Campaign :Wa campaign is scheduled for delivery.', ['campaign' => $this->wacampaigns->name]));
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
            if($this->wacampaigns->senders_class == 'all'){
                if(isset($list->extra_attributes['sales'])){
                    $wasenders = $wasenders = Wa_senders::where('name','=',$list->extra_attributes['sales'])->first();
                    if($wasenders){
                        $waoutbox = Wa_outbox::make();
                        $waoutbox->wa_campaigns_id = $this->wacampaigns->id;
                        $waoutbox->subscriber_id = $list->id;
                        $waoutbox->senders_id = $wasenders->id;
                        if($list->extra_attributes['phone']){
                            $waoutbox->phone = $list->extra_attributes['phone'];
                            $waoutbox->status = "waiting";
                        }else{
                            $waoutbox->response = "Not a valid phone number";
                            $waoutbox->status = "failed";
                        }
                        $waoutbox->save();
                    }else{
                        $waoutbox = Wa_outbox::make();
                        $waoutbox->wa_campaigns_id = $this->wacampaigns->id;
                        $waoutbox->subscriber_id = $list->id;
                        $waoutbox->senders_id = NULL;
                        if($list->extra_attributes['phone']){
                            $waoutbox->phone = $list->extra_attributes['phone'];
                            $waoutbox->response = "Not a valid sales sanders";
                            $waoutbox->status = "failed";
                        }else{
                            $waoutbox->response = "Not a valid phone number";
                            $waoutbox->status = "failed";
                        }
                        $waoutbox->save();
                    }
                }else{
                    $waoutbox = Wa_outbox::make();
                    $waoutbox->wa_campaigns_id = $this->wacampaigns->id;
                    $waoutbox->subscriber_id = $list->id;
                    $waoutbox->senders_id = NULL;
                    if($list->extra_attributes['phone']){
                        $waoutbox->phone = $list->extra_attributes['phone'];
                        $waoutbox->response = "Not a valid sales sanders";
                        $waoutbox->status = "failed";
                    }else{
                        $waoutbox->response = "Not a valid phone number";
                        $waoutbox->status = "failed";
                    }
                    $waoutbox->save();
                }
            }else{
                $waoutbox = Wa_outbox::make();
                $waoutbox->wa_campaigns_id = $this->wacampaigns->id;
                $waoutbox->subscriber_id = $list->id;
                $waoutbox->senders_id = $this->wacampaigns->senders_id;
                if($list->extra_attributes['phone']){
                    $waoutbox->phone = $list->extra_attributes['phone'];
                    $waoutbox->status = "waiting";
                }else{
                    $waoutbox->response = "Not a valid phone number";
                    $waoutbox->status = "failed";
                }
                $waoutbox->save();
            } 
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
                'title' => __mc('Delivery'),
            ]
        );
    }
}
