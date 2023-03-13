<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ExportSingleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export_single_data';

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
        $url = 'https://api.uktradeinfo.com/Export(CommodityId=82,MonthId=201912,TraderId=6177000)';
        $this->info('Collect Data...');
        $response = Http::get($url);
        if ($response->getStatusCode() == 200) {
            $Export_json = $response->getBody()->getContents();
            $Export_data = json_decode($Export_json, true);
            $progress = $this->output->createProgressBar(count($Export_data['value']));
            $progress->advance();

            $CommodityPath = "Commodity";
            if (!Storage::exists($CommodityPath)) {
                Storage::makeDirectory($CommodityPath);
            }

            $MonthPath = "Month";
            if (!Storage::exists($MonthPath)) {
                Storage::makeDirectory($MonthPath);
            }

            $TraderPath = "Trader";
            if (!Storage::exists($TraderPath)) {
                Storage::makeDirectory($TraderPath);
            }

            foreach ($Export_data['value'] as $export) {
                $commodityId = $export['CommodityId'];
                $monthId = $export['MonthId'];
                $traderId = $export['TraderId'];
                $year = substr($monthId, 0, -2);
                $month = substr($monthId, 4);
                $storeCSVPath = "Export/$year/$month";
                if (!Storage::exists($storeCSVPath)) {
                    Storage::makeDirectory($storeCSVPath, 0777, true, true);
                }
                $csvFile = fopen(Storage::path($storeCSVPath . "/" . "$monthId.csv"), 'w+');
                $header = ['TraderId', 'CommodityId', 'MonthId'];
                fputcsv($csvFile, $header);
                fputcsv($csvFile, $export);

                //Commodity Record Json To .CSV file Convert
                $Commodity = "https://api.uktradeinfo.com/Commodity/{$commodityId}";
                $rers_commo = Http::get($Commodity);
                if ($rers_commo->getStatusCode() == 200) {
                    $Commodity_json = $rers_commo->getBody()->getContents();
                    $Commodity_data = json_decode($Commodity_json, true);
                    unset($Commodity_data['@odata.context']);
                    $CommodityFile = fopen(Storage::path($CommodityPath . "/" . "$commodityId.csv"), 'w+');
                    $header = ['CommodityId', 'Cn8Code', 'Hs2Code', 'Hs4Code', 'Hs6Code', 'Hs2Description', 'Hs4Description', 'Hs6Description', 'SitcCommodityCode', 'Cn8LongDescription'];
                    fputcsv($CommodityFile, $header);
                    fputcsv($CommodityFile, $Commodity_data);
                }

                //Month Record Json To .CSV file Convert
                $Date = "https://api.uktradeinfo.com/Date/{$monthId}";
                $rers_date = Http::get($Date);
                if ($rers_date->getStatusCode() == 200) {
                    $Date_json = $rers_date->getBody()->getContents();
                    $Date_data = json_decode($Date_json, true);
                    unset($Date_data['@odata.context']);

                    $date_csvFile = fopen(Storage::path($MonthPath . "/" . "$monthId.csv"), 'w+');
                    $date_header = ['MonthId', 'Year', 'MonthNumeric', 'QuarterNumeric', 'MonthName'];
                    fputcsv($date_csvFile, $date_header);
                    if (isset($Date_data)) {
                        fputcsv($date_csvFile, $Date_data);
                    }
                    fclose($date_csvFile);
                }

                //Trader Record Json To .CSV file Convert
                $Trader = "https://api.uktradeinfo.com/Trader/{$traderId}";
                $rers_trader = Http::get($Trader);
                if ($rers_trader->getStatusCode() == 200) {
                    $Trader_json = $rers_trader->getBody()->getContents();
                    $Trader_data = json_decode($Trader_json, true);
                    unset($Trader_data['@odata.context']);

                    $trader_csvFile = fopen(Storage::path($TraderPath . "/" . "$traderId.csv"), 'w+');
                    $trader_header = ['TraderId', 'CompanyName', 'Address1', 'Address2', 'Address3', 'Address4', 'Address5', 'PostCode'];
                    fputcsv($trader_csvFile, $trader_header);
                    if (isset($Trader_data)) {
                        fputcsv($trader_csvFile, $Trader_data);
                    }
                }

            }
            fclose($csvFile);
            fclose($CommodityFile);
            fclose($date_csvFile);
            fclose($trader_csvFile);
            $this->newLine();
            $this->info('Done');
        }else{
            $this->info('Try Again...');
        }
        return Command::SUCCESS;
    }
}
