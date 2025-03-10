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
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\Mailcoach\Domain\Template\Models\Template;
use Spatie\Mailcoach\Livewire\TableComponent;
use Spatie\Mailcoach\Mailcoach;

class WacampaignsListComponent extends TableComponent
{
    protected function getTableQuery(): Builder
    {
        DB::enableQueryLog();
        $query = Wa_campaigns::query()
        ->select('*')
        ->addSelect(
            DB::raw(Mailcoach::isPostgresqlDatabase()
            ? <<<"SQL"
                CASE
                    WHEN status = 'draft' AND schedule_at IS NULL THEN '2999-01-01'::timestamp + INTERVAL '1 day' * id
                    WHEN schedule_at IS NOT NULL THEN schedule_at
                    WHEN sent_at IS NOT NULL THEN sent_at
                    ELSE updated_at
                END as sent_sort
            SQL
            : <<<"SQL"
                CASE
                    WHEN status = 'draft' AND schedule_at IS NULL THEN CONCAT(999999999, id)
                    WHEN schedule_at IS NOT NULL THEN schedule_at
                    WHEN sent_at IS NOT NULL THEN sent_at
                    ELSE updated_at
                END as 'sent_sort'
            SQL
            )
        );
        // dd($query);

        return $query;
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    public function getTableGroupingDirection(): ?string
    {
        return $this->getTableSortDirection() ?? 'desc';
    }

    public function getTableGrouping(): ?Group
    {
        return Group::make('sent_sort')
            ->getTitleFromRecordUsing(function ($record) {
                return match (true) {
                    $record->status->getLabel() === 'Sending' => __mc('Sending'),
                    $record->status->getLabel() === 'Draft' && ! $record->schedule_at => __mc('Draft'),
                    $record->status->getLabel() === 'Draft' && $record->schedule_at => __mc('Scheduled'),
                    $record->status->getLabel() === 'Sent' => __mc('Sent Wa Campaigns'),
                    default => '',
                };
            })
            ->label('');
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'id';
    }


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
            TextColumn::make('schedule_at')
                ->label(__mc('Scheduled'))
                ->date(config('mailcoach.date_format'), config('mailcoach.timezone'))
                ->sortable()
                ->alignRight(),
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
        $wanewcampaigns->senders_class = $wacampaigns->senders_class;
        $wanewcampaigns->senders_id = $wacampaigns->senders_id;
        $wanewcampaigns->schedule_at = $wacampaigns->schedule_at;
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
