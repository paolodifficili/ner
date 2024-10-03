<script>
  export default {
    data() {
      return {
        firstname: "ciao",
        firstname: "saluti",
        overlay: false,
        batch_info: [],
        batch_jobs: [],
        batch_item: {},

        file_list: [],
        config_list: [],
        action_list: [],

        engines_selected: [],
        files_selected: [],
        action_selected: [],

      }
    },
    methods: {
      async getData(id) {
        this.overlay = true;  
        const route = VueRouter.useRoute();

        console.log(route.params.id)
        const res = await fetch("/api/batch/" + route.params.id );
        const finalRes = await res.json();
        
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

        // decode options 

        console.log(this.batch_item.batch_options);

        const ob = JSON.parse(this.batch_item.batch_options)

        console.log(ob);

        this.engines_selected =  ob.engines_selected;
        this.files_selected = ob.files_selected;
        this.action_selected =  ob.action_selected;


        this.overlay = false;
      },


    },

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
            }


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
    <v-toolbar-title>Batch info - {{ $route.params.id }}</v-toolbar-title>
</v-toolbar>


<v-form v-model="valid">
<v-container fluid>
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

      <v-col cols="12" md="6" sm="6">
        <v-btn  color="success" @click="handleRun"  size="x-large" block>Elimina</v-btn>
      </v-col>

      <v-col cols="12" md="6" sm="6">
        <v-btn color="error"  @click="handleDelete" size="x-large" block>Esegui</v-btn>
      </v-col>


 
    </v-row>
  </v-container>

<div>


</div>


</v-container>
</v-form>



<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Jobs list</v-toolbar-title>
</v-toolbar>

<v-data-table :items="batch_jobs"></v-data-table>





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

<pre>{{ listItems }}</pre>


</template>