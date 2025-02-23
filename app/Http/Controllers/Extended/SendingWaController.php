<?php
namespace App\Http\Controllers\Extended;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ->leftjoin('wa_senders as senders', 'senders.id','=', 'campaigns.senders_id')
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
      if(json_decode($res)->code == "200"){
        $waoutbox->status = "sent";
      }else{
        $waoutbox->status = "failed";
      }
      $waoutbox->send_at = now();
      $waoutbox->save();
      echo "sending outbox_id-".$list->outbox_id,"\n";
      curl_close($ch);
    }

    return "finish";
  }

}