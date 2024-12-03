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
use App\Jobs\ApiJobFolder;
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

            case "WK_FILE":

                $batch_uuid = "BATCH_2024_10_12_09_42_09";
                
                $fileFolderIn = "NER_BATCH/" . $batch_uuid . "//00_INPUT/";
                $fileFolderOut = "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/";

                $engine = 'ollama';
                $engine_version = 'phi3';

                $files = Storage::files($fileFolderIn);
                
                Log::channel('stack')->info($files);

                foreach($files as $fname)
                {
                    Log::debug('fileName:', [$fname] );
                    $path_parts = pathinfo($fname);
                    Log::channel('stack')->info($path_parts);

                    $fileOut = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $engine . '-' . $engine_version . ".txt";
                    Log::debug($fileOut);
                }
                

                break;

            
            case "WK_JOB_FOLDER":

                $batch_uuid = "BATCH_2024_10_12_09_42_09";

                $fileFolderIn = "NER_BATCH/" . $batch_uuid . "//02_CLEANER/";
                $fileFolderOut = "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/";
                $dryRun = false;

                $engines = CodaConfig::where(['type' => 'analyzer'])->get();

                // per ogni motore di tipo engine esegue l'azione

                foreach($engines as $c)
                {
                    Log::debug('engine:', [$c] );

                    // dd($c->options);

                    Log::debug('engine:options', [$c->options] );
        
                    # $fileOut = $fileFolderOut . "/" . $path_parts['filename'] . "-" . $c->engine . "-" . $c->engine_version . ".txt";
                    # Log::debug('fileOut:', [$fileOut] );
        
                    // dd($c->options);

                    $eng_options = json_decode($c->options);

                    Log::debug('engine:options:', [$eng_options] );

                    Log::debug('engine:options:', [$eng_options->method ] );
                    
                    // dd($eng_options);
                                
                    $batch_id = $batch_uuid;
        
                    // Prepara il JOB ------------------------------------- -------------------------- -------------
                    
                    $job_id = [];
                    $job_id['description'] = 'API';
                    $job_id['type'] = $c->type;
                    $job_id['engine'] = $c->engine;
                    $job_id['engine_version'] = $c->engine_version;
                    $job_id['batch_uuid'] = $batch_id;
                    $job_id['api_url'] = $c->api;
                    $job_id['status_url'] = $c->api_status;
            
                    $options = [
                        'dryRun' => $dryRun,
                        'method' => $eng_options->method, 
                        'contentType' => 'text/plain',
                        'headers' => [
                            'application/pdf'
                        ],
                        'preAction' => $eng_options->preAction ?? false,
                        'postAction' => $eng_options->postAction ?? false,
                        'promptTxt' => $eng_options->promptTxt ?? false,
                        'promptTag' => $eng_options->promptTag ?? false,
                        'model' => $eng_options->model ?? false,
                        'fileInput' => $fileFolderIn,
                        'fileOutput' => $fileFolderOut,
                    ];

                    $job_id['options'] = json_encode($options);
        
                    Log::channel('stack')->debug('WK_JOB_FOLDER:job_id', [$job_id]);
                
                    ApiJobFolder::dispatch($job_id);

                }

                break;


            case "WK_BATCH":

                // Esegue un batch completo dopo essere stato creato da Upload ....
                
                $batch_uuid = "BATCH____1731583937171";

                Log::channel('stack')->info('WK_BATCH:batch_uuid:', [$batch_uuid] );
                                
                $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();
        
                $QMGR_ACTION = $batch->batch_action;
                
                Log::channel('stack')->info('WK_BATCH:ACTION', [$QMGR_ACTION] );

                $dryRun_converter = false; // esegue una finta chiamata per verificare la struttura delle cartelle
                $dryRun_cleaner = false;   // esegue una finta chiamata per verificare la struttura delle cartelle
                $dryRun_analyzer = false;  // esegue una finta chiamata per verificare la struttura delle cartelle

                // elenco azioni da eseguire :::: DA RENDERE PARAMETRICO 

                $batch_config = [
                    [
                        'engineType' => 'converter',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//00_INPUT/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'dryRun' => $dryRun_converter
                    ],
                    [
                        'engineType' => 'cleaner',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'dryRun' => $dryRun_cleaner
                    ],

                    
                    [
                        'engineType' => 'analyzer',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/",
                        'dryRun' => $dryRun_analyzer
                    ],
                    

                    // TODO REPORT action ....

                ];
