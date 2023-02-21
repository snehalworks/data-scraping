<?php

namespace App\Console\Commands;

use App\Spiders\Data_Scrap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
//        $url = "https://ec.europa.eu/eurostat/estat-navtree-portlet-prod/BulkDownloadListing?sort=1&dir=comext%2FCOMEXT_DATA%2FPRODUCTS";
//        $allLinks = Roach::collectSpider(Data_Scrap::class,
//            new Overrides(startUrls: [$url]));
        $storePath = "Product";

        if (!Storage::exists($storePath)) {
            Storage::makeDirectory($storePath);
        }

//        $this->info("Get Zip File ...");
        $this->info("Link Collecting...");
        $progress = $this->output->createProgressBar(count($links));

        //  $file = fopen(Storage::path($storePath . "/" . "unexe.csv"), 'w+');
        $header = ['DECLARANT', 'DECLARANT_ISO', 'PARTNER', 'PARTNER_ISO', 'TRADE_TYPE', 'PRODUCT_NC',
            'PRODUCT_SITC', 'PRODUCT_CPA2002', 'PRODUCT_CPA2008', 'PRODUCT_CPA2_1', 'PRODUCT_BEC',
            'PRODUCT_BEC5', 'PRODUCT_SECTION', 'FLOW', 'STAT_REGIME', 'SUPP_UNIT', 'PERIOD',
            'VALUE_IN_EUROS', 'QUANTITY_IN_KG', 'SUP_QUANTITY'];
//        fputcsv($file, $header);
        $final_link = $links[0]['data'][0];
        foreach ($final_link as $item) {
            //Year/Number/Csv Get
            $str = substr(strrchr($item, 'll'), 1, -5);
            $year = Str::limit($str, 4);
            $number = substr(strrchr($item, 'llfull'), 5, -3);
            $csv = substr(strrchr($item, '%2F'), 3, -3);

            $yearstorePath = "Product/$year/$number";

//            if (!Storage::exists($yearstorePath)) {
//                Storage::makeDirectory($yearstorePath);
//            }
        // $file = fopen(Storage::path($yearstorePath . "/" . $csv . ".csv"), 'w+');

            //  Download Zip File
//            $url = $item;
//            $response = Http::timeout(300)->retry(4, 100)->get($url);
//            Storage::put("Zip_file/$csv.zip", $response->getBody());



        }

//        dd($file);
        $this->info("Zip File Download ...");
        $this->info("Done ...");
        $progress = $this->output->createProgressBar(count($final_link));
        $progress->advance();


        //Extract Zip File
        $this->info("Zip File Extract ...");

        $files = glob(storage_path("app/Zip_file"));
        foreach ($files as $key => $value) {
            print_r($value);
            $relativeName = basename($value);
            //  $zip->addFile($value, $relativeName);
        }


        $this->newLine();
        $this->info("Done!");
        return Command::SUCCESS;
    }
}
