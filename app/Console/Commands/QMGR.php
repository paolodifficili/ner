<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Faker\Factory as Faker;
use Carbon\Carbon;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use Illuminate\Support\Facades\Config;


use App\Jobs\SpacyJob;
use App\Jobs\NerJob;
use App\Jobs\ApiJob;
use App\Jobs\CheckConfigJob;

use App\Models\CodaBatch;
use App\Models\CodaConfig;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttp\Exception\ConnectException;




/*

php artisan QMGR add

Gestore della code
d:\xampp\php\php.exe artisan QMGR add

Come gestire una coda

upload del documento in cartella
lanciare la conversione in testo
lanciare diversi recuperi dati

*/


class QMGR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'QMGR {par1?} {par2?} {par3?} {par4?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'QMGR command line queue manager ';

    
    public $soapClientOptions;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $cmd_par1 = $this->argument('par1');
        $cmd_par2 = $this->argument('par2');
        $cmd_par3 = $this->argument('par3');
        $cmd_par4 = $this->argument('par4');

        Log::channel('stack')->info('QMGR:command line:', [
            $cmd_par1,
            $cmd_par2,
            $cmd_par3,
            $cmd_par4, 
            ] );

        // Setup Soap parameters


        $faker = Faker::create('SeedData');

        switch ($cmd_par1) {

            case "WK_BATCH" :

                // simula l'esecuzione di un batch completo (solo converter)
                // Precarica il file nella cartella e crea la prima cartella 

                

                $batch_uuid = "BATCH_2024_10_12_09_42_09";
                Log::channel('stack')->info('WK_BATCH:batch_uuid:', [$batch_uuid] );
                                
                $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();
        
                $QMGR_ACTION = $batch->batch_action;
                
                Log::channel('stack')->info('WK_BATCH:ACTION', [$QMGR_ACTION] );

                $batch_config = [
                    [
                        'engineType' => 'converter',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//00_INPUT/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'dryRun' => true
                    ],
                    [
                        'engineType' => 'cleaner',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'dryRun' => true
                    ],
                    [
                        'engineType' => 'analyzer',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/",
                        'dryRun' => true
                    ],

                ];
/*
                $folder_00 = "NER_BATCH/" . $batch_uuid . "//00_INPUT/";
                $folder_01 = "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/";
                $folder_02 = "NER_BATCH/" . $batch_uuid . "//02_CLEANER/";
                $folder_03 = "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/";
                $folder_04 = "NER_BATCH/" . $batch_uuid . "//04_REPORT/";
*/

                $job_converter_list = [];
                $job_cleaner_list = [];

                $job_list = [];

                switch ($QMGR_ACTION) {

                    case 'RUN_ENGINE':
        
                        // Save batch start timestamp
                        $batch->last_run_at = Carbon::now();
                        $batch->save();
                        $bo = json_encode($batch->batch_options);
                        Log::channel('stack')->info('WK_BATCH:batch options:', [$bo] );

                        // Converter per ogni file della cartella input
                        // Log::channel('stack')->info('WK_BATCH:folder', [$folder_00] );
                        // $files = Storage::files($folder_00);
                        // Log::channel('stack')->info('WKLB:', [$files]);
                        // $allConv = CodaConfig::where(['type' => 'converter'])->get();
                        $job_list = [];
            
                        foreach($batch_config as $b_c) 
                        {
                            Log::debug('##################   Build Job for', [$b_c['engineType'] , $b_c] );
                            
                            $job_list[$b_c['engineType']] = [];
                            $files = Storage::files($b_c['fileFolderIn']);
                            $engines = CodaConfig::where(['type' => $b_c['engineType']])->get();
                            
                            foreach($files as $fname)
                            {
                                Log::debug('fileName:', [$fname] );
                                $path_parts = pathinfo($fname);
                            
                                foreach($engines as $c)
                                {
                                    Log::debug('engine:', [$c] );
                                    Log::debug('engine:options', [$c->options] );
        
                                    $fileOut = $b_c['fileFolderOut'] . "/" . $path_parts['filename'] . "-" . $c->engine . ".txt";
                                    Log::debug('fileOut:', [$fileOut] );
        
                                    $options = json_decode($c->options);
                                    Log::debug('engine:options:', [$options] );
                                
                                    $batch_id = $batch_uuid;
        
                                    // Prepara il JOB
                                    
                                    $job_id = [];
                                    $job_id['description'] = 'API';
                                    $job_id['type'] = $c->type;
                                    $job_id['engine'] = $c->engine;
                                    $job_id['batch_uuid'] = $batch_id;
                                    $job_id['api_url'] = $c->api;
                                    $job_id['status_url'] = $c->api_status;
                                
            
                                    $options = [
                                        'dryRun' => $b_c['dryRun'],
                                        'method' => $options->method, //
                                        'contentType' => 'text/plain',
                                        'headers' => [
                                            'application/pdf'
                                        ],
                                        'fileInput' => $fname,
                                        'fileOutput' => $fileOut,
                                    ];

                                    $job_id['options'] = json_encode($options);
                
                                    Log::channel('stack')->debug('WK_BATCH:job_id', [$job_id]);
                
                                    $job_list[$b_c['engineType']][] = new ApiJob($job_id);
            
                                    Log::channel('stack')->debug('QMGR add Job to queue', [$b_c['engineType'], $job_id]);
            
                                }

                            }
                        }


                        /*
                        

                        // per ogni file esegue tutti i motori di conversione
                        foreach($files as $fname)
                        {
                            Log::debug('Converte:', [$fname] );
                            $path_parts = pathinfo($fname);
                            Log::debug('Path:', [$path_parts] );
                            
                            foreach($allConv as $c)
                            {
                                Log::debug('engine:', [$c->engine] );
                                Log::debug('engine:options', [$c->options] );
        
                                $fileOut = $folder_01 . "/" . $path_parts['filename'] . "-" . $c->engine . ".txt";
                                Log::debug('fileOut:', [$fileOut] );
        
                                $options = json_decode($c->options);
        
                                Log::debug('engine:options:', [$options] );
                                
                                $batch_id = $batch_uuid;
        
                                // Prepara il JOB
            
                                $job_id = [];
                                $job_id['description'] = 'API';
                                $job_id['type'] = $c->type;
                                $job_id['engine'] = $c->engine;
                                $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                                $job_id['batch_uuid'] = $batch_id;
                                $job_id['api_url'] = $c->api;
                                $job_id['status_url'] = $c->api_status;
                                
            
                                $options = [
                                    'dryRun' => true,
                                    'method' => $options->method, //
                                    'contentType' => 'text/plain',
                                    'headers' => [
                                        'application/pdf'
                                    ],
                                    'fileInput' => $fname,
                                    'fileOutput' => $fileOut,
                                ];
                                $job_id['options'] = json_encode($options);
             
                                Log::channel('stack')->debug('WK_BATCH:job_id', [$job_id]);
            
                                $job_converter_list[] = new ApiJob($job_id);
        
                                Log::channel('stack')->debug('QMGR add Job to queue', [$job_id]);
        
                            }
        
                        }
                        Log::channel('stack')->info('**FINE**CONVERTER **', []);

                        */


        
                    break;

                    default:
                    Log::channel('stack')->error('WK_BATCH: QueueController:mgrBatch', ['ERROR!']);
                    $status = 501;
                    $status_action = 'NO QMGR_ACTION FOUND!';
                    $out = [
                        'message' => 'NO Action found!'
                    ];
                    Log::channel('stack')->error('WK_BATCH: QueueController:mgrBatch', [$out]);
                    
                    break;
                }
                

                $batch = Bus::batch([
                    $job_list['converter'], 
                    $job_list['cleaner'],
                    $job_list['analyzer'],
                    // $job_cleaner_list
                    ])->before(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:before:', [$batch->id] );
                    // The batch has been created but no jobs have been added...
                })->progress(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:progress:', [$batch->id] );
                })->then(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:then:', [$batch->id] );
                })->catch(function (Batch $batch, Throwable $e) {
                    Log::channel('stack')->info('*****WK_BATCH:error:', [$batch->id, $e->getMessage()] );
                })->finally(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:finally:', [$batch->id] );
                })->dispatch();
                
                
                Log::channel('stack')->info('WK_BATCH:id:', [$batch->id] );


                /*


                $allConv = CodaConfig::where(['type' => 'converter'])->get();

                $fname = "WK_BACTH.txt";
                Log::debug('Converte:', [$fname] );

                $path_parts = pathinfo($fname);
                Log::debug('Path:', [$path_parts] );


                $jobList = []; // contiene la lista dei job da avviare

                foreach($allConv as $c)
                {
                    Log::debug('engine:', [$c->engine] );
                    Log::debug('engine:options', [$c->options] );
                    
                    $fileOut = $folder_wl . "/" . $path_parts['filename'] . "-" . $c->engine . ".txt";
                 
                    Log::debug('fileOut:', [$fileOut] );

                    $options = json_decode($c->options);

                    Log::debug(':options:', [$options] );
                    
                    $batch_id = $batch_uuid;

                    // Prepara il JOB

                    $job_id = [];
                    $job_id['description'] = 'API';
                    $job_id['type'] = $c->type;
                    $job_id['engine'] = $c->engine;
                    $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                    $job_id['batch_uuid'] = $batch_id;
                    $job_id['api_url'] = $c->api;
                    $job_id['status_url'] = $c->api_status;

                    $options = [
                        'method' => $options->method, //
                        'contentType' => 'text/plain',
                        'headers' => [
                            'application/pdf'
                        ],
                        'fileInput' => $fname,
                        'fileOutput' => $fileOut,
                    ];
                    $job_id['options'] = json_encode($options);
 
                    Log::channel('stack')->debug('@@@@@@@QMGR add API Job to list', [$job_id]);
                    $job_list[] = new ApiJob($job_id);
                    // ApiJob::dispatch($job_id);
                    // Log::channel('stack')->debug('QMGR add Job to queue', [$job_id]);
                }
*/

                // new ApiJob($job_id);
                // new ImportCsv(1, 100),

