<?php
namespace App\Http\Controllers\Extended;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Wa_campaigns;
use App\Models\Wa_senders;
use App\Models\Wa_outbox;

class SendingWaController
{

  public function WaJobs(){

    $list = DB::table('wa_outbox as outbox')
        ->select('outbox.id as outbox_id', 
        'campaigns.id as campaigns_id', 
        'campaigns.content as new_content', 
        'campaigns.file as new_file',
        'templates.content as old_content',
        'templates.file as old_file',
        'senders.token',
        'outbox.phone',
        )
        ->leftjoin('wa_campaigns as campaigns', 'campaigns.id','=', 'outbox.wa_campaigns_id')
        ->leftjoin('wa_templates as templates', 'templates.id','=', 'campaigns.wa_templates_id')
        ->leftjoin('wa_senders as senders', 'senders.id','=', 'outbox.senders_id')
        ->where('outbox.status', 'waiting')
        ->limit(50)
        ->get();
    foreach ($list as $list) {
      if($list->new_file != "" || $list->old_file != ""){
        $url = "https://notifapi.com/send_image_url";
        $data = array(
          "phone_no" => $list->phone,
          "key"      => $list->token,
          "message"  => ($list->new_content != "" ? $list->new_content : $list->old_content),
          "url"      => asset('storage/store/' . ($list->new_file != "" ? $list->new_file : $list->old_file))
        );
      }else{
        $url = "https://notifapi.com/send_message";
        $data = array(
          "phone_no" => $list->phone,
          "key"      => $list->token,
          "message"  => ($list->new_content != "" ? $list->new_content : $list->old_content)
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
      $waoutbox = Wa_outbox::find($list->outbox_id);
      $waoutbox->response = $res;
      
      if(isset(json_decode($res)->code) &&  json_decode($res)->code == "200"){
        $waoutbox->status = "sent";
      }else{
        $waoutbox->response = $res;
        $waoutbox->status = "failed";
      }
      $waoutbox->send_at = now();
      $waoutbox->save();
      echo "sending outbox_id-".$list->outbox_id,"\n";
      curl_close($ch);
    }

    $campaigns = DB::table('wa_campaigns AS campaigns')
    ->leftJoin(DB::raw('(SELECT wa_campaigns_id, COUNT(*) AS count FROM wa_outbox WHERE status = "waiting" GROUP BY wa_campaigns_id) AS outbox'), 'outbox.wa_campaigns_id', '=', 'campaigns.id')
    ->select('campaigns.id', DB::raw('IFNULL(outbox.count, 0) AS count'))
    ->where('campaigns.status','=', 'sending')
    ->get();

    if(count($campaigns) > 0){
      echo "check wa campaigns \n";
      foreach ($campaigns as $campaigns) {
        Wa_campaigns::where('id', $campaigns->id)
        ->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
      }
      echo "finish wa campaigns \n";
    }
  }

  public function WaSchecuduleJobs(){
    $campaigns = Wa_campaigns::where('status','=','draft')
                ->where('schedule_at','!=', NULL) 
                ->get();
    foreach ($campaigns as $campaigns) {
      if(now() > $campaigns->schedule_at){
        $campaigns->status = "sending";
        $campaigns->save();
        echo "update wa campaings". $campaigns->name." ".now()."\n";

        $subscribersQuery = $campaigns->baseSubscribersQuery();
        $segment = $campaigns->getSegment();
        $segment->subscribersQuery($subscribersQuery);
        $list = $subscribersQuery->orderBy('id')->get();

        foreach ($list as $list) {
          if($campaigns->senders_class == 'all'){
              if(isset($list->extra_attributes['sales'])){
                  $wasenders = $wasenders = Wa_senders::where('name','=',$list->extra_attributes['sales'])->first();
                  if($wasenders){
                      $waoutbox = Wa_outbox::make();
                      $waoutbox->wa_campaigns_id = $campaigns->id;
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
                      $waoutbox->wa_campaigns_id = $campaigns->id;
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
                  $waoutbox->wa_campaigns_id = $campaigns->id;
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
              $waoutbox->wa_campaigns_id = $campaigns->id;
              $waoutbox->subscriber_id = $list->id;
              $waoutbox->senders_id = $campaigns->senders_id;
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

      }
    }
  }

}