/*
                $folder_00 = "NER_BATCH/" . $batch_uuid . "//00_INPUT/";
                $folder_01 = "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/";
                $folder_02 = "NER_BATCH/" . $batch_uuid . "//02_CLEANER/";
                $folder_03 = "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/";
                $folder_04 = "NER_BATCH/" . $batch_uuid . "//04_REPORT/";
*/

                // array che contengono i job da generare ATTENZIONE si devono attendere la fine dei job precedenti
                // per gli input dei successivi ....

                $job_converter_list = [];
                $job_cleaner_list = [];
                $job_analyzer_list = [];
                $job_report_list = [];

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

                        // Per ogni azione del batch recupera i motori / engines ON!
            
                        foreach($batch_config as $b_c) 
                        {
                            Log::debug('##################   Build Job for', [$b_c['engineType'] , $b_c] );
                            
                            $job_list[$b_c['engineType']] = [];
                            # $files = Storage::files($b_c['fileFolderIn']);
                            $engines = CodaConfig::where(['type' => $b_c['engineType']])
                            ->where(['enable' => 'ON'])->get();
                            
                            
                            foreach($engines as $c)
                            {
                                Log::debug('engine:', [$c] );
                                
   
    
                                $engine_options = json_decode($c->options);

                                Log::debug('engine:options:', [$engine_options] );
                            
                                $batch_id = $batch_uuid;
    
                                // Prepara il JOB
                                
                                $job_id = [];
                                $job_id['description'] = 'API';
                                $job_id['type'] = $c->type;
                                $job_id['engine'] = $c->engine;
                                $job_id['engine_version'] = $c->engine_version;
                                $job_id['batch_uuid'] = $batch_id;
                                $job_id['api_url'] = $c->api;
                                $job_id['status_url'] = $c->api_status;
                                
            
                                $options = [
                                    'dryRun' => $b_c['dryRun'],
                                    'method' => $engine_options->method, //
                                    'contentType' => 'text/plain',
                                    'headers' => [
                                        'application/pdf'
                                    ],
                                    'fileInput' => $b_c['fileFolderIn'],
                                    'fileOutput' => $b_c['fileFolderOut'],
                                    'preAction' => $engine_options->preAction ?? false,
                                    'postAction' => $engine_options->postAction ?? false,
                                    'inType' => $engine_options->inType ?? "TXT",
                                    'outType' => $engine_options->outType ?? false
                                ];

                                $job_id['options'] = json_encode($options);
                
                                Log::channel('stack')->debug('WK_BATCH:job_id', [$job_id]);
                
                                $job_list[$b_c['engineType']][] = new ApiJobFolder($job_id);
            
                                Log::channel('stack')->debug('QMGR add Job to queue', [$b_c['engineType'], $job_id]);

                            }
                        }

        
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
// DA TOGLIERE                    $job_list['cleaner'],
// DA TOGLIERE                    $job_list['analyzer'],
                    
                    
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


            case "EMULATOR":

                // APIJOBFOLDER BATCH EMULATOR

                /*
                    engine : 
                    type :
                    engine_version :
                    fileIn : 
                    prompt : 
                    status : 
                    filename : 
                    res_output : 
                    output : 

                */



                $batch_uuid = "BATCH____1731583937171";
                $dryRun_converter = false;
                $dryRun_cleaner = false;
                $dryRun_analyzer = false;
                $HTTP_TIMEOUT_SEC = 180;

                // configura le esecuzioni dei batch secondo la tipologia

                $batch_config = [
                    
                    [
                        'engineType' => 'converter',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//00_INPUT/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'dryRun' => $dryRun_converter
                    ],
                    
                    
                    [
                        'engineType' => 'cleaner',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//01_CONVERTER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'dryRun' => $dryRun_cleaner
                    ],
                    
                    
                    [
                        'engineType' => 'analyzer',
                        'fileFolderIn' => "NER_BATCH/" . $batch_uuid . "//02_CLEANER/",
                        'fileFolderOut' => "NER_BATCH/" . $batch_uuid . "//03_ANALYZER/",
                        'dryRun' => $dryRun_analyzer
                    ],
                    

                ];


                foreach($batch_config as $b_c) 
                {
                    Log::debug('----------------------------------------------------------------', [] );
                    Log::debug('EMULATOR', [$b_c['engineType'] , $b_c['fileFolderIn']] );
                    Log::debug('----------------------------------------------------------------', [] );
                    
                    // $job_list[$b_c['engineType']] = [];
                    # $files = Storage::files($b_c['fileFolderIn']);

                    // recupera tutti i motori di un certo tipo ed abilitati
                    $engines = CodaConfig::where(['type' => $b_c['engineType']])->where(['enable' => 'ON'])->get();
                    

foreach($engines as $c)
{
                        
                Log::debug('APIJOBFOLDER:-----------------------------------------', [] );
                Log::debug('APIJOBFOLDER:type           :', [$c->type] );
                Log::debug('APIJOBFOLDER:engine         :', [$c->engine] );
                Log::debug('APIJOBFOLDER:engine_version :', [$c->engine_version] );

                // recupero parametri 

                $fileFolderIn = $b_c['fileFolderIn'];
                $fileFolderOut = $b_c['fileFolderOut'];
             
                $options = json_decode($c->options);
                
                $inputTag = $options->inputTag ?? false; // tag che diventerà l'input per il motore successivo
                $outputTag = $options->outputTag ?? false;

                $promptTxt = $options->promptTxt ?? false;
                $promptTag = $options->promptTag ?? false;
                $model = $options->model ?? false;

                $type = $c->type;
                $engine = $c->engine;
                $engine_version = $c->engine_version;
                $api_url = $c->api;
                $method = $options->method;
                $contentType = $options->contentType ?? false;
                $inType = $options->inType ?? "json";

                Log::debug('APIJOBFOLDER:url        :', [$api_url] );
                Log::debug('APIJOBFOLDER:method     :', [$method] );
                Log::debug('APIJOBFOLDER:contentType:', [$contentType] );
                Log::debug('APIJOBFOLDER:inputTag   :', [$inputTag] );
                Log::debug('APIJOBFOLDER:outputTag  :', [$outputTag] );
                Log::debug('APIJOBFOLDER:promptTxt  :', [$inputTag] );
                Log::debug('APIJOBFOLDER:promptTag  :', [$outputTag] );
                Log::debug('APIJOBFOLDER:model      :', [$model] );
                Log::debug('APIJOBFOLDER:inType     :', [$inType] );

                // ricerca i file di tipo $inType ....

                $files = collect(Storage::files($fileFolderIn))->filter(function ($file) use ($inType) {
                    return (pathinfo($file, PATHINFO_EXTENSION) === $inType);
                });

                // file da elaborare    

                Log::debug('APIJOBFOLDER:files to analyze :', [$files] );
                
                foreach($files as $fname)
                {
                    
                    $fileIn = $fname;
                    $ts = Carbon::now()->format('Y_m_d_H_i_s_u');
                    $fileOut = $fileFolderOut . $engine . '-' . $engine_version . '-' . $ts  .  ".json";

                    Log::debug('APIJOBFOLDER:fileIn :', [$fileIn] );
                    Log::debug('APIJOBFOLDER:fileOut:', [$fileOut] );

                    $j_output = [];
                    
                    $j_output['engine'] = $engine;
                    $j_output['type'] = $type;
                    $j_output['engine_version'] = $engine_version;
                    $j_output['fileIn'] = $fileIn;

                    $path_parts = pathinfo($fileIn);
                    
                    $statusCode = 123;
                    $statusDescription = 'Success!';
                    $statusBody = '';

                    // SOLO SE IL FILE INPUT è JSON ESEGUE IL CONTROLLO dello status

                    if (str_ends_with($fileIn, 'json')) {
                        Log::debug('APIJOBFOLDER:status check!', [] );

                        $jj_tmp = Storage::get($fileIn);
                        $jj = json_decode($jj_tmp);
                        $sourceStatusCode = $jj->{"status"};
                        Log::debug('APIJOBFOLDER:status check::-->', [$sourceStatusCode] );
        
                        if ($sourceStatusCode != 200) {
                            $method = "SOURCE_CODE_STATUS_ERROR";
                            Log::error('APIJOBFOLDER:previous status code ERROR!', [$sourceStatusCode, $method] );
                        }

                    } else {
                        Log::debug('APIJOBFOLDER:SKIP! status check!', [] );
                    }

                // Controllail tag status del json verificare che la chiamata precedente sia OK
             
                // inputTag esiste si estrae il contenuto dal json, altrimenti tutto il file ...
                if($inputTag) {
                    Log::debug('APIJOBFOLDER:inputTag', [$inputTag] );
                    $j_tmp = Storage::get($fileIn);
                    $j = json_decode($j_tmp);
                    $content2analyze = $j->{$inputTag};
                } else {
                    $content2analyze = Storage::get($fileIn);    
                }


                // Se esiste il TAG prompt (Txt e Tag)  bisogna creare un prompt ...
                if ($promptTxt && $promptTag ) {
                    
                    Log::debug('APIJOBFOLDER:Make prompt', [$promptTxt] );
                    
                    /*
                            {
                            "model": "llama3.2",
                            "prompt": "Why is the sky blue?",
                            "stream": false
                            }'
                    */
                    
                    // str_replace("world", "Peter", "Hello world!"); // Outputs: Hello Peter!
                    $prompt = str_replace($promptTag, $content2analyze, $promptTxt);
                    Log::debug('APIJOBFOLDER:PROMPT!', [$prompt] );
        
                    $data = [];
                    $data['model'] = $model;
                    $data['prompt'] = $prompt;
                    $data['stream'] = false;
        
                    $content2analyze =  json_encode($data);
                    $j_output['prompt'] = $content2analyze;

                    Log::debug('APIJOBFOLDER:PROMPT!', [$content2analyze] );

                }

    // Preparati tutti i dati esegue la chiamata al motore

    try {

                Log::debug('APIJOBFOLDER:try method:', [$method] );

                if($method == "POST") {
                    // $response = Http::withBody( $content2analyze, $contentType )->post($api_url);    
                    // ContentType ???

                    Log::debug('APIJOBFOLDER:POST_FORM: ', [$path_parts['basename'], $HTTP_TIMEOUT_SEC]);
                    $response = Http::timeout($HTTP_TIMEOUT_SEC)->withBody( $content2analyze)->post($api_url);    
                    $statusBody = $response->body(); 
                    $statusCode = $response->status();

                } elseif ($method == "POST_FORM") {
    
                    Log::debug('APIJOBFOLDER:POST_FORM: ', [$path_parts['basename']]);

                    $response = Http::attach(
                        'file', // Nome del parametro file nella richiesta
                        $content2analyze, // Contenuto del file
                        $path_parts['basename'] // Nome del file inviato
                    )
                    ->post($api_url);

                    $statusBody = $response->body(); 
                    $statusCode = $response->status();

                } elseif ($method == "PUT") {
                    $response = Http::withBody(
                        $content2analyze, 'application/pdf'
                    )->put($api_url);    
                    $statusBody = $response->body(); 
                    $statusCode = $response->status();
                } elseif ($method == "GET") {
                    $response = Http::get($api_url, []);
                    $statusBody = $response->body(); 
                    $statusCode = $response->status();
                } elseif ($method == "SOURCE_CODE_STATUS_ERROR") {
                    $statusBody = '{"response": "SOURCE_CODE_STATUS_ERROR"}'; 
                    $statusCode = 500;
                } else {
                    $statusBody = 'METHOD! NOT! FOUND!'; 
                    $statusCode = 998;
                    $statusDescription = 'METHOD! NOT! FOUND!';
                    Log::error('APIJOBFOLDER:METHOD! NOT! FOUND!:', [] );
                }

                // Log::debug('ApiJob->response:', [$response] );
             
                $j_output['status'] = $statusCode;
                Log::debug('APIJOBFOLDER:response->statusCode:', [$statusCode] );

                $j = json_decode($statusBody);
                Log::debug('APIJOBFOLDER:response->statusBody:', [$statusBody] );

                // dd($j->{"X-TIKA:content"});

                $j_output['res_output'] = json_decode($statusBody);
               
                // se esiste outputTag viene estratto dalla risposta ed inserito nel file di output

                if($outputTag) {
                    Log::debug('APIJOBFOLDER:outputTag:', [$outputTag] );
                    $j_output['output'] = $j->{$outputTag};
                } else {
                    $j_output['output'] = $j;
                }
                

                Log::channel('stack')->debug('*************************************:', []);    
                Log::channel('stack')->debug('',[$j_output['output']]);    
                Log::channel('stack')->debug('*************************************:', []);    
              

                // Log::debug('ApiJob->response:', [$response->body()] );
            
              
            }  catch (\Exception $e) {
                    Log::channel('stack')->error('--------------------------------------------------------:', [$e->getMessage()]);    
                    Log::channel('stack')->error('--------------------------------------------------------:', [$e->getMessage()]);    
                    Log::channel('stack')->error('ApiJobFolder APIJOBFOLDER:', [$e->getMessage()]);    
                    Log::channel('stack')->error('--------------------------------------------------------:', [$e->getMessage()]);    
                    Log::channel('stack')->error('--------------------------------------------------------:', [$e->getMessage()]);    
                    
                    $j_output['status'] = '500';
                    $j_output['output'] = $e->getMessage();
            }


            Storage::write($fileOut, json_encode($j_output));
            Log::debug('APIJOBFOLDER:fileOut:created!:', [$fileOut] );


            

                }

}

                }

                // Log::debug('ApiJob->response:', [$response->body()] );

        

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
