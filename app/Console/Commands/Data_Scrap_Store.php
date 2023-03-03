<?php

namespace App\Console\Commands;

use App\Console\Commands\MyArchive7z;
use App\Spiders\Data_Scrap;
use Archive7z\Archive7z;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RoachPHP\Roach;
use function Nette\Schema\Expect;

class Data_Scrap_Store extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:data_scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Is Data Scraping';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $links = Roach::collectSpider(Data_Scrap::class);
        if ($links != null) {
            $links = $links[0]['data'];
            $links = [$links[0], $links[1], $links[2], $links[13], $links[14]
                , $links[15], $links[16], $links[17], $links[18], $links[19]
                , $links[20], $links[26], $links[39], $links[52], $links[65]];

            //Remove Array
            unset($links[12], $links[25], $links[38], $links[51],
                $links[64], $links[77], $links[90], $links[103],
                $links[116], $links[129], $links[142], $links[155],
                $links[168], $links[181], $links[194], $links[207],
                $links[220], $links[233], $links[246], $links[259],
                $links[272], $links[285], $links[298], $links[311],
                $links[324], $links[337], $links[350], $links[363],
                $links[376], $links[389], $links[402], $links[415],
                $links[428], $links[441], $links[452], $links[454],
                $links[467], $links[480], $links[481], $links[482],
                $links[483], $links[484], $links[485], $links[486]);


            $storePath = "euro-state";
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
                $year = substr(strrchr($fileName, 'full'), 4, -2);
                $month = substr(strrchr($fileName, 'full'), -2);
                $storeCSVPath = "Product/$year/$month";
                if (!Storage::exists($storeCSVPath)) {
                    Storage::makeDirectory($storeCSVPath, 0777, true, true);
                }
                rename("storage/app/euro-state/$fileName.dat", "storage/app/Product/$year/$month/$fileName.csv");
            }


            $this->newLine();
            $this->info("Done!");
            return Command::SUCCESS;
        } else {
            $this->info('No Link Found...');
        }
    }

}
