<?php

namespace App\Livewire\Wasenders;

use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Wa_senders;

class WasendersEditComponent extends Component
{
    public Wa_senders $wasenders;
    public ?string $name;
    public ?string $token;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'token' => ['required', 'string'],
        ];
    }

    public function mount($id)
    {

        $post = Wa_senders::where('uuid','=',$id)->first();
        if($post){
            $this->wasenders = $post;
            $this->fill($this->wasenders->toArray());
        }else{
            return abort(404);
        }
    }

    public function save(): void
    {

        $this->validate();
        $this->wasenders->name = $this->name;
        $this->wasenders->token = $this->token;
        $this->wasenders->save();

        notify(__mc('The Wa senders has been updated.'));
        
    }

    public function render()
    {
        return view('livewire.wasenders.edit',[
            "data" => $this->wasenders
        ])->
        layout('mailcoach::app.layouts.settings', 
            [
                'title' => $this->wasenders->name,
                'originTitle' => __mc('Wa Senders'),
                'originHref' => route('wasenders.list'),
            ]
        );
    }
}
