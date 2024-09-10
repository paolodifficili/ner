<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NER Services
    |--------------------------------------------------------------------------
    |
    | List endpoint of ner services
    |
    */

    'root_folder' => 'NER_DATA',

    'tika' => [
        'status_url' => 'http://127.0.0.1:8080/status',
        'upload_url' => 'http://127.0.0.1:8080/status',
        'convert_url' => 'http://127.0.0.1:8080/status',
    ],


    'spacy_ner' => [
        'status' => 'https://jsonplaceholder.typicode.com/albums/2',
        'api' => 'https://jsonplaceholder.typicode.com/albums/2',
        'convers' => 'https://jsonplaceholder.typicode.com/albums/2',
    ],

    'spacy_regexp' => [
        'status' => 'https://jsonplaceholder.typicode.com/albums/3',
        'api' => 'https://jsonplaceholder.typicode.com/albums/3',
        'convers' => 'https://jsonplaceholder.typicode.com/albums/3',
    ],

    'hf01' => [
        'status' => 'http://127.0.0.1:8080/status',
        'upload' => 'http://127.0.0.1:8080/status',
        'convers' => 'http://127.0.0.1:8080/status',
    ],



];
