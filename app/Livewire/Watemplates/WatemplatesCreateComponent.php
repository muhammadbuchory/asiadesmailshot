<?php

namespace App\Livewire\Watemplates;

use App\Models\Wa_templates;
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

class WatemplatesCreateComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $name = '';

    public function saveWatemplates()
    {
        $validated = $this->validate([
            'name' => 'required|string',
        ]);

        $watemplates = Wa_templates::make();
        $watemplates->name = $validated['name'];
        $watemplates->uuid = Str::uuid()->toString();;
        $watemplates->save();

        // try {
        //     $watemplates->sendWelcomeNotification(now()->addDay());

        //     notify(__mc('The user has been created. A mail with login instructions has been sent to :email', ['email' => $watemplates->email]));

        // } catch (\Throwable $e) {
        //     report($e);
        //     notifyError(__mc('The user has been created. A mail with setup instructions could not be sent: '.$e->getMessage()));
        // }

        return redirect()->route('watemplates.edit', $watemplates);
    }

    public function render()
    {
        return view('livewire.watemplates.create');
    }
}
