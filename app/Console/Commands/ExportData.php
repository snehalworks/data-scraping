<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export_data';

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
            foreach ($Export_data['value'] as $export) {
                $name = $export['MonthId'];
                $year = substr($name, 0, -2);
                $month = substr($name, 4);
                $storeCSVPath = "Export/$year/$month";
                if (!Storage::exists($storeCSVPath)) {
                    Storage::makeDirectory($storeCSVPath, 0777, true, true);
                }
                $csvFile = fopen(Storage::path($storeCSVPath . "/" . "$name.csv"), 'w+');
                $header = ['TraderId', 'CommodityId', 'MonthId'];
                fputcsv($csvFile, $header);
                fputcsv($csvFile, $export);

            }
            fclose($csvFile);
            $this->newLine();
            $this->info('Done');
        }
        return Command::SUCCESS;
    }
}
