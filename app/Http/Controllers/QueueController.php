<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Coda;
use App\Models\CodaBatch;
use App\Models\Config;

use Illuminate\Support\Facades\Validator;

use App\Http\Requests\StoreBatchRequest;

use App\Jobs\CheckConfigJob;

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
        $config = Config::where([
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
        $coda = Coda::all();
        $codaJson = json_encode($coda);

        return response()->json($coda);
        // return $codaJson;
        
    }

    public function showBatchAction()
    {
        Log::channel('stack')->info('QueueController:showBatchActions:', [] );
        $coda = [
            [
                "id" => 0,
                "action" => "CHECK_CONFIG",
            ],

            [
                "id" => 1,
                "action" => "RUN_ENGINE",
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
            $jobs = Coda::where(['batch_uuid' => $batchId])->get();

            $out = [
                'batch_info' => $batch,
                'batch_jobs' => $jobs,
            ];
    
        } else {
            $batch = CodaBatch::all();
            $jobs = [];

            $out = $batch;
        }

    

        
        
        // $codaJson = json_encode($coda);

        Log::channel('stack')->info('QueueController:showBatch:', [$jobs, $batch] );

      
        return response()->json($out);

        // return $codaJson;
    }


    public function storeBatch(StoreBatchRequest $request)
    {
      
        $details =[
            'batch_uuid' => $request->batch_uuid,
            'batch_description' => $request->batch_description,
            'batch_action' => $request->batch_action,
            'batch_options' => $request->batch_options,
        ];

        Log::channel('stack')->info('QueueController:storeBatch:', [$details] );

        $cb = CodaBatch::create($request->validated());

        Log::channel('stack')->info('QueueController:storeBatch:', [$cb] );
        
        $resp = [
            'success' => true,
            'data'    => $cb
        ];        

        return response()->json($resp);
        // return $codaJson;
    }

/*

        **************** MGR **********************


*/


    public function mgrBatch(Request $request)
    {
      
        Log::channel('stack')->info('QueueController:mgrBatch:', [$request->all() , $request->has('QMGR_ACTION')] );

        $status = 200;
        $status_action = 'NO ACTION';
        $data = [];

        if ($request->has('QMGR_ACTION')) {

            $QMGR_ACTION = $request->input('QMGR_ACTION');

            switch ($QMGR_ACTION) {

                case 'TEST_ACTION':
                    
                    Log::channel('stack')->info('QueueController:mgrBatch:', [$QMGR_ACTION] );
                    $status_action = 'Batch TEST_ACTION submitted';
                
                break;
                

                case 'CHECK_CONFIG':

                    /* build a batch to check configuration */

                    $faker = Faker::create('SeedData');

                    $batch_id = 'BATCH' . $faker->numberBetween($min = 1, $max = 2000);
                    $batch_uuid = $batch_id;
                    $batch_description = $batch_id;
                    $batch_options = ['start_at' => Carbon::now()];

                    $data =[
                        'batch_uuid' => $batch_id,
                        'batch_description' => $batch_id,
                        'batch_options' => json_encode($batch_options),
                    ];
            
                    Log::channel('stack')->info('QueueController:storeBatch:', [$data] );
            
                    $cb = CodaBatch::create($data);
                
                    
                    // recupera tutte le configurazioni e per ognuna esegue il test
            
                    $job_list = [];

                    $coda = Config::all();

                    foreach ($coda as $c) {

                        $job_id = [];
                        $job_id['description'] = 'CHECKCONFIG';
                        $job_id['type'] = $c->type;
                        $job_id['engine'] = $c->engine;
                        $job_id['batch_uuid'] = $batch_id;
            
                        Log::channel('stack')->info('QueueController:mgrBatch', [$job_id]);
                        CheckConfigJob::dispatch($job_id);

                        $job_list = $job_id;
                    }
            
                    $data = [
                        "batch_uuid" => $batch_id,
                        "job_list" => $job_list
                    ];

                    $status_action = 'Batch submitted';
                break;
                
                default:
                    Log::channel('stack')->error('QueueController:mgrBatch', ['ERROR!']);
                    $status = 501;
                    $status_action = 'NO QMGR_ACTION FOUND!';
                break;
            }



            /*
            $input = [
                'user' => [
                    'name' => 'Taylor Otwell',
                    'username' => 'taylorotwell',
                    'admin' => true,
                ],
            ];
            Validator::make($input, [
                'user' => 'array:name,username',
            ]);
            */


            $out = [
                'action' => $QMGR_ACTION,
                'status' => $status_action
            ];


            $out = array_merge($out, $data);
            
        } else {
            Log::channel('stack')->error('QueueController:mgrBatch:', ['NO_ACTION'] );
            $out = ['message' => 'QMGR_ACTION not found'];
            $status = 501;
        }


        return response()->json($out, $status);
        
        // return $codaJson;
    }


    public function showConfig()
    {
        Log::channel('stack')->info('QueueController:index:', [] );
        $coda = Config::all();
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::channel('stack')->info('QueueController:create:', [] );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        Log::channel('stack')->info('QueueController:store:', [] );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        Log::channel('stack')->info('QueueController:show:', [] );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Log::channel('stack')->info('MuxController:destroy:', [] );
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