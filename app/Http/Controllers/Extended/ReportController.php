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
    public function Exportsent(Request $request)
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
            'Name',
            'Email',
            'Opens',
            'Click',
          ]];

          $no = 1;
          foreach ($list as $list) {
              $data[] = [
                  $no,
                  $list->first_name .' '.$list->last_name,
                  $list->email,
                  $list->opens,
                  $list->clicks,
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