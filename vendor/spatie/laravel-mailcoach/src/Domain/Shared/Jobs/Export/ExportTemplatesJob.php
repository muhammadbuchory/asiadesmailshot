<?php

namespace Spatie\Mailcoach\Domain\Shared\Jobs\Export;

use Illuminate\Support\Facades\DB;
use Spatie\Mailcoach\Mailcoach;

class ExportTemplatesJob extends ExportJob
{
    /**
     * @param  array<int>  $selectedTemplates
     */
    public function __construct(protected string $path, protected array $selectedTemplates)
    {
    }

    public function name(): string
    {
        return 'Templates';
    }

    public function execute(): void
    {
        $templates = DB::connection(Mailcoach::getDatabaseConnection())
            ->table(self::getTemplateTableName())
            ->whereIn('id', $this->selectedTemplates)
            ->get();

        $this->writeFile('templates.csv', $templates);
        $this->addMeta('templates_count', $templates->count());
    }
}
