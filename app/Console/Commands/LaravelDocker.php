<?php

namespace App\Console\Commands;

use Archive7z\Archive7z;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RoachPHP\Roach;
use App\Spiders\LaravelDocsSpider;

class LaravelDocker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:laravelDocker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $links = Roach::collectSpider(LaravelDocsSpider::class);
        if ($links != null) {
            $links = $links[0]['data'];
            $links = [$links[0], $links[1], $links[2], $links[3], $links[4], $links[5]];

            $storePath = "Laravel-Docker";
            if (!Storage::exists($storePath)) {
                Storage::makeDirectory($storePath);
            }
            $this->info("Download Zip File ...");
            $progress = $this->output->createProgressBar(count($links));
            $progress->advance();
            $this->newLine();
            $downloadedFiles = null;
            foreach ($links as $link) {
                $response = Http::timeout(300)->retry(100, 4)->get($link);
                $filename = pathinfo(urldecode($link), PATHINFO_BASENAME);
                $con = $response->body();
                $path = $storePath . "/" . $filename;

                $downloadedFiles[] = Storage::path($path);
                Storage::put($path, $con);
            }

            //Extract Zip File
            $this->info("Zip File Extract ...");
            $progress = $this->output->createProgressBar(count($downloadedFiles));
            $progress->advance();
            $this->newLine();
            foreach ($downloadedFiles as $file) {
                $obj = new Archive7z($file);
                foreach ($obj->getEntries() as $entry) {
                    $entry->extractTo(Storage::path($storePath));
                }
            }

            //Rename .dat to .csv file
            $this->info('Rename .dat to .csv...');
            $progress = $this->output->createProgressBar(count($downloadedFiles));
            $progress->advance();
            foreach ($downloadedFiles as $file) {
                $info = pathinfo($file);
                $fileName = $info['filename'];
                $year = substr(strrchr($fileName, 'refs'), 4);
                $storeCSVPath = "Laravel-Dockers/$year";
                if (!Storage::exists($storeCSVPath)) {
                    Storage::makeDirectory($storeCSVPath, 0777, true, true);
                }
                rename("storage/app/Laravel-Docker/$fileName.dat", "storage/app/Laravel-Dockers/$year/$fileName.csv");
            }


            $this->newLine();
            $this->info("Done!");
            return Command::SUCCESS;
        } else {
            $this->info('No Link Found...');
        }
    }
}
