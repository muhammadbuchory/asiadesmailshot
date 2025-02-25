<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Spatie\Mailcoach\Domain\Shared\Models\Sendable;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\ValidationRules\Rules\Delimited;

use App\Models\Wa_senders;

class WacampaignsSendtestComponent extends Component
{
    use UsesMailcoachModels;

    public Model $model;
    public string $phone = '';

    public int|string|null $senders_id = null;
    public array $sendersOptions;

    public function mount(Model $model)
    {
        $this->model = $model;

        $this->sendersOptions = Wa_senders::orderBy('id')->get()
        ->mapWithKeys(fn (Wa_senders $wasenders) => [$wasenders->id => $wasenders->name])
        ->toArray();
        $this->senders_id = array_key_first($this->sendersOptions);

    }

    public function sendTest()
    {

        $wacampaigns = $this->model;

        $phone = array_map('trim', explode(',', $this->phone));
        $senders = Wa_senders::where('id','=',$this->senders_id)->first();

        foreach ($phone as $phone) {
            if($wacampaigns->file != ""){
              $url = "https://notifapi.com/send_image_url";
              $data = array(
                "phone_no" => $phone,
                "key"      => $senders->token,
                "message"  => $wacampaigns->content,
                "url"      => asset('storage/store/' . $wacampaigns->file)
              );
            }else{
              $url = "https://notifapi.com/send_message";
              $data = array(
                "phone_no" => $phone,
                "key"      => $senders->token,
                "message"  => $wacampaigns->content,
              );
            }
      
            $data_string = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 360);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt(
              $ch,
              CURLOPT_HTTPHEADER,
              array(
                      'Content-Type: application/json',
                      'Content-Length: ' . strlen($data_string)
              )
            );
            $res = curl_exec($ch);
            if($res == "phone_no empty"){
              continue;
            }
            if(json_decode($res)->code == "200"){
                notify(__mc('A test wa content was sent to phone number: '. $phone));
            }else{
                notifyError(__mc('data does not support sending phone number: '. $phone));
            }
            curl_close($ch);
          }
        
          $this->dispatch('modal-closed', ['modal' => 'send-watest']);

    }

    public function render()
    {
        return view('livewire.wacampaigns.partials.test');
    }
}
