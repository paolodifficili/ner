<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QUEUE MANAGER</title>

        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>



        <!-- Fonts -->
    </head>
    <body>



    <div class="container my-5">

        UPLOAD
       

        <input id="picker" type="file" />

        <div id="myProgress"><div id="myBar"></div></div>

        <script>

        const picker = document.getElementById('picker');
        const myBar = document.getElementById("myBar");


        picker.onchange = () => {

        console.log('Picker OnChange!');
        console.log(picker.files[0]);

        myBar.style.width = "0" + "%";
        
        const myHeaders = new Headers();
        myHeaders.append("X-UP-FILENAME", picker.files[0].name);
        myHeaders.append("X-UP-UUID", Date.now());


        const ea = {
            'X-UP-FILENAME': picker.files[0].name,
            'X-UP-UUID': Date.now(),
        };

        

        console.log(ea);

        const upload = UpChunk.createUpload({
            endpoint: '/api/mux',
            headers : ea,
            file: picker.files[0],
            chunkSize: 2048, //  30720 Uploads the file in ~30 MB chunks
        });

        // subscribe to events
        upload.on('error', (err) => {
            console.error('💥 🙀', err.detail);
        });

        upload.on('progress', (progress) => {
            console.log(`So far we've uploaded ${progress.detail}% of this file.`);
            myBar.style.width = progress.detail + "%";
        });

    
        upload.on('success', () => {
            console.log("Wrap it up, we're done here.");
        });
        };

</script>

    </div>

    </body>
</html>