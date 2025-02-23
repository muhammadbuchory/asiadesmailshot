<?php

namespace App\Livewire\Watemplates;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Wa_templates;

class WatemplatesEditComponent extends Component
{
    use WithFileUploads;

    public Wa_templates $watemplates;
    public ?string $name;
    public ?string $content;
    public ?string $type;
    public $file;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'file' => ['max:15000']
        ];
    }

    public function mount($id)
    {

        $post = Wa_templates::where('uuid','=',$id)->first();
        if($post){
            $this->watemplates = $post;
            $this->fill($this->watemplates->toArray());
        }else{
            return abort(404);
        }
    }

    public function save(): void
    {

        $this->validate();

        if ($this->file) {
            if($this->file != $this->watemplates->file){
                $this->file->storeAs('public/store', $this->file->hashName());
                $this->watemplates->name = $this->name;
                $this->watemplates->content = $this->content;
                $this->watemplates->file = $this->file->hashName();
                $this->watemplates->type = $this->file->getMimeType();
                $this->watemplates->save();
            }else{
                $this->watemplates->name = $this->name;
                $this->watemplates->content = $this->content;
                $this->watemplates->save();
            }
        }else{
            $this->watemplates->name = $this->name;
            $this->watemplates->content = $this->content;
            $this->watemplates->save();
        }

        

        notify(__mc('The Wa template has been updated.'));
    }

    public function render()
    {
        return view('livewire.watemplates.edit',[
            "data" => $this->watemplates
        ])->
        layout('mailcoach::app.layouts.app', 
            [
                'title' => $this->watemplates->name,
                'originTitle' => __mc('Wa Templates'),
                'originHref' => route('watemplates.list'),
            ]
        );
    }
}
