<?php

namespace App\Livewire\Wacampaigns;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use App\Models\Wa_campaigns;
use App\Models\Wa_outbox;
use Closure;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Spatie\Mailcoach\Domain\Template\Models\Template;
use Spatie\Mailcoach\Livewire\TableComponent;

class WacampaignsOutboxComponent extends TableComponent
{

    public Wa_campaigns $wacampaigns;
    public $wacampaigns_id;

    public function mount($id)
    {
        $post = Wa_campaigns::where('uuid','=',$id)->first();
        $this->wacampaigns = $post;
    }

    protected function getTableQuery(): Builder
    {
        return Wa_outbox::query()
                ->select('wa_outbox.*', 'subscribers.first_name', 'subscribers.last_name', 'subscribers.extra_attributes')
                ->leftJoin('mailcoach_subscribers as subscribers', 'subscribers.id', '=', 'wa_outbox.subscriber_id')
                ->where('wa_campaigns_id','=',$this->wacampaigns->id);
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'phone';
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('company')
                ->label(__mc('Company'))
                ->getStateUsing(function ($record) {
                    $company = $record->first_name." ".$record->last_name;
                    return $company;
                })
                ->size('base'),
            TextColumn::make('Person')
                ->label(__mc('Name'))
                ->getStateUsing(function ($record) {
                    if($record->extra_attributes){
                        $listdetail = json_decode($record->extra_attributes);
                        $name = (isset($listdetail->person) ? $listdetail->person : "-");
                    }else{
                        $name = "-";
                    }
                    $company = $record->first_name." ".$record->last_name;
                    return $name;
                })
                ->size('base'),
            TextColumn::make('phone')
                ->sortable()
                ->searchable()
                ->size('base'),
            TextColumn::make('status')
                ->getStateUsing(fn ($record) => match (true) {
                    $record->status === "failed" => __mc('Failed'),
                    $record->status === "sent" => __mc('Sent'),
                    default => __mc('Waiting'),
                })
                ->sortable()
                ->searchable()
                ->size('base'),
            TextColumn::make('response')
                ->label(__mc('Problem'))
                ->sortable()
                ->searchable()
                ->size('base')
                ->getStateUsing(function ($record) {
                    if($record->status == "failed"){
                        $res = json_decode($record->response);
                        if(isset($res->results)){
                            $reason = $res->results->message;
                        }else{
                            $reason = $record->response;
                        }
                    }else{
                        $reason = "";
                    }
                    return Str::limit($reason, 50);
                }),
            TextColumn::make('created_at')
                ->label(__mc('Sent'))
                ->date(config('mailcoach.date_format'), config('mailcoach.timezone'))
                ->sortable()
                ->alignRight(),
        ];
    }

    // protected function getTableRecordUrlUsing(): ?Closure
    // {
    //     // return function (Template $record) {
    //     //     return route('mailcoach.templates.edit', $record);
    //     // };
    //     // return fn (Wa_campaigns $wacampaigns) => route('wacampaigns.settings', $wacampaigns);
    // }

    // public function deleteWacampaings(Wa_campaigns $wacampaigns)
    // {
    //     $wacampaigns->delete();
    //     notify(__mc('Wa Campaigns has been deleted.'));
    // }

    // protected function getTableActions(): array
    // {
    //     return [
    //         // ActionGroup::make([
    //         //     Action::make('Duplicate')
    //         //         ->action(fn (Template $record) => $this->duplicateTemplate($record))
    //         //         ->icon('heroicon-s-document-duplicate')
    //         //         ->label(__mc('Duplicate')),
    //         //     Action::make('Delete')
    //         //         ->action(fn (Wa_campaigns $watemplates) => $this->deleteWatemplate($watemplates))
    //         //         ->requiresConfirmation()
    //         //         ->label(__mc('Delete'))
    //         //         ->icon('heroicon-s-trash')
    //         //         ->color('danger'),
    //         // ]),
    //         Action::make('Delete')
    //                 ->action(fn (Wa_campaigns $wacampaigns) => $this->deleteWacampaigns($wacampaigns))
    //                 ->icon('heroicon-o-trash')
    //                 ->color('danger')
    //                 ->label('')
    //                 ->tooltip(__mc('Delete'))
    //                 ->modalHeading(__mc('Delete'))
    //                 ->requiresConfirmation(),
    //     ];
    // }

    // public function duplicateTemplate(Template $template)
    // {
    //     $this->authorize('create', self::getTemplateClass());

    //     $duplicateTemplate = self::getTemplateClass()::create([
    //         'name' => $template->name.' - '.__mc('copy'),
    //         'html' => $template->html,
    //         'structured_html' => $template->structured_html,
    //     ]);

    //     notify(__mc('Template :template was created.', ['template' => $template->name]));

    //     return redirect()->route('mailcoach.templates.edit', $duplicateTemplate);
    // }

    public function getTitle(): string
    {
        return __mc('WA campaigns outbox');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return __mc('No Wa Campaigns Outbox.');
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-s-document-text';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return __mc('Waiting wa campaigns outbox.');
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


    public function getLayout(): string
    {
        return 'livewire.wacampaigns.layouts.campaign';
    }

    public function getLayoutData(): array
    {
        return [
            'title' => __mc('Outbox'),
            'campaign' => $this->wacampaigns,
        ];
    }

}
