<?php

namespace App\Livewire\Wacampaigns;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Wa_templates;
use App\Models\Wa_campaigns;

class WacampaignsContentComponent extends Component
{
    use WithFileUploads;

    public Wa_templates $watemplates;
    public Wa_campaigns $wacampaigns;
    public ?string $content;
    public ?string $type;
    public $file;

    public function rules(): array
    {
        return [
            'file' => ['max:15000']
        ];
    }

    public function mount($id)
    {

        $post = Wa_campaigns::where('uuid','=',$id)->first();
        if($post){
            // if($post->file == "" || $post->content == ""){
            // $content = Wa_templates::where('id','=', $post->wa_templates_id)->first();
            // $this->watemplates = $content;
            //     if($content){
            //         $this->wacampaigns = $post;
            //         if($post->content == ""){
            //             $this->wacampaigns->content = $content->content;
            //         }
            //         if($post->file == ""){
            //             $this->wacampaigns->file = $content->file;
            //             $this->wacampaigns->type = $content->type;
            //         }
            //         $this->fill($this->wacampaigns->toArray());
            //     }else{
            //         return abort(404);
            //     }
            // }else{
            //     $content = Wa_templates::where('id','=', $post->wa_templates_id)->first();
            //     $this->watemplates = $content;
            //     $this->wacampaigns = $post;
            //     $this->fill($this->wacampaigns->toArray());
            // }
            $this->wacampaigns = $post;
            $this->fill($this->wacampaigns->toArray());

        }else{
            return abort(404);
        }
        
    }

    public function save(): void
    {
        $this->validate();
        if ($this->file) {
            if(!is_string($this->file) && $this->file != $this->wacampaigns->file){
                $this->file->storeAs('public/store', $this->file->hashName());
                $this->wacampaigns->content = $this->content;
                $this->wacampaigns->file = $this->file->hashName();
                $this->wacampaigns->type = $this->file->getMimeType();
                $this->wacampaigns->save();
            }else{
                $this->wacampaigns->content = $this->content;
                // $this->wacampaigns->file = $this->watemplates->file;
                // $this->wacampaigns->type = $this->watemplates->type;
                $this->wacampaigns->save();
            }
        }else{
            $this->wacampaigns->content = $this->content;
            // $this->wacampaigns->file = $this->watemplates->file;
            // $this->wacampaigns->type = $this->watemplates->type;
            $this->wacampaigns->save();
        }

        notify(__mc('Wa campaigns content has been updated.'));
    }

    public function deleteWatemplatesfiles()
    {
        $this->wacampaigns->content = $this->content;
        $this->wacampaigns->file = NULL;
        $this->wacampaigns->type = NULL;
        $this->wacampaigns->save();
        notify(__mc('Wa campaigns content has been updated.'));
    }

    // public function sendTest(): void
    // {
    //     dd("sendtest");

    //     notify(__mc('Wa campaigns content has been updated.'));
    // }

    public function render()
    {
        return view('livewire.wacampaigns.content',[
            "data" => $this->wacampaigns
        ])->
        layout('livewire.wacampaigns.layouts.campaign', 
            [
                'campaign' => $this->wacampaigns,
                'title' => __mc('Content'),
            ]
        );
    }
}
