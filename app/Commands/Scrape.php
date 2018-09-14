<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Weidner\Goutte\GoutteFacade as Goutte;

class Scrape extends Command
{
    protected $signature = 'scrape';

    protected $description = 'Scrape https://www.behindthename.com/names';

    public function handle()
    {
        $file = fopen('names.csv', 'c');

        $crawler = Goutte::request('GET', 'https://www.behindthename.com/names');

        $pages = $crawler->filter('.pagination a.pgcanhide')->last()->text();

        $bar = $this->output->createProgressBar($pages);

        $page = 1;

        do {
            $bar->advance();
            
            $crawler->filter('.browsename')->each(function ($node) use ($file) {
                $name = $node->filter('.listname a')->text();
                $name = preg_replace('/[\d\(\)\']/', '', $name);
                $name = trim($name);
    
                $gender = $node->filter('.listgender span')->text();
                
                $usage = $node->filter('.listusage a')->text();
    
                $fields = [$name, $gender, $usage];
    
                fputcsv($file, $fields);
            });

            $page++;
            $crawler = Goutte::request('GET', 'https://www.behindthename.com/names/' . $page);
        } while ($page <= $pages);

        $bar->finish();
        
        fclose($file);
    }
}
