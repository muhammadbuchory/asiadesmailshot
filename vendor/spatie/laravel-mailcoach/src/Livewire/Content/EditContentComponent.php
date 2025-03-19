<?php

namespace Spatie\Mailcoach\Livewire\Content;

use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\Mailcoach\MainNavigation;
use Illuminate\Support\Facades\DB;

class EditContentComponent extends Component
{
    use AuthorizesRequests;
    use UsesMailcoachModels;

    public Campaign|AutomationMail $model;

    public Collection $contentItems;

    public array $content = [];

    public array $preview = [];

    public ?string $mailer;

    public bool $canBeSplitTested = false;

    public bool $autosaveConflict = false;

    public ?CarbonInterface $lastSavedAt = null;

    protected function rules(): array
    {
        return [
            'content.*.subject' => ['nullable', 'string'],
            'content.*.preview_text' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        /** @var Campaign|AutomationMail $sendable */
        $sendable = Route::current()->parameter('campaign') ?? Route::current()->parameter('automationMail');

        if (is_string($sendable)) {
            $sendable = self::getCampaignClass()::findByUuid($sendable)
                ?? self::getAutomationMailClass()::findByUuid($sendable);
        }

        abort_if(is_null($sendable), 404);

        if ($sendable instanceof Campaign) {
            $this->canBeSplitTested = true;
        }

        $this->model = $sendable;

        $this->authorize('update', $this->model);

        $this->contentItems = $this->model->contentItems;
        $this->content = $this->contentItems->mapWithKeys(function (ContentItem $contentItem) {
            return [
                $contentItem->uuid => [
                    'html' => $contentItem->getHtml(),
                    'structured_html' => $contentItem->getStructuredHtml(),
                    'subject' => $contentItem->subject,
                    'preview_text' => $contentItem->preview_text,
                ],
            ];
        })->toArray();
        $this->preview = $this->contentItems->mapWithKeys(function (ContentItem $contentItem) {
            return [
                $contentItem->uuid => $contentItem->getHtml(),
            ];
        })->toArray();

        $this->lastSavedAt = $this->model->updated_at;

        app(MainNavigation::class)->activeSection()?->add($this->model->name, match (true) {
            $this->model instanceof Campaign => route('mailcoach.campaigns.content', $this->model),
            $this->model instanceof AutomationMail => route('mailcoach.automations.mails.content', $this->model),
            default => '',
        });
    }

    public function addSplitTest(?string $uuid = null): void
    {
        foreach ($this->content as $contentUuid => $item) {
            $this->contentItems->firstWhere('uuid', $contentUuid)?->update([
                'subject' => $item['subject'],
                'preview_text' => (isset($item['preview_text']) ? $item['preview_text'] : "" ),
            ]);
        }

        $this->dispatch('saveContentQuietly');

        $this->contentItems
            ->when($uuid, fn ($contentItems) => $contentItems->where('uuid', $uuid))
            ->last()
            ->replicate(['uuid'])
            ->save();

        notify(__mc('Split test added'));

        $this->redirect(match (true) {
            $this->model instanceof Campaign => route('mailcoach.campaigns.content', $this->model),
            $this->model instanceof AutomationMail => route('mailcoach.automations.mails.content', $this->model),
        }, navigate: true);
    }

    public function deleteSplitTest(ContentItem $contentItem): void
    {
        $contentItem->delete();

        notify(__mc('Split test deleted'));

        $this->redirect(match (true) {
            $this->model instanceof Campaign => route('mailcoach.campaigns.content', $this->model),
            $this->model instanceof AutomationMail => route('mailcoach.automations.mails.content', $this->model),
        }, navigate: true);
    }

    public function save(): void
    {

        $this->validate();

        $this->dispatch('saveContent');

        foreach ($this->content as $uuid => $item) {
            $this->contentItems->firstWhere('uuid', $uuid)->update([
                'subject' => $item['subject'],
                'preview_text' => (isset($item['preview_text']) ? $item['preview_text'] : "" )
            ]);
        }
    }

    public function autosave()
    {

        if ($this->lastSavedAt && $this->lastSavedAt->timestamp !== $this->model->fresh()->updated_at->timestamp) {
            $this->autosaveConflict = true;

            return;
        }

        $this->dispatch('saveContentQuietly');
    }

    #[On('editorSavedQuietly')]
    public function onSavedQuietly()
    {
 
        $this->model->touch();
        $this->lastSavedAt = $this->model->updated_at;
        $this->autosaveConflict = false;
    }

    #[On('editorSaved')]
    public function notifySave(): void
    {

        once(function () {
            notify(__mc(':name was updated.', ['name' => $this->model->fresh()->name]));
        });

        $newcontent = $this->model->contentItems->first();
        $replace = '/<div\s+style="display:none;">.*?<\/div>/';
        $result = preg_replace('/<div\s+style="display:none;">.*?<\/div>/', '', $newcontent->html);

        $newSpan = '
        <div style="display:none;">'.(isset($newcontent->preview_text) ? $newcontent->preview_text : "" ).'</div>';
        $html = preg_replace('/(<body[^>]*>)/', '$1' . $newSpan, $result);
        $send = $newcontent->firstWhere('uuid', $newcontent->uuid)->update([
            'html' => $html
        ]);
    }

    #[On('editorUpdated')]
    public function updatePreviewHtml($uuid, $previewHtml)
    {
        $this->preview[$uuid] = $previewHtml;
    }

    public function render(): View
    {
        $this->mailer = $this->model->getMailerKey();

        $view = $this->model->isEditable()
            ? 'mailcoach::app.content.edit'
            : 'mailcoach::app.content.view';

        $layout = match (true) {
            $this->model instanceof Campaign => 'mailcoach::app.campaigns.layouts.campaign',
            $this->model instanceof AutomationMail => 'mailcoach::app.automations.mails.layouts.automationMail',
        };

        return view($view)->layout($layout, [
            'campaign' => $this->model,
            'mail' => $this->model,
            'title' => __mc('Content'),
        ]);
    }
}
