<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\CodaConfig;

use App\Models\CodaFile;

class MuxController extends Controller
{
    // $uploadFolder = './final_destination/'; 
    // $logsFolder = './logs/';
    // $chunksFolder = './chunks/';
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::channel('stack')->info('MuxController:index:', [] );
        return "MUX INDEX";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::channel('stack')->info('MuxController:create:', [] );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        Log::channel('stack')->info('MuxController:store:', [] );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        Log::channel('stack')->info('MuxController:show:', [] );
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

        $config = CodaConfig::where([
            'type' => 'folder',
            'engine' => 'upload_folder',
        ])->first();

        
        $uploadFolder = $config->api;


        Log::channel('stack')->info('MuxController:uploadFolder:', [$uploadFolder] );

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
    
        $chunkDest = $uploadFolder . "/" . $uuid . "/" .  $startSize;
        Log::info('MuxController write to :', [$chunkDest] );
        Storage::put($chunkDest, $bodyContent);
   

        if ( ($endSize+1) == $totalSize) {
            
            $filePath = $uploadFolder . "/" . $uuid;
            $fileDest = $uploadFolder . "/" . $uuid . "/" . $fileName;

            Log::info('MuxController write merge & clean :', [$fileDest] );
            
            $chunk_list = Storage::files($uploadFolder . "/" . $uuid);
            Log::channel('stack')->info('MuxController chunk list:', [$chunk_list]);

            $stringToRemove = $uploadFolder . "/" . $uuid . "/";

            $onlyFileName = array_map(function($item) use ($stringToRemove) {
                // Rimuovi la stringa dall'inizio (se presente)
                $item = preg_replace('/^' . preg_quote($stringToRemove, '/') . '/', '', $item);
                
                // Converti l'elemento in double
                return intval($item);
            }, $chunk_list);
            
            // Ordina l'array in maniera crescente
            sort($onlyFileName);

            Log::channel('stack')->info('MuxController RBUILD! list:', [$onlyFileName]);

            $fname = $uploadFolder . "/" . $uuid . "/" . $fileName;

            foreach($onlyFileName as $item ) {

                // read contents
                $file2get = $uploadFolder . "/" . $uuid . "/" . $item;
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

            $size = Storage::size($fname);
            $mime = Storage::mimeType($fname);

            // Update file list

            $info = [
                'file_uuid' => $uuid,
                'file_name' => $fileName,
                'file_size' => $size,
                'file_mime' => $mime,
                'file_path' => $filePath,
                'file_extension' => '',
                'file_root_path' => $uploadFolder
            ];

            $cf = CodaFile::create($info);
        

            // delete Chunk ....
            
            foreach($onlyFileName as $item ) {

                // read contents
                $file2delete = $uploadFolder . "/" . $uuid . "/" . $item;
                Log::channel('stack')->info('MuxController remove:', [$file2delete]);
                Storage::delete($file2delete);
                
            }

            // mergeChunks( $uuid, $chunksFolder, $uploadFolder, $fileName );
            
            return response()->json([
                'action' => 'upload',
                'info' => $cf,
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