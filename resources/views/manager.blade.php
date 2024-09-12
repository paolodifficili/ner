<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>QUEUE MANAGER</title>

        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <link href="https://unpkg.com/tabulator-tables@6.2.5/dist/css/tabulator.min.css" rel="stylesheet">
        <script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.2.5/dist/js/tabulator.min.js"></script>

    </head>
    
<body onload="onLoadFn()">

    <div class="container my-5">
   
        <h3>Config:</h3>
        <div id="config-table"></div>
        <div id="example-table"></div>
        <button id="ajax-trigger">Load Data via AJAX</button>
        <pre id="output"></pre>

    </div>

    <script>

    var configTable = new Tabulator("#config-table", {
        height:"311px",
        selectableRows:1, //make rows selectable
        columns:[
        {title:"id", field:"id"},
        {title:"uuid", field:"uuid"},
        {title:"engine", field:"engine"},
        {title:"engine_version", field:"engine_version"},
        {title:"api", field:"api"},
        {title:"api_status", field:"api_status"},
        {title:"api_config", field:"api_config"},
        ],
    });

    


    var table = new Tabulator("#example-table", {
        height:"311px",
        selectableRows:1, //make rows selectable
        columns:[
        {title:"id", field:"id"},
        {title:"created_at", field:"created_at"},
        {title:"uuid", field:"uuid"},
        {title:"uuid_internal", field:"uuid_internal"},
        {title:"batch_uuid", field:"batch_uuid"},
        {title:"file", field:"file"},
        ],
    });




table.on("rowClick", function(e, row){
var rowData = row.getData();
console.log(rowData);
document.getElementById("output").textContent = JSON.stringify(rowData.uuid, null, 2); // Stampa i dati

document.getElementById("output").textContent += "\nMARIO!";
//e - the click event object
//row - row component
});


function onLoadFn()  {
console.log('onLoadFn');
configTable.setData("/api/config/show");
table.setData("/api/coda/show");

}
</script>


    </body>
</html>
