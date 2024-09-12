
php artisan migrate

# QUEUE
php artisan make:queue-table
php artisan migrate


# Struttura del lavoro su file system

 - Upload
 - Conversione / Batch / Motori
 - Generazione Output risultato


/UUID/Source si trovano il pdf e la conversione in testo
/UUID/TAG-NER si trovano gli output dei vari motori di ricerca
/UUID/Ouput si trova al combinazione dei vari output gli output dei vari motori di ricerca

# https://github.com/paolodifficili/ner.git

# CLONE TO GITHUB

git add .
git commit -m "%DATE%-%TIME%"
git push -u -f origin master