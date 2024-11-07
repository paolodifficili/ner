<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;


use App\Models\CodaJob;
use App\Models\CodaBatch;
use App\Models\CodaConfig;
use App\Models\CodaFile;

use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Requests\StoreBatchRequest;

use App\Jobs\CheckConfigJob;
use App\Jobs\ApiJob;
use App\Jobs\SendMessage;

use App\Events\GotMessage;

use Faker\Factory as Faker;
use Carbon\Carbon;

class QueueController extends Controller
{
    // $uploadFolder = './final_destination/'; 
    // $logsFolder = './logs/';
    // $chunksFolder = './chunks/';
   
    /**
     * Display a listing of the resource.
     */

    public function broadcastMessage(Request $request): JsonResponse {

        Log::channel('stack')->info('QueueController:broadcast GotMessage:', [] );

        $msg = "MSG_AT_" . Carbon::now();

        broadcast(new GotMessage([
            'action' => 'demo_action',
            'status' => 'ok',
            'message' => $msg,
        ]))->toOthers();
        
        
        return response()->json([
            'success' => true,
            'message' => "Message broadcasted!.",
        ]);

    }

    // Crea il job che esegue la notifica
    public function createMessage(Request $request): JsonResponse {

        Log::channel('stack')->info('QueueController:createMessage:', [] );
        
        $message = [
            'user_id' => 'USER_ID',
            'text' => 'TEXT_MESSAGE!',
        ];

        SendMessage::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => "Message created and job dispatched.",
        ]);
    }

    public function submitCheckConfig()
    {
        Log::channel('stack')->info('QueueController:submitCheckConfig:', [] );
        $faker = Faker::create('SeedData');
        $batch_id = 'BATCH' . $faker->numberBetween($min = 1, $max = 2000);

        // recupera tutte le configurazioni e per ognuna esegue il test

        $coda = Config::all();
        foreach ($coda as $c) {
            $job_id = [];
            $job_id['description'] = 'CHECKCONFIG';
            $job_id['type'] = $c->type;
            $job_id['engine'] = $c->engine;
            $job_id['batch_uuid'] = $batch_id;

            Log::channel('stack')->info('QueueController:submitCheckConfig', [$job_id]);
            CheckConfigJob::dispatch($job_id);
        }

        return response()->json([
            "status" => 200,
            "batch_uuid" => $batch_id,
        ]);

    }

    public function showUploadFileList()
    {
        Log::channel('stack')->info('QueueController:showUploadFileList:', [] );
        $coda = CodaFile::all();
        // $codaJson = json_encode($coda);
        return response()->json($coda);
    }


    // list file from folder
    public function showUploadFileList_OLD()
    {
        $config = CodaConfig::where([
            'type' => 'folder',
            'engine' => 'upload_folder',
        ])->first();
        
        $uploadFolder = $config->api;

        Log::channel('stack')->info('showUploadFileList:index:', [$uploadFolder] );

        $file_list = Storage::allFiles($uploadFolder);
        Log::channel('stack')->info('showUploadFileList chunk list:', [$file_list]);
        return response()->json($file_list);
        // return $codaJson;
    }


    public function showCoda()
    {
        Log::channel('stack')->info('QueueController:index:', [] );
        $coda = CodaJob::all();
        $codaJson = json_encode($coda);

        return response()->json($coda);
        // return $codaJson;
        
    }

    public function showBatchAction()
    {
        Log::channel('stack')->info('QueueController:showBatchActions:', [] );
        $coda = [
            [
                "id" => "CHECK_CONFIG",
                "value" => "CHECK_CONFIG",
            ],

            [
                "id" => "RUN_ENGINE",
                "value" => "RUN_ENGINE",
            ],

            [
                "id" => "DRY_RUN",
                "value" => "DRY_RUN",
            ],

        ];
        $codaJson = json_encode($coda);

        return response()->json($coda);
        // return $codaJson;
        
    }

    /**
     * 
     *  ********* BATCH **********************
     * 
     */

    // mostra le info di un batch o la lista dei batch

    public function showBatch(String $batchId = null)
    {
        Log::channel('stack')->info('QueueController:showBatch:', [$batchId] );
     
        if($batchId) {
            $batch = CodaBatch::where(['batch_uuid' => $batchId])->get();
            $jobs = CodaJob::where(['batch_uuid' => $batchId])->get();

            $out = [
                'batch_info' => $batch,
                'batch_jobs' => $jobs,
            ];
    
        } else {
            // $batch = CodaBatch::all();
            $batch = CodaBatch::orderBy('id','desc')->get();
            $jobs = [];
            $out = $batch;
        }


        
        // $codaJson = json_encode($coda);

        Log::channel('stack')->info('QueueController:showBatch:', [$jobs, $batch] );

      
        return response()->json($out);

        // return $codaJson;
    }



    // Salva un nuovo batch
    public function storeBatch(StoreBatchRequest $request)
    {

        // Crea il batch su db
        $details =[
            'batch_uuid' => $request->batch_uuid,
            'batch_description' => $request->batch_description,
            'batch_action' => $request->batch_action,
            'batch_options' => $request->batch_options,
        ];

        Log::channel('stack')->info('QueueController:storeBatch:', [$details] );

        $cb = CodaBatch::create($request->validated());

        Log::channel('stack')->info('QueueController:storeBatch:', [$cb] );

        // Create la cartella di lavoro corrispondente
        $batch_folder = "NER_BATCH/" . $request->batch_uuid;
        Storage::makeDirectory($batch_folder);

        // Copia il/i file di input nella cartella INPUT
        $ops = json_decode($request->batch_options);
        Log::channel('stack')->info('QueueController:storeBatch:', [$ops] );

        $f2conv = [];

        // se vi sono file selezionati dalla lista
        if ( $ops->files_selected ?? false ) {

            foreach($ops->files_selected as $fId) {
                Log::channel('stack')->info('QueueController:fId:', [$fId] );
                $f = CodaFile::find($fId);
    
                Log::channel('stack')->info('QueueController:f:', [$f] );
    
                $oldFile = $f->file_path . "/" . $f->file_name;
                $newFile = $batch_folder . "/00_INPUT/" . $f->file_name;
    
                Log::channel('stack')->info('QueueController:copy:', [$oldFile, $newFile] );
    
                Storage::copy($oldFile, $newFile);
    
                $f2conv[] = $newFile;
            }

        }

        // se è usato uuid del file
        if ( $ops->files_uuid ?? false ) {

            foreach($ops->files_uuid as $fId) {

                Log::channel('stack')->info('QueueController:fId:', [$fId] );

                $f = CodaFile::where(['file_uuid' => $fId])->firstOrFail();
                    
                Log::channel('stack')->info('QueueController:f:', [$f] );
    
                $oldFile = $f->file_path . "/" . $f->file_name;
                $newFile = $batch_folder . "/00_INPUT/" . $f->file_name;
    
                Log::channel('stack')->info('QueueController:copy:', [$oldFile, $newFile] );
    
                Storage::copy($oldFile, $newFile);
    
                $f2conv[] = $newFile;
            }

            
        }

        

        // Esegue per ogni file attiva un JOB di conversione 
        // Log::channel('stack')->info('QueueController:TODOTODOconvesione???:',[]);

       
              
        $resp = [
            'success' => true,
            'data'    => $cb
        ];        

        return response()->json($resp);
        // return $codaJson;
    }



    // elimina le info di un batch o la lista dei batch

    public function deleteBatch(String $batchId = null)
    {
     Log::channel('stack')->info('QueueController:deleteBatch:', [$batchId] );
  
     if($batchId) {
         $batch = CodaBatch::where(['batch_uuid' => $batchId])->delete();
         $jobs = CodaJob::where(['batch_uuid' => $batchId])->delete();

         $out = [
             'message' => $batchId . " deleted!",
         ];
 
     } else {
         $out = [
            'message' => 'NO batchId',
        ];

     }
     
     // $codaJson = json_encode($coda);

     Log::channel('stack')->info('QueueController:deleteBatch:', [$jobs, $batch] );

   
     return response()->json($out);

     // return $codaJson;
 }


    // ------------------------------------------------------------------------------

    public function showJobAction(String $jobId = null)
    {
        Log::channel('stack')->info('QueueController:showJob:', [$jobId] );
     
        if($jobId) {
            // $batch = CodaBatch::where(['batch_uuid' => $jobId])->get();
            // $job = CodaJob::firstOrFail($jobId)->get();
            $job = CodaJob::find($jobId);

            $out = $job;
    
        } else {
            $out = [];
            // $batch = CodaBatch::all();
            // $jobs = [];
            // $out = $batch;
        }

        // $codaJson = json_encode($coda);
        // Log::channel('stack')->info('QueueController:showBatch:', [$jobs, $batch] );
     
        return response()->json($out);

        // return $codaJson;
    }











