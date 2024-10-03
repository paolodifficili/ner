<script>
  export default {
    data() {
      return {
        overlay: false,
        batch_id : '',
        
        file_list: [],
        config_list: [],
        action_list: [],

        engines_selected: [],
        files_selected: [],
        action_selected: [],

        batch_item: {}
      }
    },
    methods: {

      async loadData() {
        this.overlay = true;  
        const route = VueRouter.useRoute();
        
        const res1 = await fetch("api/upload/list");
        this.file_list = await res1.json();

        const res2 = await fetch("api/config/list");
        this.config_list = await res2.json();

        const res3 = await fetch("api/action/list");
        this.action_list = await res3.json();


        console.log(this.file_list);
        console.log(this.config_list);
        console.log(this.action_list);

        this.overlay = false;

      },

      handleSubmit() {
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
        const idx = Math.floor(Math.random() * 100000);
        this.batch_id = 'BATCH' + idx;
    

        console.log('mounted --- load data');
        this.loadData();
      
    }
  }
</script>


<template>

<v-toolbar
    color="blue-grey"
    dark
    flat
 >
    <v-toolbar-title>Batch NEW</v-toolbar-title>
</v-toolbar>

<v-text-field v-model="batch_id" label="BATCH_ID"></v-text-field>

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

<v-btn @click="handleSubmit"> Invia </v-btn>

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

<pre>{{ action_selected }}</pre>
<pre>{{ engines_selected }}</pre>
<pre>{{ files_selected }}</pre>


</template>