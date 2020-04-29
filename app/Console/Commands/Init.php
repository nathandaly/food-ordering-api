<?php

namespace App\Console\Commands;

use App\Entities\Intergration\Page;
use App\Entities\Intergration\System;
use App\Exceptions\IntegrationPageCreateFailed;
use App\Exceptions\IntegrationPageExists;
use App\Exceptions\IntegrationSystemCreateFailed;
use App\Exceptions\IntegrationSystemExists;
use App\Helpers\DatabaseConnection;
use Illuminate\Console\Command;

/**
 * Class Init
 * @package App\Console\Commands
 */
class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fo:init {connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize any food ordering settings depending on the set supplier.';

    /**
     * @throws IntegrationPageCreateFailed
     */
    public function handle()
    {
        $apiKey = trim($this->argument('connection'));
        DatabaseConnection::addConnection($apiKey);
        DatabaseConnection::setDefaultConnection($apiKey);

        try {
            $this->createIntegrationPages();
        } catch (IntegrationPageExists $e) {
            $this->comment('Integration pages exists... Skipping!');
        } catch (IntegrationPageCreateFailed $e) {
            $this->error('Failed creating integration pages!');
        }

        try {
            $this->createIntegrationSystem();
        } catch (IntegrationSystemExists $e) {
            $this->comment('Integration system "' . $e->getMessage() . '" exists... Skipping!');
        } catch (IntegrationSystemCreateFailed $e) {
            $this->error('Failed creating integration system"' . $e->getSystemName() . '"!');
        }
    }

    /**
     * @throws IntegrationPageCreateFailed
     * @throws IntegrationPageExists
     */
    public function createIntegrationPages(): void
    {
        $pages = ['Add/Edit store',	'Mange your centre'];
        $persistedPages = [];
        $existingResult = Page
            ::where([
                ['name', 'like', '%' . $pages[0] . '%'],
            ])
            ->orWhere([
                ['name', 'like', '%' . $pages[1] . '%'],
            ])
            ->get();

        if ((bool) $existingResult->count()) {
            throw new IntegrationPageExists();
        }

        foreach ($pages as $page) {
            $persistedPage = Page::create(['name' => $page]);

            if (!$persistedPage->id) {
                throw new IntegrationPageCreateFailed();
            }

            $persistedPages[] = [
                'id' => $persistedPage->id,
                'name' => $persistedPage->name,
            ];
        }

        $this->info('New integration pages created!');
        $this->table(['id', 'name'], $persistedPages);
    }

    /**
     * @throws IntegrationSystemCreateFailed
     * @throws IntegrationSystemExists
     */
    private function createIntegrationSystem(): void
    {
        $integrationPage = ['name' => env('ORDERING_PROVIDER_NAME', 'database')];

        if (System::where(array_keys($integrationPage)[0], array_values($integrationPage)[0])->first()) {
            throw new IntegrationSystemExists($integrationPage['name']);
        }

        $system = System::create($integrationPage);

        if (!$system->id) {
            throw new IntegrationSystemCreateFailed($integrationPage['name']);
        }

        $this->info('New integration system created!');
        $this->table(['id', 'name'], [['id' => $system->id, 'name' => $system->name]]);
    }
}