/*
                $batch = Bus::batch([
                    $job_converter_list, 
                    $job_cleaner_list ...
                    ])->before(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:before:', [$batch->id] );
                    // The batch has been created but no jobs have been added...
                })->progress(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:progress:', [$batch->id] );
                })->then(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:then:', [$batch->id] );
                })->catch(function (Batch $batch, Throwable $e) {
                    Log::channel('stack')->info('*****WK_BATCH:error:', [$batch->id, $e->getMessage()] );
                })->finally(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:finally:', [$batch->id] );
                })->dispatch();
                
                
                Log::channel('stack')->info('WK_BATCH:id:', [$batch->id] );

*/
                
                


                break;


            case "WK_PREPARE":
                // Prepara i dati di input per tutti i motori di lavoro
                $batch_uuid = 'BATCH_2024_10_12_09_42_09';


                break;


            case "WK_CONVERT": 
                //Esecuzione dei motori di conversione di un batch

                $batch_uuid = 'BATCH_2024_10_12_09_42_09';
                Log::channel('stack')->info('QueueController:mgrBatch:', [$batch_uuid] );
        
                $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();
        
                $QMGR_ACTION = $batch->batch_action;
                Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$QMGR_ACTION] );
                
                $bo = json_decode($batch->batch_options);


                $folder_in = "NER_BATCH/" . $batch_uuid . "//00_INPUT/";
                $folder_wl = "NER_BATCH/" . $batch_uuid . "//01_CONVERSION/";
                Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$folder_in] );
                Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$folder_wl] );
                $files = Storage::files($folder_in);
                Log::channel('stack')->info('WKLB:', [$files]);
                

                $allConv = CodaConfig::where(['type' => 'converter'])->get();

                // per ogni file esegue tutti i motori di conversione
                foreach($files as $fname)
                {
                    Log::debug('Converte:', [$fname] );
                    $path_parts = pathinfo($fname);
                    Log::debug('Path:', [$path_parts] );
                    

                    foreach($allConv as $c)
                    {
                        Log::debug('engine:', [$c->engine] );
                        Log::debug('engine:options', [$c->options] );

                        $fileOut = $folder_wl . "/" . $path_parts['filename'] . "-" . $c->engine . ".txt";
                        Log::debug('fileOut:', [$fileOut] );

                        $options = json_decode($c->options);

                        Log::debug(':options:', [$options] );
                        
                        $batch_id = $batch_uuid;

                        // Prepara il JOB
    
                        $job_id = [];
                        $job_id['description'] = 'API';
                        // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                        $job_id['type'] = $c->type;
                        $job_id['engine'] = $c->engine;
                        // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                        // $job_id['id'] = $faker->uuid();
                        $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                        $job_id['batch_uuid'] = $batch_id;
                        $job_id['api_url'] = $c->api;
                        $job_id['status_url'] = $c->api_status;
    
                        $options = [
                            'method' => $options->method, //
                            'contentType' => 'text/plain',
                            'headers' => [
                                'application/pdf'
                            ],
                            'fileInput' => $fname,
                            'fileOutput' => $fileOut,
                        ];
                        $job_id['options'] = json_encode($options);
     
                        Log::channel('stack')->debug('QMGR add API Job to queue', [$job_id]);
    
                        ApiJob::dispatch($job_id);

                        Log::channel('stack')->debug('QMGR add Job to queue', [$job_id]);

                    }

                }
                Log::channel('stack')->info('**FINE****', []);

                break;

                case "WK_RUN": 
                    //Esecuzione dei motori di analisi
    
                    $batch_uuid = 'BATCH_2024_10_12_09_42_09';
                    Log::channel('stack')->info('QueueController:mgrBatch:', [$batch_uuid] );
            
                    $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();
            
                    $QMGR_ACTION = $batch->batch_action;
                    Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$QMGR_ACTION] );
                    
                    $bo = json_decode($batch->batch_options);
    
    
                    $folder_in = "NER_BATCH/" . $batch_uuid . "//INPUT/";
                    $folder_wl = "NER_BATCH/" . $batch_uuid . "//WORK_LOAD/";
                    $folder_wr = "NER_BATCH/" . $batch_uuid . "//WORK_RESULT/";

                    Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$folder_in] );
                    Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$folder_wl] );
                    
                    $files = Storage::files($folder_wl);
                    Log::channel('stack')->info('WKLB:', [$files]);


                    Log::channel('stack')->info('Batch options:', [$bo]);
                    Log::channel('stack')->info('Batch options:', [$bo->engines_selected]);

                    // Filtra i motori e per ogni motore esegue il lavoro di analisi
                        
                    $allConv = CodaConfig::whereIn('id', $bo->engines_selected)->get();

                    Log::channel('stack')->info('Batch options:', [$allConv]);
    
                    // per ogni file esegue i motori di analisi
                    
                    foreach($files as $fname)
                    {
                        Log::debug('ANALISI:', [$fname] );
                        $path_parts = pathinfo($fname);
                        Log::debug('Path:', [$path_parts] );
                       
    
                        foreach($allConv as $c)
                        {
                            Log::debug('engine:', [$c->engine] );
                            Log::debug('engine:options', [$c->options] );
    
                            $fileOut = $folder_wr . "/" . $path_parts['filename'] . "-" . $c->engine . ".txt";
                            Log::debug('fileOut:', [$fileOut] );
    
                            $options = json_decode($c->options);
    
                            Log::debug(':options:', [$options] );
                            
                            $batch_id = $batch_uuid;
    
                            // Prepara il JOB
        
                            $job_id = [];
                            $job_id['description'] = 'API';
                            // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                            $job_id['type'] = $c->type;
                            $job_id['engine'] = $c->engine;
                            // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                            // $job_id['id'] = $faker->uuid();
                            $job_id['id'] = 'to_gen?';
                            $job_id['batch_uuid'] = $batch_id;
                            $job_id['api_url'] = $c->api;
                            $job_id['status_url'] = $c->api_status;
        
                            $options = [
                                'method' => $options->method, //
                                'contentType' => 'text/plain',
                                'headers' => [
                                    'application/pdf'
                                ],
                                'fileInput' => $fname,
                                'fileOutput' => $fileOut,
                            ];
                            $job_id['options'] = json_encode($options);
         
                            // Log::channel('stack')->debug('QMGR add API Job to queue', [$job_id]);
        
                            ApiJob::dispatch($job_id);
    
                            Log::channel('stack')->debug('QMGR add Job to queue', [$job_id]);
    
                        }
    
                    }
                    
                    Log::channel('stack')->info('**FINE****', []);
    
                    break;
    

            case "LV":

                Log::debug('LV', [] );
                $allConv = CodaConfig::where(['type' => 'converter'])->get();
                foreach($allConv as $c)
                {

                    Log::debug('ApiJob->check_url:PUT!', [$c->engine] );

                }


                break;


            case "PUT":

                $api_url = 'http://10.10.6.25:9998/tika';
                Log::debug('ApiJob->check_url:PUT!', [$api_url] );
                /*
                $response = Http::attach(
                    'attachment', file_get_contents('D:/tmp/simple.pdf'), 'simple.pdf', ['Content-Type' => 'application/pdf']
                )
                ->withOptions(['verify' => false])
                ->put($api_url);

                */

                $response = Http::withBody(
                    file_get_contents('D:/tmp/simple.pdf'), 'application/pdf'
                )->put($api_url);


                Log::debug('ApiJob->response:', [$response] );
                $statusCode = $response->status();
                Log::debug('ApiJob->response:', [$statusCode] );
                Log::debug('ApiJob->response:', [$response->body()] );

                break;

            case "APIJOB_CONVERT": 
                // TEST ESECUZIONE DI UNA BATCH PER PREPARAZIONE INPUT
                // LA CARTELLA E' GIA' CREATA

                $batch_id = "BATCH_2024_10_12_09_42_09";

                // Recupero del Batch e lancio dei motori di azione

                $job_id = [];
                $job_id['description'] = 'API';
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'coverter';
                $job_id['engine'] = 'tika';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                $job_id['batch_uuid'] = $batch_id;
                $job_id['api_url'] = 'http://10.10.6.25:9998/tika';
                $job_id['status_url'] = 'http://10.10.6.25:9998';

                $options = [
                    'method' => 'PUT',
                    'headers' => [
                        'application/pdf'
                    ],
                    'fileInput' => 'NER_BATCH/$batch_id/simple.pdf',
                    'fileOutput' => 'NER_BATCH/$batch_id/tika-simeple.txt',
                ];
                $job_id['options'] = json_encode($options);
 
                Log::channel('stack')->info('QMGR add API Job to queue', [$job_id]);
                ApiJob::dispatch($job_id);

                break;

            case "RB": // Run batch demo ...

                // ESEGU


                $batch_uuid = 'BATCH_2024_10_12_09_42_09';
                Log::channel('stack')->info('QueueController:mgrBatch:', [$batch_uuid] );
        
                $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();
        
                $QMGR_ACTION = $batch->batch_action;
                Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$QMGR_ACTION] );
                
                $bo = json_decode($batch->batch_options);
                Log::channel('stack')->info('QueueController:mgrBatch:RUN:with:', [$bo] );
                Log::channel('stack')->info('', [$bo->engines_selected] );

                foreach ($bo->engines_selected as $e) {

                    $job_id = [];
                    $job_id['description'] = 'CHECKCONFIG';
                    // $job_id['type'] = $c->type;
                    // $job_id['engine'] = $c->engine;
                    // $job_id['batch_uuid'] = $batch_uuid;
        
                    Log::channel('stack')->info('EngineId', [$e]);

                    $engine = CodaConfig::findOrFail($e);

                    Log::channel('stack')->info('Engine', [$engine]);

                    // CheckConfigJob::dispatch($job_id);

                    // $job_list = $job_id;
                }


                break;


            case "JSON":
                Log::debug('Save json and retrieve:', [] );

                $batch_id = 'BATCH' . $faker->numberBetween($min = 1, $max = 2000);
                $batch_id = 'BATCH1774';

                $data = [
                    "name" => $batch_id,
                    "title" => "Mr.",
                    "age" => 33,
                    "City" => "Naples"
                ];

                $data_json = json_encode($data);

                
                $codab = CodaBatch::firstOrNew([
                    'batch_uuid' => $batch_id,
                    'batch_description' => $batch_id,
                ]);

                Log::channel('stack')->info('codabatch JSON:', [$codab]);    
                Log::channel('stack')->info('codabatch JSON:', [$data_json]);    

                $codab->info = 'INFO';

                $codab->batch_options = $data_json;

                $codab->save();

                Log::channel('stack')->info('codabatch JSON:', [$codab]);    


            break;

            case "HTTP":
                $url = "http://127.0.0.1:8900/test";

                Log::debug(' API STATUS:', [$url] );
    
                try {
                    $response = Http::withOptions(['verify' => false])->get($url);
                } 

                catch( \Illuminate\Http\Client\ConnectionException $e)
                {
                    Log::channel('stack')->error('QMGR ConnectionException:', [$e->getMessage()]);    
                }

                catch (\Exception $e) {
                    Log::channel('stack')->error('QMGR Exception:', []);    
                }
                
                
            break;


            case "ORM":

                $config1 = CodaConfig::where([
                    'type' => 'folder',
                    'engine' => 'root_folder',
                ])->first();

                Log::channel('stack')->info('QMGR merge:', [$config1]);

                $config = CodaConfig::where([
                    'type' => 'folder',
                    'engine' => 'root_folder',
                ])->firstOrFail();
             

                Log::channel('stack')->info('QMGR merge:', [$config]);

            break;

            case "checkConfigJOB":

                $batch_id = 'BATCH' . $faker->numberBetween($min = 1, $max = 2000);

                // crea un job di tipo root_folder

                $job_id = [];
                $job_id['description'] = 'CHECKCONFIG';
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'folder';
                $job_id['engine'] = 'root_folder';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                $job_id['batch_uuid'] = $batch_id;
 
                Log::channel('stack')->info('QMGR add Job to queue', [$job_id]);
                CheckConfigJob::dispatch($job_id);

                // crea un job di tipo spacy

                $job_id = [];
               
                $job_id['description'] = 'CHECKCONFIG';
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'ner';
                $job_id['engine'] = 'spacy_ner';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'JOB_XXX-BBB-CCCC';
                $job_id['batch_uuid'] = $batch_id;
 
                Log::channel('stack')->info('QMGR add Job to queue', [$job_id]);
                CheckConfigJob::dispatch($job_id);


                $job_id = [];
                $job_id['description'] = 'CHECKCONFIG';
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'ner';
                $job_id['engine'] = 'hf01';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'JOB_XXX-BBB-CCCC';
                $job_id['batch_uuid'] = $batch_id;
 
                Log::channel('stack')->info('QMGR add Job to queue', [$job_id]);
                CheckConfigJob::dispatch($job_id);

            break;

            case "merge":
                
                $uuid = "1726138047856";
                $fname = $uuid . "/out.pdf";
                Log::channel('stack')->info('QMGR merge:', [$uuid]);
                
                $files = Storage::files($uuid);
                Log::channel('stack')->info('QMGR merge:', [$files]);


                $stringToRemove = $uuid . "/";

                $onlyFileName = array_map(function($item) use ($stringToRemove) {
                    // Rimuovi la stringa dall'inizio (se presente)
                    $item = preg_replace('/^' . preg_quote($stringToRemove, '/') . '/', '', $item);
                    
                    // Converti l'elemento in double
                    return intval($item);
                }, $files);
                
                // Ordina l'array in maniera crescente
                sort($onlyFileName);

                Log::channel('stack')->info('QMGR merge:', [$onlyFileName]);

                foreach($onlyFileName as $item ) {

                    // read contents
                    $file2get = $uuid . "/" . $item;
                    Log::channel('stack')->info('QMGR read:', [$file2get]);
                    $contents = Storage::get($file2get);

                    if( $item == 0) {
                        Log::channel('stack')->info('QMGR write 2:', [$fname]);
                        Storage::put($fname, $contents);
                    } else {
                        Log::channel('stack')->info('QMGR append 2:', [$fname]);
                        Storage::append($fname, $contents);
                    }

                }


            break;


            case "batch":
                $faker = Faker::create('SeedData');

                $root_folder = Config::get('ner_services.root_folder');

                $engine = 'spacy_ner';
               
                $api = Config::get('ner_services.' . $engine . '.api');
                Log::channel('stack')->info('QMGR config:', [$engine, $root_folder, $api]);

                $batchID =  $faker->uuid();
                

                $job_1 = [];
                $job_1['description'] = strtoupper($faker->firstName());
                $job_1['type'] = $engine;
                $job_1['id'] = $faker->uuid();
                $job_1['batch_uuid'] = $batchID;
                $job_1['failed'] = '';
                $job_1['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_1['root_folder'] = $root_folder;
                $job_1['service_url'] = $api;

                $engine = 'spacy_regexp';

                $api = Config::get('ner_services.' . $engine . '.api');
                Log::channel('stack')->info('QMGR config:', [$engine, $root_folder, $api]);

                $job_2 = [];
                $job_2['description'] = strtoupper($faker->firstName());
                $job_2['type'] = $engine;
                $job_2['id'] = $faker->uuid();
                $job_2['batch_uuid'] = $batchID;
                $job_2['failed'] = '';
                $job_2['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_2['root_folder'] = $root_folder;
                $job_2['service_url'] = $api;



                $batch = Bus::batch([
                    new NerJob($job_1),
                    new NerJob($job_2)
                ])
                ->before(function ($batch) {
                 // The batch has been created but no jobs have been added...
                })
                ->progress(function ($batch) {
                    Log::channel('stack')->info('BATCH PROGRESS:', [$batch]);
                    // A single job has completed successfully...
                })->then(function ($batch) {
                    // All jobs completed successfully...
                })->catch(function ($batch, Throwable $e) {
                    Log::channel('stack')->error('BATCH ERROR:', [$batch->id]);
                    Log::channel('stack')->error('BATCH ERROR:', [$e]);
                })->finally(function ($batch) {
                    Log::channel('stack')->info('BATCH COMPLETED:', [$batch->id]);
                })->allowFailures()->name($batchID)->dispatch();
                                
                Log::channel('stack')->info('BATCH ID:', [$batch->id, $batchID]);

            break;


            case "run":

               # Artisan::call("infyom:scaffold", ['name' => $request['name'], '--fieldsFile' => 'public/Product.json']);
               \Artisan::call("queue:work", ['--max-jobs' => '100']);

            break;


            case "add":
                $faker = Faker::create('SeedData');

                $engine = 'spacy_ner';
                Log::channel('stack')->info('QMGR engine:', [$engine]);

                $root_folder = Config::get('ner_services.root_folder');
                Log::channel('stack')->info('QMGR config root_folder:', [$engine, $root_folder]);

                $api = Config::get('ner_services.spacy_ner.api');
                Log::channel('stack')->info('QMGR config root_folder:', [$api]);

                $job_id = [];
                $job_id['description'] = strtoupper($faker->firstName());
                // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                $job_id['type'] = 'spacy_ner';
                // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                // $job_id['id'] = $faker->uuid();
                $job_id['id'] = 'AAA-BBB-CCCC';
                $job_id['batch_uuid'] = 'AAA-BBB-CCCC';
                $job_id['failed'] = '';
                $job_id['file'] = $faker->randomElement(['a.pdf','b.pdf','c.pdf']);
                $job_id['root_folder'] = $root_folder;
                $job_id['service_url'] = $api;

                Log::channel('stack')->info('QMGR add Job to queue', [$job_id]);
                NerJob::dispatch($job_id);
                
            break;


            case "dryrun":

                $type = "tika";
                $status_url = Config::get('ner_services.tika.status_url');
                $value2 = Config::get('ner_services.tika.status_url2');
                Log::channel('stack')->info('QMGR config info:', [$status_url, $value2]);

                $contents = "TEST00022221";
                Storage::put('1312313/file2.txt', $contents);
                
                /*
                if (! Storage::put('file.jpg', $contents)) {
                    // The file could not be written to disk...
                }

                Personally identifiable information (PII) is any data that can be used to identify someone

                */

            break;
            
            default:
                Log::channel('stack')->info('... command not found');
			break;
        }

        return 0;
    }

  

 


    
    public function clean($string) 
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }


}
