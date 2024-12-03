<script>
  export default {
    data() {
      return {


        batch_id : '',
        overlay: false,

        snackbar: false,
        txtSnackbar : '',


        batch_info: [],
        batch_jobs: [],
        batch_item: {},
        listItems: [],
        storageItems: [],

        file_list: [],
        config_list: [],
        action_list: [],
        reducedData: [],
        engines_selected: [],
        files_selected: [],
        action_selected: [],

      }
    },
    methods: {

      async getData(id) {

        this.overlay = true;  
        // const route = VueRouter.useRoute();

        console.log(id)
        const res = await fetch("/api/batch/" + id );
        const finalRes = await res.json();
        
        this.batch_jobs = finalRes.batch_jobs;
        this.batch_info = finalRes.batch_info;
        this.batch_item = finalRes.batch_info[0];

        console.log(this.batch_info);
        console.log(this.batch_jobs);
        console.log(this.batch_item);

        const res0 = await fetch("/api/batchStorage/" + id );
        const batchStorage = await res0.json(); 
        this.storageItems = batchStorage;

        console.log(this.storageItems);


        const res1 = await fetch("api/upload/list");
        this.file_list = await res1.json();

        const res2 = await fetch("api/config/list");
        this.config_list = await res2.json();

        const res3 = await fetch("api/action/list");
        this.action_list = await res3.json();

        // decode options 

        console.log(this.batch_item.batch_options);

        const ob = JSON.parse(this.batch_item.batch_options)

        console.log(ob);

        this.engines_selected =  ob.engines_selected;
        this.files_selected = ob.files_selected;
        this.action_selected =  ob.action_selected;


        const selectedProperties = ['id','batch_uuid', 'type', 'engine', 'status', 'status_description', 'api_url', 'status_url', 'last_run_at' ];

        // Riduzione dell'array di oggetti
        this.reducedData = this.batch_jobs.map(obj => {
            // Creiamo un nuovo oggetto con solo le proprietà desiderate
            const newObj = {};
            selectedProperties.forEach(prop => {
                if (obj.hasOwnProperty(prop)) {
                    newObj[prop] = obj[prop];
                }
            });
            return newObj;
        });

        console.log(this.reducedData);

        

        this.overlay = false;
      },

    handleReload() {
      console.log('Reload!');
      console.log(this.batch_id);
      this.getData(this.batch_id);
    },

    async handleDelete()  {
      console.log('handleDelete');
      console.log(this.batch_item.batch_uuid);

      const requestOptions = {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
      };
      const res = await fetch("/api/batch/" + this.batch_item.batch_uuid,  requestOptions );


      this.txtSnackbar = 'Batch deleted ! ' + this.batch_item.batch_uuid ;
      this.snackbar = true;
      
    }, 

    showJob(item) {
      console.log(item);
      this.$router.push('/job/' + item.id);
    },

    async handleRun() {
            // POST request using fetch with error handling

          
        console.log('handleRun');
        console.log(this.batch_item);
        console.log(this.batch_item.batch_uuid);
        
        const obj = {
           "BATCH_UUID" : this.batch_item.batch_uuid
        };

        console.log(obj);

        const requestOptions = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(obj)
        };

        this.overlay = true;
        try {
          const response = await fetch('/api/qmgr', requestOptions);
          if (!response.ok) {
            throw new Error('Errore nel recuperare i dati');
          }
          const jsonData = await response.json();
          console.log(jsonData);
           this.txtSnackbar = jsonData.message ;
           this.snackbar = true;
      
          } catch (err) {
            console.error(err.message);
            this.txtSnackbar = err.message ;
            this.snackbar = true;
            // this.error = err.message;
          } finally {
            this.overlay = false;
          }

        }
    
    /*
        fetch('/api/qmgr', requestOptions)
          .then(async response => {
                const data = await response.json();
                // check for error response
                if (!response.ok) {
                    // get error message from body or default to response status
                    const error = (data && data.message) || response.status;
                    return Promise.reject(error);
                }
                console.log(data);

                
                })
                .catch(error => {
                this.errorMessage = error;
                console.error('There was an error!', error);
                });
                
      },

      */
    },    

    mounted() {
      
      const route = VueRouter.useRoute();
      console.log(route.params.id);
      this.batch_id = route.params.id;
      this.getData(this.batch_id);
      
    }
  }
</script>


<template>

<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Batch info - {{ $route.params.id }} (batch_item.vue)</v-toolbar-title>
</v-toolbar>




  <v-text-field v-model="batch_item.id" label="ID"></v-text-field>
  <v-text-field v-model="batch_item.batch_uuid" label="batch_uuid"></v-text-field>
  <v-text-field v-model="batch_item.batch_options" label="batch_options"></v-text-field>

  <v-select
  v-model="action_selected"
  :items="action_list"
  item-title="value"
  item-subtitle="action"
  item-value="id"
  label="Select action"
  ></v-select>

  <v-select
  v-model="engines_selected"
  :items="config_list"
  item-title="engine"
  item-subtitle="type"
  item-value="id"
  label="Select Engine"
  multiple
  ></v-select>

  <v-select
  v-model="files_selected"
  :items="file_list"
  label="Select File"
  item-title="file_name"
  item-value="id"
  multiple
  persistent-hint
  ></v-select>


    <v-row justify="center">
      <v-col cols="12" md="4" sm="4">
        <v-btn  color="success" @click="handleDelete"  size="x-large" block>Elimina</v-btn>
      </v-col>

      <v-col cols="12" md="4" sm="4">
        <v-btn color="error"  @click="handleRun" size="x-large" block>Esegui</v-btn>
      </v-col>

     <v-col cols="12" md="4" sm="4">
        <v-btn color="warning"  @click="handleReload" size="x-large" block>Ricarica</v-btn>
      </v-col>

    </v-row>




