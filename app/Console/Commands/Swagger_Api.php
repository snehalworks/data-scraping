<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Swagger_Api extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:swagger_api {id}';

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
        $url = 'https://api.uktradeinfo.com/Export';
        $id = $this->argument('id');
        $Commodity = "https://api.uktradeinfo.com/Commodity/{$id}";
        $Date = "https://api.uktradeinfo.com/Date/{$id}";
        $Trader = "https://api.uktradeinfo.com/Trader/{$id}";
        $response = Http::get($url);
        $rers_commo = Http::get($Commodity);
        $rers_date = Http::get($Date);
        $rers_trader = Http::get($Trader);
        if ($rers_commo->getStatusCode() == 200 || $rers_date->getStatusCode() == 200 || $rers_trader->getStatusCode() == 200) {
            $Commodity_json = $rers_commo->getBody()->getContents();
            $Date_json = $rers_date->getBody()->getContents();
            $Trader_json = $rers_trader->getBody()->getContents();
            $Commodity_data = json_decode($Commodity_json, true);
            $Date_data = json_decode($Date_json, true);
            $Trader_data = json_decode($Trader_json, true);

//            dd($Commodity_data,$Date_data,$Trader_data);
unset($Commodity_data['@odata.context'],$Date_data['@odata.context'],$Trader_data['@odata.context']);
            //Json to .csv convert

            //Commodity
            $storePath = "Commodity";
            if (!Storage::exists($storePath)) {
                Storage::makeDirectory($storePath);
            }

            $csvFile = fopen(Storage::path($storePath . "/" . "commodity.csv"), 'w+');
            $header = ['CommodityId', 'Cn8Code', 'Hs2Code', 'Hs4Code', 'Hs6Code', 'Hs2Description', 'Hs4Description', 'Hs6Description', 'SitcCommodityCode', 'Cn8LongDescription'];
            fputcsv($csvFile, $header);
            if (isset($Commodity_data)) {
                fputcsv($csvFile, $Commodity_data);
            }
            fclose($csvFile);

            //Date

            $date_storePath = "Month";
            if (!Storage::exists($date_storePath)) {
                Storage::makeDirectory($date_storePath);
            }

            $date_csvFile = fopen(Storage::path($date_storePath . "/" . "date.csv"), 'w+');
            $date_header = ['MonthId', 'Year', 'MonthNumeric', 'QuarterNumeric', 'MonthName'];
            fputcsv($date_csvFile, $date_header);
            if (isset($Date_data)) {
                fputcsv($date_csvFile, $Date_data);
            }
            fclose($date_csvFile);

            //Trader

            $trader_storePath = "Trader";
            if (!Storage::exists($trader_storePath)) {
                Storage::makeDirectory($trader_storePath);
            }
            $trader_csvFile = fopen(Storage::path($trader_storePath . "/" . "trader.csv"), 'w+');
            $trader_header = ['TraderId', 'CompanyName', 'Address1', 'Address2', 'Address3', 'Address4', 'Address5', 'PostCode'];
            fputcsv($trader_csvFile, $trader_header);
            if (isset($Trader_data)) {
                fputcsv($trader_csvFile, $Trader_data);
            }
            fclose($trader_csvFile);

            $this->info('Data received from API:');
//            $this->line($Commodity_data);
//            $this->line($Date_data);
//            $this->line($Trader_data);
//            dd($Commodity_data, $Date_data, $Trader_data);
        }


//        if ($response->getStatusCode() == 200) {
//            $json = $response->getBody()->getContents();
//            $data = json_decode($json, true);
//            $array = collect($data)->toArray();
//            $csvFile = fopen(Storage::path("output.csv"), 'w+');
//            $header = ['TraderId', 'CommodityId', 'MonthId'];
//            fputcsv($csvFile, $header);
//            foreach ($array['value'] as $row) {
//                fputcsv($csvFile, $row);
//            }
//            fclose($csvFile);
//
//            $this->info('Data received from API:');
//            $this->line($json);
//        } else {
//            $this->error('Error: API call failed with status code ' . $response->getStatusCode());
//        }

//        $json = $url->getBody()->getContents();
//        $data = json_decode($json, true);
//        dd($url);
//        $client = new Client();
//        $response = $client->get($url);


        return Command::SUCCESS;
    }
}