/*

        **************** MGR **********************

        // Exec batch actions
        // Submit Jobs
        // Reset 
        // Delete


*/

    public function mgrBatch(Request $request)
    {
      
        Log::channel('stack')->info('QueueController:mgrBatch:', [$request->all() , $request->has('QMGR_ACTION')] );

        $validatedData = $request->validate([
            'BATCH_UUID' => ['required'],
        ]);
        
        // TODO validate parameters
        Log::channel('stack')->info('QueueController:mgrBatch:', [$validatedData] );

        $status = 200;
        $status_action = 'NO ACTION';
        $data = [];
        $out = [];

        $batch_uuid = $request->input('BATCH_UUID');

        Log::channel('stack')->info('QueueController:mgrBatch:', [$batch_uuid] );

        $batch = CodaBatch::where(['batch_uuid' => $batch_uuid])->firstOrFail();

        $QMGR_ACTION = $batch->batch_action;
        
        Log::channel('stack')->info('QueueController:mgrBatch:ACTION', [$QMGR_ACTION] );
        
        
        // get batch info .....

        switch ($QMGR_ACTION) {

            case 'RUN_ENGINE':

                // Save batch start timestamp
                $batch->last_run_at = Carbon::now();
                $batch->save();

                // get batch options
                $bo = json_encode($batch->batch_options);

                                
                Log::channel('stack')->info('QueueController:mgrBatch:RUN:with:', [$bo] );

                // $bo->engines_selected;

                $status_action = 'Batch RUN_ENGINE submitted';
                $out = [
                    'message' => $status_action
                ];
                $status = 200;

            break;
            
            // controlla tutta la configurazione test dei servizi    
            case 'CHECK_CONFIG':

                $batch->last_run_at = Carbon::now();
                $batch->save();

                // recupera tutte le configurazioni e per ognuna esegue il test
        
                Log::debug('QueueController:mgrBatch:', ['CHECK_CONFIG'] );

                // $allConv = CodaConfig::where(['type' => 'converter'])->get();
                $allConv = CodaConfig::where(['id' => 44])->get();
                // $allConv = CodaConfig::all();

                // $batch_id = $request->batch_uuid;

                foreach($allConv as $c)
                {
                    Log::debug('QueueController:CHECK:engine:', [$c->type ,$c->engine] );
                    // se folder (controlla la scrittura su file ...) TODO
                    if ($c->type != 'folder') {


                        $job_id = [];
                        $job_id['description'] = 'CHECK CONFIG ' . $c->engine;
                        // $job_id['type'] = $faker->randomElement(['convert','hf','spacy']);
                        $job_id['type'] = $c->type;
                        $job_id['engine'] = $c->engine;
                        // $job_id['id'] = $faker->numberBetween($min = 1, $max = 2000);
                        // $job_id['id'] = $faker->uuid();
                        $job_id['id'] = 'JOB_AAA-BBB-CCCC';
                        $job_id['batch_uuid'] = $batch_uuid;
                        $job_id['api_url'] = $c->api_status;
                        $job_id['status_url'] = $c->api_status;
                
                        $options = [
                            'method' => 'GET',
                            'headers' => [
                                'application/pdf'
                            ],
                            'timeout' => '',
                            'query' => '',
                            'fileInput' => '',
                            'fileOutput' => '',
                        ];
                        $job_id['options'] = json_encode($options);
                
                        Log::channel('stack')->info('QueueController add API Job to queue', [$job_id]);
                
                        // ApiJob::dispatch($job_id);
                        $job_list[] = new ApiJob($job_id);
              
                    }
                }

                $batch = Bus::batch([
                    $job_list, 
                    // $job_cleaner_list
                    ])->before(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:before:', [$batch->id] );
                    // The batch has been created but no jobs have been added...
                })->progress(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:progress:', [$batch->id] );
                })->then(function (Batch $batch) {
                    Log::channel('stack')->info('*****WK_BATCH:then:', [$batch->id] );
                    // $batch->custom_data = $batch_uuid;
                })->catch(function (Batch $batch, Throwable $e) {
                    Log::channel('stack')->info('*****WK_BATCH:error:', [$batch->id, $e->getMessage()] );
                })->finally(function (Batch $batch) {

                    // dd($batch);

                    Log::channel('stack')->info('*****WK_BATCH:finally:', [$batch->id] );
                    Log::channel('stack')->info('*****WK_BATCH:finally:', [$batch->name] );

                    $msg = $batch->name . " Completato! ";

                    broadcast(new GotMessage([
                        'action' => 'CHECK_CONFIG',
                        'status' => 'ok',
                        'message' => $msg,
                    ]))->toOthers();

                })->name($batch_uuid)->dispatch();

                $out = [
                    'message' => 'CHECK_CONFIG :: all Job submitted : ' . $batch_uuid
                ];


            break;

            default:
                Log::channel('stack')->error('QueueController:mgrBatch', ['ERROR!']);
                $status = 501;
                $status_action = 'NO QMGR_ACTION FOUND!';
                $out = [
                    'message' => 'NO Action found!'
                ];
                
            break;


        }

        return response()->json($out, $status);

    }




    public function showConfig()
    {
        Log::channel('stack')->info('QueueController:index:', [] );
        $coda = CodaConfig::all();
        $codaJson = json_encode($coda);

        return response()->json($coda);
        // return $codaJson;
    }

    public function CheckConfig()
    {
        Log::channel('stack')->info('QueueController:index:', [] );
        $coda = Config::all();
        $codaJson = json_encode($coda);

        return response()->json($coda);
        // return $codaJson;
    }

    public function index()
    {
        Log::channel('stack')->info('QueueController:index:', [] );
        return "MUX INDEX";
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //Log::channel('stack')->info('MuxController:update:', [$request] );

        $fileName = $request->header('X-UP-FILENAME');
        $uuid = $request->header('X-UP-UUID');

        Log::channel('stack')->info('MuxController:update:', [$fileName, $uuid] );


        $config = Config::firstOrFail([
            'type' => 'folder',
            'engine' => 'upload_folder',
        ]);

        $uploadFolder = $config->api;

        Log::channel('stack')->info('MuxController:update:', [$uploadFolder] );

        $contentLength = $request->header('CONTENT-LENGTH');
        $contentType = $request->header('CONTENT-TYPE');
        $contentRange = $request->header('CONTENT-RANGE');
        $matches = [];
        preg_match('/bytes ([0-9]+)-([0-9]+)\/([0-9]+)/', $contentRange, $matches);
        // Array ( [0] => bytes 567-66775/6864680 [1] => 567 [2] => 66775 [3] => 6864680 )

        Log::channel('stack')->info('MuxController:update:', [$matches] );

        $startSize = $matches[1];
        $endSize = $matches[2];
        $totalSize = $matches[3];

        Log::channel('stack')->info('MuxController:update:', [$startSize, $endSize, $totalSize] );
      
        $bodyContent = $request->getContent();
    
        $chunkDest = $uuid . "/" .  $startSize;
        Log::info('MuxController write to :', [$chunkDest] );
        Storage::put($chunkDest, $bodyContent);
   

        if ( ($endSize+1) == $totalSize) {
            
            $fileDest = $uuid . "/" . $fileName;
            Log::info('MuxController write merge & clean :', [$fileDest] );
            
            $chunk_list = Storage::files($uuid);
            Log::channel('stack')->info('MuxController chunk list:', [$chunk_list]);

            $stringToRemove = $uuid . "/";

            $onlyFileName = array_map(function($item) use ($stringToRemove) {
                // Rimuovi la stringa dall'inizio (se presente)
                $item = preg_replace('/^' . preg_quote($stringToRemove, '/') . '/', '', $item);
                
                // Converti l'elemento in double
                return intval($item);
            }, $chunk_list);
            
            // Ordina l'array in maniera crescente
            sort($onlyFileName);

            Log::channel('stack')->info('MuxController RBUILD! list:', [$onlyFileName]);

            $fname = $uuid . "/" . $fileName;

            foreach($onlyFileName as $item ) {

                // read contents
                $file2get = $uuid . "/" . $item;
                Log::channel('stack')->info('MuxController read:', [$file2get]);
                $contents = Storage::get($file2get);

                if( $item == 0) {
                    Log::channel('stack')->info('MuxController write 2:', [$fname]);
                    Storage::put($fname, $contents);
                } else {
                    Log::channel('stack')->info('MuxController append 2:', [$fname]);
                    Storage::append($fname, $contents);
                }

            }

            Log::channel('stack')->info('MuxController RBUILDED!:', [$fname]);

        
            
            foreach($onlyFileName as $item ) {

                // read contents
                $file2get = $uuid . "/" . $item;
                Log::channel('stack')->info('MuxController remove:', [$file2get]);
                Storage::delete($file2get);
                
            }

            // mergeChunks( $uuid, $chunksFolder, $uploadFolder, $fileName );
            
            return response()->json([
                'action' => 'upload',
                'status' => 'ok',
            ]);
            
            
            // cleanChunks( $uuid, $chunksFolder );
            
        } else  {
            return response()->json([
                'action' => 'chunk_upload',
                'status' => 'ok',
            ]);
        }
        
       

        // $headers = request::header();
    }

  


    public function mergeChunks( $uuid, $pathChunk, $pathDest, $fileNameDest )
    {
        error_log("mergeChunks run: $uuid $pathChunk $pathDest $fileNameDest", 0);
    
        $chunkCount = 100;
    
        $fileDest_with_path = $pathDest . $fileNameDest;
    
        for ($i = 0; $i <= $chunkCount; $i++)
        {
             $fileNameToCheck = $uuid . "." . $i;
             $fileNameToCheck_with_path = $pathChunk . $fileNameToCheck;
    
             error_log("Read file_exists: $fileNameToCheck_with_path", 0);
    
            if (file_exists($fileNameToCheck_with_path)) 
            {
                error_log("Read chunk: $fileNameToCheck_with_path", 0);
                $fh = fopen( $fileNameToCheck_with_path, 'rb' );
                $chunkSize = filesize($fileNameToCheck_with_path);
                error_log("Size chunk: $chunkSize", 0);
                $buffer = fread( $fh, $chunkSize);
                fclose( $fh );
    
                error_log("Write chunk to: $fileDest_with_path", 0);
                $total = fopen( $fileDest_with_path , 'ab' );
                $write = fwrite( $total, $buffer );
                fclose( $total );
            }
        }
    
        $fileDestSize = filesize($fileDest_with_path);
        error_log("File dest complete $fileDest_with_path size: $fileDestSize", 0);
    
    }
    
    
    public function cleanChunks( $uuid, $pathChunk )
    {
        error_log("cleanChunks run: $uuid $pathChunk", 0);
        $files = glob($pathChunk . "$uuid.*"); // get all file names
        foreach($files as $file){ // iterate files
          if(is_file($file)) {
            error_log("delete  $file", 0);
            unlink($file); // delete file
          }
        }
    }
    
    // mergeChunks ( $saveChunksAs, $chunkCount, $chunkSize, $saveMergedAs );
    
    
    
    public function getChunkFileName($uuid, $cF)
    {
        for ($i=0; $i<100; $i++)
        {
            $fileNameToCheck = $uuid . "." . $i;
            if (!file_exists($cF . $fileNameToCheck)) 
            { 
                error_log("Not exists  $cF$fileNameToCheck", 0);
                return $fileNameToCheck; 
            } else {
                 error_log("File exists $cF$fileNameToCheck continue...", 0);
            }            
        }
    }


}