<?php

namespace App\Livewire\Wacampaigns;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\Wa_campaigns;
use Closure;
use Illuminate\Support\Str;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\Mailcoach\Domain\Template\Models\Template;
use Spatie\Mailcoach\Livewire\TableComponent;

class WacampaignsListComponent extends TableComponent
{
    protected function getTableQuery(): Builder
    {
        return Wa_campaigns::query();
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'asc';
    }

    public function getTableGroupingDirection(): ?string
    {
        return $this->getTableSortDirection() ?? 'asc';
    }

    public function getTableGrouping(): ?Group
    {
        return Group::make('status')
            ->getTitleFromRecordUsing(function ($record) {
                return match (true) {
                    $record->status->getLabel() === 'Sending' => __mc('Sending'),
                    // $record->status === CampaignStatus::Draft && $record->scheduled_at => __mc('Scheduled'),
                    $record->status->getLabel() === 'Draft' => __mc('Draft'),
                    $record->status->getLabel() === 'Sent' => __mc('Sent Wa Campaigns'),
                    default => '',
                };
            })
            ->label('');
    }

    // protected function getDefaultTableSortColumn(): ?string
    // {
    //     return 'name';
    // }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->requiresConfirmation()
                ->icon('heroicon-s-trash')
                ->color('danger')
                ->deselectRecordsAfterCompletion()
                ->action(function ($records) {
                    $records->each->delete();
                    notify(__mc('Campaigns successfully deleted.'));
                }),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->sortable()
                ->searchable()
                ->size('base')
                ->extraAttributes(['class' => 'link']),
            TextColumn::make('list')
                ->url(fn ($record) => $record->emailList
                    ? route('mailcoach.emailLists.summary', $record->emailList)
                    : null
                )
                ->view('livewire.wacampaigns.partials.email_list'),
            TextColumn::make('status')
                ->getStateUsing(fn ($record) => match (true) {
                    default => $record->status->getLabel(),
                })
                ->sortable()
                ->searchable()
                ->size('base'),
        ];
    }   

    protected function getTableRecordUrlUsing(): ?Closure
    {
        // return function (Template $record) {
        //     return route('mailcoach.templates.edit', $record);
        // };
        return fn (Wa_campaigns $wacampaigns) => route('wacampaigns.settings', $wacampaigns);
    }

    public function deleteWacampaigns(Wa_campaigns $wacampaigns)
    {
        $wacampaigns->delete();
        notify(__mc('Wa Campaigns has been deleted.'));
    }


    protected function getTableActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('Duplicate')
                    ->action(fn (Wa_campaigns $wacampaigns) => $this->duplicateWacampaigns($wacampaigns))
                    ->icon('heroicon-s-document-duplicate')
                    ->label(__mc('Duplicate')),
                Action::make('Delete')
                    ->action(fn (Wa_campaigns $wacampaigns) => $this->deleteWacampaigns($wacampaigns))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('Delete')
                    ->tooltip(__mc('Delete'))
                    ->modalHeading(__mc('Delete'))
                    ->requiresConfirmation(),
            ]),
            
        ];
    }

    public function duplicateWacampaigns(Wa_campaigns $wacampaigns)
    {
        $wanewcampaigns = Wa_campaigns::make();
        $wanewcampaigns->name = 'Duplicate '.$wacampaigns->name;
        $wanewcampaigns->uuid = Str::uuid()->toString();;
        $wanewcampaigns->email_list_id = $wacampaigns->email_list_id;
        $wanewcampaigns->wa_templates_id = $wacampaigns->wa_templates_id;
        $wanewcampaigns->segment_class = $wacampaigns->segment_class;
        $wanewcampaigns->segment_id = $wacampaigns->segment_id;
        $wanewcampaigns->content = $wacampaigns->content;
        $wanewcampaigns->file = $wacampaigns->file;
        $wanewcampaigns->status = "draft";
        $wanewcampaigns->save();

        notify(__mc('Campaign :Wa campaign was created.', ['Wacampaings' => 'Duplicate '.$wacampaigns->name]));

        return redirect()->route('wacampaigns.list');
    }

    public function getTitle(): string
    {
        return __mc('WA campaigns');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __mc('No Wa Campaigns.');
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-s-document-text';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return __mc('You have not created any campaigns yet.');
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('learn')
                ->url('https://mailcoach.app/resources/learn-mailcoach/features/templates')
                ->label(__mc('Learn more about wa campaigns'))
                ->openUrlInNewTab()
                ->link(),
        ];
    }

    public function getLayoutData(): array
    {
       
        return [
            'hideBreadcrumbs' => true,
            // 'create' => 'template',
            'create' => 'wacampaings',
            'createText' => 'New Wa Campaings',
            'createComponent' => WacampaignsCreateComponent::class,
        ];
    }

}
