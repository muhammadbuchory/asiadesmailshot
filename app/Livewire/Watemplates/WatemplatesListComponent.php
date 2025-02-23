<?php

namespace App\Livewire\Watemplates;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\Wa_templates;
use Closure;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\Mailcoach\Domain\Template\Models\Template;
use Spatie\Mailcoach\Livewire\TableComponent;

class WatemplatesListComponent extends TableComponent
{
    protected function getTableQuery(): Builder
    {
        return Wa_templates::query();
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'name';
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->size('base')
                ->extraAttributes(['class' => 'link']),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        // return function (Template $record) {
        //     return route('mailcoach.templates.edit', $record);
        // };
        return fn (Wa_templates $watemplates) => route('watemplates.edit', $watemplates);
    }

    public function deleteWatemplate(Wa_templates $watemplates)
    {
        $watemplates->delete();
        notify(__mc('Wa templates has been deleted.'));
    }

    protected function getTableActions(): array
    {
        return [
            // ActionGroup::make([
            //     Action::make('Duplicate')
            //         ->action(fn (Template $record) => $this->duplicateTemplate($record))
            //         ->icon('heroicon-s-document-duplicate')
            //         ->label(__mc('Duplicate')),
            //     Action::make('Delete')
            //         ->action(fn (Wa_templates $watemplates) => $this->deleteWatemplate($watemplates))
            //         ->requiresConfirmation()
            //         ->label(__mc('Delete'))
            //         ->icon('heroicon-s-trash')
            //         ->color('danger'),
            // ]),
            Action::make('Delete')
                    ->action(fn (Wa_templates $watemplates) => $this->deleteWatemplate($watemplates))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->tooltip(__mc('Delete'))
                    ->modalHeading(__mc('Delete'))
                    ->requiresConfirmation(),
        ];
    }

    public function duplicateTemplate(Template $template)
    {
        $this->authorize('create', self::getTemplateClass());

        $duplicateTemplate = self::getTemplateClass()::create([
            'name' => $template->name.' - '.__mc('copy'),
            'html' => $template->html,
            'structured_html' => $template->structured_html,
        ]);

        notify(__mc('Template :template was created.', ['template' => $template->name]));

        return redirect()->route('mailcoach.templates.edit', $duplicateTemplate);
    }

    public function getTitle(): string
    {
        return __mc('WA Templates');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __mc('No Wa Templates.');
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-s-document-text';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return __mc('You have not created any templates yet.');
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('learn')
                ->url('https://mailcoach.app/resources/learn-mailcoach/features/templates')
                ->label(__mc('Learn more about wa templates'))
                ->openUrlInNewTab()
                ->link(),
        ];
    }


    public function getLayoutData(): array
    {
       
        return [
            'hideBreadcrumbs' => true,
            // 'create' => 'template',
            'create' => 'watemplates',
            'createText' => 'New Wa Templates',
            'createComponent' => WatemplatesCreateComponent::class,
        ];
    }

}
