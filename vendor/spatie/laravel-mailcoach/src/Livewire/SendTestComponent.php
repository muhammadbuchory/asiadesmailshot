<?php

namespace Spatie\Mailcoach\Livewire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Spatie\Mailcoach\Domain\Shared\Models\Sendable;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\ValidationRules\Rules\Delimited;

class SendTestComponent extends Component
{
    use UsesMailcoachModels;

    public Model $model;

    public string $emails = '';

    public string $from_email = '';

    public string $html = '';

    public function mount(Model $model)
    {
        $this->model = $model;
        $this->emails = Auth::guard(config('mailcoach.guard'))->user()->email;
        $this->from_email = Auth::guard(config('mailcoach.guard'))->user()->email;
    }

    public function sendTest()
    {
        $automationMailClass = self::getAutomationMailClass();

        $this->validate([
            'emails' => ['required', (new Delimited('email'))->min(1)->max(10)],
            'from_email' => ['nullable', 'email', Rule::requiredIf($this->model instanceof $automationMailClass)],
        ], [
            'email.required' => __mc('You must specify at least one e-mail address.'),
            'email.email' => __mc('Not all the given e-mails are valid.'),
        ]);

        $emails = array_map('trim', explode(',', $this->emails));

        if ($this->from_email) {
            config()->set('mail.from.address', $this->from_email);
        }

        $contentItem = null;
        if ($this->model instanceof ContentItem) {
            $contentItem = $this->model;
            $this->model = $contentItem->getModel();
        }

        if ($this->model instanceof Sendable) {
            try {
                $this->model->sendTestMail($emails, $contentItem);
            } catch (\Throwable $e) {
                report($e);
                notifyError($e->getMessage());
                $this->dispatch('modal-closed', ['modal' => 'send-test']);

                return;
            }

            if (count($emails) > 1) {
                notify(__mc('A test email was sent to :count addresses.', ['count' => count($emails)]));
            } else {
                notify(__mc('A test email was sent to :email.', ['email' => $emails[0]]));
            }
        } else {
            notifyError(__mc('Model :model does not support sending tests.', ['model' => $this->model::class]));
        }

        $this->dispatch('modal-closed', ['modal' => 'send-test']);
    }

    public function render()
    {
        return view('mailcoach::app.components.sendTest');
    }
}
