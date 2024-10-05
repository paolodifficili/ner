
php artisan migrate

# API Route
php artisan route:clear
php artisan route:list


# QUEUE
php artisan make:queue-table
php artisan migrate


# Struttura del lavoro su file system

- Upload - OK!
- Conversione / Batch / Motori
- Generazione Output risultato


- /UUID/Source si trovano il pdf e la conversione in testo
- /UUID/TAG-NER si trovano gli output dei vari motori di ricerca
- /UUID/Ouput si trova al combinazione dei vari output gli output dei vari motori di ricerca

# CONFIG su tabella DB

type : folder, ner, converter,
engine : tipo di configurazione
engine_version : info
api : 
api_status : da richiamare per una verifica del funzionamento (ner,coverter,folder) 
api_service : per utilizzare il servizio

CheckConfigJob esegue un test della configurazione


## Workflow

/upload OK MuxController.php


CMD
D:\mariadb-11.5.2-winx64\RUN_MYSQL.bat

CMD
D:\PROGETTI\LARAVEL>START_ENV_SET_PATH.bat
D:\PROGETTI\LARAVEL>ner_app\php artisan queue:work

CMD
D:\PROGETTI\LARAVEL>ner_app\php artisan queue:work


# Post di un batch
- Upload del file con creazione del batch Id
- Creazione del batch : 
      . selezione file + 
      . selezione dell'azione
      . selezione motori di analisi
      . salvataggio

- Visualizzazione stato di elaborazione di un batch 
    . avvio elaborazione
    . visualizzazione dei risultati
    . cancellazione dei dati
 
- Esecuzione dei valori
-


# https://github.com/paolodifficili/ner.git

# CLONE TO GITHUB

git add .
git commit -m "%DATE%-%TIME%"
git push -u -f origin master

# Laravel Migration
php artisan make:migration create_TABLENAME_table
-- edit file
php artisan migrate


---

Creazione di un batch

- Creazione di una cartella (NER_BATCH/$batch_id)
- Inserimento di tutti i file di lavoro
- Una sotto cartella per il lavoro e preparazione

ApiJob da options riceve tutte le indicazioni 
    method, 
    contentType
    input (fileId/name)
    output (fileId/name)
    per interagire con un servizio api


FileJob


# Fpdi composer install laravel  - composer require setasign/fpdi