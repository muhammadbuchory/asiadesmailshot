<?php
namespace App\Http\Controllers\Extended;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportController
{
    public function ExportCampaignssent(Request $request)
    {
      if($request->has('id')){
        $uuid = $request->query('id');
        // DB::enableQueryLog();
        $list = DB::table('mailcoach_campaigns as mcampaign')
        ->select('mcampaign.id', 
        'mcampaign.name', 
        'mcampaign.status', 
        'mcampaign.segment_description', 
        'msends.subscriber_id', 
        'mcontent.subject', 
        'msubscriber.email', 
        'msubscriber.first_name', 
        'msubscriber.last_name', 
        'msubscriber.extra_attributes', 
         DB::raw('COUNT(mclicks.send_id) AS clicks'),
         DB::raw('COUNT(mopens.send_id) AS opens')
         )
        ->leftjoin('mailcoach_content_items as mcontent', 'mcontent.model_id','=', 'mcampaign.id')
        ->leftjoin('mailcoach_sends as msends', 'msends.content_item_id','=', 'mcontent.id')
        ->leftjoin('mailcoach_subscribers as msubscriber', 'msubscriber.id','=', 'msends.subscriber_id')
        ->leftjoin('mailcoach_clicks as mclicks', 'mclicks.send_id','=', 'msends.id')
        ->leftjoin('mailcoach_opens as mopens', 'mopens.send_id','=', 'msends.id')
        ->where('mcampaign.uuid', $uuid)
        ->groupBy('msends.subscriber_id')
        ->orderBy('msubscriber.first_name', 'ASC')
        ->get();
        // dd($list);
        // dd(DB::getQueryLog());
        if($list){
          $data = [[
            'No',
            'Company',
            'Name',
            'Email',
            'Country',
            'Sales',
            'Opens',
            'Click',
          ]];

          $no = 1;
          foreach ($list as $list) {
              if($list->extra_attributes){
                $listdetail = json_decode($list->extra_attributes);
                $person = (isset($listdetail->person) ? $listdetail->person : "-");
                $country = (isset($listdetail->country) ? $listdetail->country : "-");
                $sales = (isset($listdetail->sales) ? $listdetail->sales : "-");
              }else{
                $person = "-";
                $country = "-";
                $sales = "-";
              }
              $data[] = [
                  $no,
                  $list->first_name .' '.$list->last_name,
                  $person,
                  $list->email,
                  $country,
                  $sales,
                  ($list->opens > 0 ? $list->opens : "0"),
                  ($list->clicks > 0 ? $list->clicks : "0"),
              ];
          $no++;
          }

          $filename = $list->name."-Report (".date('d-m-Y').").xlsx";

          return Excel::download(new class($data) implements FromArray, WithStyles {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(Worksheet $sheet)
              {
                  foreach (range('A', 'E') as $columnID) {
                      $sheet->getColumnDimension($columnID)->setAutoSize(true);
                  }
              }

          }, $filename);
        }else{
          exit;
        }
      }
    }

    public function ExportWaCampaignssent(Request $request)
    {
      if($request->has('id')){
        $uuid = $request->query('id');
        // DB::enableQueryLog();
        $list = DB::table('wa_campaigns as wacampaign')
        ->select('wacampaign.id', 
        'wacampaign.name', 
        'wacampaign.status',  
        'waoutbox.phone', 
        'subscriber.first_name', 
        'subscriber.last_name'
         )
        ->leftjoin('wa_outbox as waoutbox', 'waoutbox.wa_campaigns_id','=', 'wacampaign.id')
        ->leftjoin('mailcoach_subscribers as subscriber', 'subscriber.id','=', 'waoutbox.subscriber_id')
        ->where('wacampaign.uuid', $uuid)
        ->orderBy('msubscriber.first_name', 'ASC')
        ->get();
        // dd($list);
        // dd(DB::getQueryLog());
        if($list){
          $data = [[
            'No',
            'Company',
            'Name',
            'Email',
            'Country',
            'Sales',
          ]];

          $no = 1;
          foreach ($list as $list) {
              $data[] = [
                  $no,
                  $list->first_name .' '.$list->last_name,
                  '',
                  $list->email,
                  '',
                  '',
              ];
          $no++;
          }

          $filename = $list->name."-Report (".date('d-m-Y').").xlsx";

          return Excel::download(new class($data) implements FromArray, WithStyles {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function styles(Worksheet $sheet)
              {
                  foreach (range('A', 'E') as $columnID) {
                      $sheet->getColumnDimension($columnID)->setAutoSize(true);
                  }
              }

          }, $filename);
        }else{
          exit;
        }
      }
    }
}