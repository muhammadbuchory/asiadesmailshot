<?php

namespace App\Livewire\Watemplates;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\Wa_templates;
use Closure;
use Illuminate\Support\Str;
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
        return 'created_at';
    }
    
    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }


    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->size('base')
                ->extraAttributes(['class' => 'link']),
            TextColumn::make('file')
                ->sortable()
                ->size('base')
                ->formatStateUsing(function ($record) {
                  return view('livewire.watemplates.partials.files', ["templates"=>$record]);
                })
                ->html(),
            TextColumn::make('type')
                ->sortable()
                ->size('base'),
            TextColumn::make('created_at')
                ->label(__mc('Created'))
                ->date(config('mailcoach.date_format'), config('mailcoach.timezone'))
                ->sortable()
                ->alignRight(),
        ];
    }

    protected function getTableRecordUrlUsing(): ?Closure
    {
        // return function (Template $record) {
        //     return route('mailcoach.templates.edit', $record);
        // };
        return fn (Wa_templates $watemplates) => route('watemplates.edit', $watemplates);
    }

    public function deleteWatemplates(Wa_templates $watemplates)
    {
        $watemplates->delete();
        notify(__mc('Wa templates has been deleted.'));
    }

    protected function getTableActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('Duplicate')
                    ->action(fn (Wa_templates $watemplates) => $this->duplicateWatemplates($watemplates))
                    ->icon('heroicon-s-document-duplicate')
                    ->label(__mc('Duplicate')),
                Action::make('Delete')
                    ->action(fn (Wa_templates $watemplates) => $this->deleteWatemplates($watemplates))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('Delete')
                    ->modalHeading(__mc('Delete'))
                    ->requiresConfirmation(),
            ]),
        ];
    }

    public function duplicateWatemplates(Wa_templates $watemplates)
    {
        $wanewtemplates = Wa_templates::make();
        $wanewtemplates->name = 'Duplicate '.$watemplates->name;
        $wanewtemplates->uuid = Str::uuid()->toString();
        $wanewtemplates->content = $watemplates->content;
        $wanewtemplates->file = $watemplates->wa_templates_id;
        $wanewtemplates->type = $watemplates->type;
        $wanewtemplates->save();

        notify(__mc('Template :Wa template was created.', ['Watemplates' => 'Duplicate '.$watemplates->name]));

        return redirect()->route('watemplates.list');
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