<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Jobs list</v-toolbar-title>
</v-toolbar>


<v-data-iterator
    :items="storageItems"
    item-value="fileName"
  >
    <template v-slot:default="{ items, isExpanded, toggleExpand }">
      <v-row>
        <v-col
          v-for="item in items"
          :key="item.fileName"
          cols="12"
          md="12"
          sm="12"
        >
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon
                :color="(item.raw.fileContent.status != 200) ? 'red' : 'green'"
                size="18"
                start
              >mdi-check-circle</v-icon>
              ({{ item.raw.fileContent.status }})
              {{ item.raw.fileContent.type }}
              {{ item.raw.fileContent.engine }}
              {{ item.raw.fileContent.engine_version }}
              
            </v-card-title>
            <v-card-subtitle>
            file name : {{ item.raw.fileName }}
            </v-card-subtitle>


            
  <v-table theme="dark">
    <tbody>
      <tr><td>Engine name:</td><td>{{ item.raw.fileContent.engine }}</td></tr>
      <tr><td>Engine version:</td><td>{{ item.raw.fileContent.engine_version }}</td></tr>
      <tr><td>Engine type:</td><td>{{ item.raw.fileContent.type }}</td></tr>
      <tr><td>File input:</td><td>{{ item.raw.fileName }}</td></tr>
    </tbody>
  </v-table>




            <v-card-actions>
              <v-switch
                :label="`${isExpanded(item) ? 'Hide' : 'Show'} details`"
                :model-value="isExpanded(item)"
                density="compact"
                inset
                @click="() => toggleExpand(item)"
              ></v-switch>
            </v-card-actions>

            <v-divider></v-divider>

            <v-expand-transition>
              <div v-if="isExpanded(item)">
                  {{item.raw.fileContent.output}}
              </div>
            </v-expand-transition>

          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-data-iterator>


 <v-list lines="two">
      <v-list-subheader inset>Jobs</v-list-subheader>

      <v-list-item
        v-for="folder in reducedData"
        :key="folder.id "
        :subtitle="`api: ${folder.api_url} status: ${folder.status_url}`"
        :title="`${folder.type} - ${folder.engine}  - ${folder.status} - (${folder.last_run_at})`"
      >

        <template v-slot:prepend>
          <v-avatar color="grey-lighten-1">
            <v-icon 
            @click="showJob(folder)"
            :color="(folder.status != 200) ? 'red' : 'green'">mdi-check-circle</v-icon>
          </v-avatar>
      
        </template>

    
        {{folder.status_description}}

      </v-list-item>
  </v-list>

<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Storage</v-toolbar-title>
</v-toolbar>

 <v-list lines="two">
      <v-list-subheader inset>storageItems</v-list-subheader>

      <v-list-item
        v-for="item in storageItems"
     >


        <template v-slot:prepend>
          <v-avatar color="grey-lighten-1">
            <v-icon 
            @click="showJob(folder)"
            color="green">mdi-check-circle</v-icon>
          </v-avatar>
      
        </template>


      
      <pre>BatchId: {{item.batchId}}</pre>
      <pre>fileName: {{item.fileName}}</pre>
      <pre>Type: {{item.fileContent.type}}</pre>
      <pre>Engine name: {{item.fileContent.engine}}</pre>
      <pre>Engine version:{{item.fileContent.engine_version}}</pre>
      <pre>Status: {{item.fileContent.status}}</pre>


      <pre>{{item.fileContent}}</pre>
      

      </v-list-item>
  </v-list>



<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Jobs table</v-toolbar-title>
</v-toolbar>


<v-data-table :items="reducedData">

      <template v-slot:item.status="{ item }">
        <div class="text-end">
          <v-chip
            :color="(item.status != 200) ? 'red' : 'green'"
            :text="item.status"
            class="text-uppercase"
            label
          ></v-chip>
        </div>
      </template>

      <template v-slot:item.id="{ item }">
        <v-btn 
        :color="(item.status != 200) ? 'red' : 'green'"
        
        @click="showJob(item)">{{item.id}}</v-btn>
      </template>

</v-data-table>



<v-overlay
:model-value="overlay"
class="align-center justify-center"
>
<v-progress-circular
  color="primary"
  size="64"
  indeterminate
></v-progress-circular>
</v-overlay>

<v-snackbar v-model="snackbar">
      {{ txtSnackbar }}
      <template v-slot:actions>
        <v-btn
          color="blue"
          variant="text"
          :timeout="3000"
          @click="snackbar = false"
        >
          Close
        </v-btn>
      </template>
</v-snackbar>


</template>