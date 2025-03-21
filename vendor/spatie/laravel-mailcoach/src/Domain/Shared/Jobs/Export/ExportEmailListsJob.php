<?php

namespace Spatie\Mailcoach\Domain\Shared\Jobs\Export;

use Illuminate\Support\Facades\DB;
use Spatie\Mailcoach\Mailcoach;

class ExportEmailListsJob extends ExportJob
{
    /**
     * @param  array<int>  $selectedEmailLists
     */
    public function __construct(protected string $path, protected array $selectedEmailLists)
    {
    }

    public function name(): string
    {
        return 'Email Lists';
    }

    public function execute(): void
    {
        $prefix = DB::getTablePrefix();

        $emailLists = DB::connection(Mailcoach::getDatabaseConnection())
            ->table(self::getEmailListTableName())
            ->whereIn('id', $this->selectedEmailLists)
            ->get();

        $allowedSubscriptionTags = DB::connection(Mailcoach::getDatabaseConnection())
            ->table('mailcoach_email_list_allow_form_subscription_tags')
            ->select(
                'mailcoach_email_list_allow_form_subscription_tags.*',
                DB::raw($prefix.self::getEmailListTableName().'.uuid as email_list_uuid')
            )
            ->join(self::getEmailListTableName(), self::getEmailListTableName().'.id', '=', 'mailcoach_email_list_allow_form_subscription_tags.email_list_id')
            ->whereIn('email_list_id', $this->selectedEmailLists)
            ->get();

        $this->writeFile('email_lists.csv', $emailLists);
        $this->writeFile('email_list_allow_form_subscription_tags.csv', $allowedSubscriptionTags);
        $this->addMeta('email_lists_count', $emailLists->count());
        $this->addMeta('email_list_allow_form_subscription_tags_count', $allowedSubscriptionTags->count());
    }
}
