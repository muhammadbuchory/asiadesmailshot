<?php

namespace App\Livewire\Wasenders;

use App\Models\Wa_senders;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\WelcomeNotification\WelcomeNotification;

class WasendersCreateComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $name = '';
    public string $token = '';

    public function saveWasenders()
    {
        $validated = $this->validate([
            'name' => 'required|string',
            'token' => 'required|string|min:5',
        ]);

        $watemplates = Wa_senders::make();
        $watemplates->name = $validated['name'];
        $watemplates->token = $validated['token'];
        $watemplates->uuid = Str::uuid()->toString();;
        $watemplates->save();

        // try {
        //     $watemplates->sendWelcomeNotification(now()->addDay());

        //     notify(__mc('The user has been created. A mail with login instructions has been sent to :email', ['email' => $watemplates->email]));

        // } catch (\Throwable $e) {
        //     report($e);
        //     notifyError(__mc('The user has been created. A mail with setup instructions could not be sent: '.$e->getMessage()));
        // }

        notify(__mc('The Wa senders has been created.'));
        return redirect()->route('wasenders.list');
    }

    public function render()
    {
        return view('livewire.wasenders.create');
    }
}
