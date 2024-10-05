<script>
  export default {
    data() {
      return {

        overlay: false,
        job_info : {},
        job_info_options : {},
        batch_info: [],
        batch_jobs: [],
        batch_item: {},
        listItems: [],

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

       showBatch(item) {
        console.log(item);
        this.$router.push('/batch/' + item.batch_uuid);
      },

      async getData(id) {

        this.overlay = true;  
        const route = VueRouter.useRoute();

        console.log(route.params.id)
        const res = await fetch("/api/job/" + route.params.id );
        const finalRes = await res.json();
        
        this.job_info = finalRes;

        /*
        this.batch_jobs = finalRes.batch_jobs;
        this.batch_info = finalRes.batch_info;
        this.batch_item = finalRes.batch_info[0];

        console.log(this.batch_info);
        console.log(this.batch_jobs);
        console.log(this.batch_item);

        const res1 = await fetch("api/upload/list");
        this.file_list = await res1.json();

        const res2 = await fetch("api/config/list");
        this.config_list = await res2.json();

        const res3 = await fetch("api/action/list");
        this.action_list = await res3.json();

        */

        // decode options 

        console.log(this.job_info.options);

        this.job_info_options = JSON.parse(this.job_info.options)
        console.log(this.job_info_options);
      

        

        this.overlay = false;
      },

    handleDelete()  {}, 

    handleRun() {
            // POST request using fetch with error handling

        console.log(this.files_selected);
        console.log(this.engines_selected);
        console.log(this.action_selected);

        const ops = {
          action_selected : this.action_selected,
          engines_selected : this.engines_selected,
          files_selected : this.files_selected,
        };

        const obj = {
            batch_uuid:  this.batch_id,
            batch_description : this.batch_id,
            batch_action: 'CHECK_CONFIG',
            batch_options: JSON.stringify(ops)
        };

        console.log(obj);

            const requestOptions = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(obj)
            };

            fetch('/api/batch', requestOptions)
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
    },    
    mounted() {
      
      const route = VueRouter.useRoute();
      console.log(route.params.id)
      this.getData(route.params.id)
      
    }
  }
</script>


<template>

<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Job info - {{ $route.params.id }} </v-toolbar-title>
    <v-btn class="mb-2"    @click="showBatch(job_info)">
              Batch {{job_info.batch_uuid}} 
            </v-btn>
</v-toolbar>

<v-table>
    <thead>
      <tr>
        <th class="text-left">Key</th>
        <th class="text-left">Value</th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="(value, key) in job_info"
        :key="key"
      >
        <td>{{ key }}</td>
        <td>{{ value }}</td>
      </tr>
    </tbody>
  </v-table>



<v-divider inset></v-divider>
<v-list-subheader inset>Files</v-list-subheader>


<v-list lines="two">
  <v-list-item
    v-for="(value, key) in job_info_options" 
    :key="key"
  >{{key}} : {{ value }}</v-list-item>
</v-list>

<v-col cols="12" md="6" sm="6">
        <v-btn  color="success" @click="handleRun"  size="x-large" block>{{job_info_options.fileInput}}</v-btn>
</v-col>


<v-col cols="12" md="6" sm="6">
        <v-btn  color="success" @click="handleRun"  size="x-large" block>{{job_info_options.fileOutput}}</v-btn>
</v-col>



    <v-row justify="center">
      <v-col cols="12" md="6" sm="6">
        <v-btn  color="success" @click="handleRun"  size="x-large" block>Elimina</v-btn>
      </v-col>

      <v-col cols="12" md="6" sm="6">
        <v-btn color="error"  @click="handleDelete" size="x-large" block>Esegui</v-btn>
      </v-col>
    </v-row>




<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Jobs list</v-toolbar-title>
</v-toolbar>





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




</template>