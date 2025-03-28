<?php

namespace Spatie\Mailcoach\Livewire\Templates;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Campaign\Enums\CampaignStatus;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Spatie\Mailcoach\Domain\Shared\Traits\UsesMailcoachModels;
use Spatie\Mailcoach\Domain\Template\Models\Template as TemplateModel;
use Spatie\Mailcoach\Domain\Template\Support\TemplateRenderer;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMail;
use Spatie\Mailcoach\Mailcoach;
use Barryvdh\Snappy\Facades\SnappyImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class TemplateComponent extends Component
{
    use AuthorizesRequests;
    use UsesMailcoachModels;

    public TemplateModel $template;

    #[Validate('required')]
    public ?string $name;

    #[Validate('required')]
    public ?string $html;

    public function mount(TemplateModel $template)
    {
        $this->authorize('update', $template);

        $this->template = $template;
        $this->name = $template->name;
        $this->html = $template->getHtml();
    }

    public function save()
    {
        $this->validate();

        $this->dispatch('saveContent');

    }

    #[On('editorSaved')]
    public function updateTemplate()
    {
        $this->template->refresh();

        $this->template->name = $this->name;
        $this->template->save();
        
        $this->reRenderEmailsUsingTemplate();

            $name = Str::slug($this->name).'.jpg';
            $fullPath = storage_path('app/public/template/' . $name);

            if (Storage::exists('public/template/'.$name)) {
                Storage::delete('public/template/'.$name);
            }

            $html = $this->html;

            $html = preg_replace_callback(
                '/<img[^>]+src="(https?:\/\/[^"]+)"[^>]*>/i',
                function($matches) {
                    try {
                        $imageUrl = $matches[1];
                        $imageData = Http::timeout(120)->get($imageUrl)->body();
                        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION) ?: 'jpg';
                        return str_replace(
                            $imageUrl, 
                            'data:image/'.$extension.';base64,'.base64_encode($imageData), 
                            $matches[0]
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to fetch image: {$matches[1]} - {$e->getMessage()}");
                        return str_replace($matches[1], public_path('placeholder.jpg'), $matches[0]);
                    }
                },
                $html
            );

            $html = preg_replace_callback(
                '/<link[^>]+href="(https?:\/\/fonts\.googleapis\.com[^"]+)"[^>]*>/i',
                function($matches) {
                    try {
                        $fontUrl = $matches[1];
                        $cssContent = Http::timeout(30)->get($fontUrl)->body();
                        
                        $cssContent = preg_replace_callback(
                            '/url\((https?:\/\/[^)]+)\)/i',
                            function($fontMatches) {
                                try {
                                    $fontData = Http::timeout(30)->get($fontMatches[1])->body();
                                    return 'url(data:font/woff2;base64,'.base64_encode($fontData).')';
                                } catch (\Exception $e) {
                                    Log::error("Failed to fetch font: {$fontMatches[1]}");
                                    return $fontMatches[0]; 
                                }
                            },
                            $cssContent
                        );
                        
                        return '<style>'.$cssContent.'</style>';
                    } catch (\Exception $e) {
                        Log::error("Failed to fetch Google Font: {$matches[1]} - {$e->getMessage()}");
                        return ''; 
                    }
                },
                $html
            );

            SnappyImage::loadHTML($html)
                ->setOption('width', 1280)
                ->save($fullPath);

        notify(__mc('Template :template was updated.', ['template' => $this->template->name]));
    }

    #[On('editorUpdated')]
    public function updatePreviewHtml($uuid, $previewHtml)
    {
        $this->html = $previewHtml;
    }

    private function reRenderEmailsUsingTemplate(): void
    {
        self::getCampaignClass()::query()
            ->where('status', CampaignStatus::Draft)
            ->whereHas('contentItem', fn (Builder $query) => $query->where('template_id', $this->template->id))
            ->each(function (Campaign $campaign) {
                $campaign->contentItems->where('template_id', $this->template->id)->each(function (ContentItem $contentItem) {
                    $contentItem->setHtml($this->renderHtml($contentItem->getTemplateFieldValues()));
                    $contentItem->save();
                });
            });

        self::getTransactionalMailClass()::query()
            ->whereHas('contentItem', fn (Builder $query) => $query->where('template_id', $this->template->id))
            ->each(function (TransactionalMail $mail) {
                $mail->contentItem->setHtml($this->renderHtml($mail->contentItem->getTemplateFieldValues()));
                $mail->contentItem->save();
            });

        self::getAutomationMailClass()::query()
            ->whereHas('contentItem', fn (Builder $query) => $query->where('template_id', $this->template->id))
            ->each(function (AutomationMail $mail) {
                $mail->contentItem->setHtml($this->renderHtml($mail->contentItem->getTemplateFieldValues()));
                $mail->contentItem->save();
            });
    }

    private function renderHtml(array $fieldValues): string
    {
        $templateRenderer = (new TemplateRenderer($this->template->html ?? ''));

        $html = $templateRenderer->render($fieldValues);
        if (containsMjml($html)) {
            $mjml = Mailcoach::getSharedActionClass('initialize_mjml', InitializeMjmlAction::class)->execute();

            $html = $mjml->toHtml($html);
        }

        return $html;
    }

    public function render()
    {
        return view('mailcoach::app.templates.edit')
            ->layout('mailcoach::app.layouts.app', [
                'title' => $this->template->name,
                'originTitle' => __mc('Templates'),
                'originHref' => route('mailcoach.templates'),
            ]);
    }
}
