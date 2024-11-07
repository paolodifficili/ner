<template>
<v-responsive class="border rounded">
<v-app :theme="theme">

<v-app-bar title="Mgr" class="px-3">
<v-spacer></v-spacer>
<RouterLink to="/"><v-btn>Home</v-btn></RouterLink>
<RouterLink to="/upload"><v-btn>Upload</v-btn></RouterLink>
<RouterLink to="/batch"><v-btn>Batch</v-btn></RouterLink>
<RouterLink to="/batchnew"><v-btn>Batch new</v-btn></RouterLink>

<v-btn stacked>
      <v-badge
        :content="counter"
        color="error"
        @click="onOpen"
      >
        <v-icon icon="mdi-newspaper-variant-outline"></v-icon>
      </v-badge>
 
</v-btn>



<v-btn
  :prepend-icon="theme === 'light' ? 'mdi-weather-sunny' : 'mdi-weather-night'"
  text="TT"
  slim
  @click="onClick"
></v-btn>

</v-app-bar>




  <v-main>
    <v-container fluid>
      <RouterView />
    </v-container>
  </v-main>


</v-app>

<div class="text-center pa-4">
    <v-btn @click="dialog = true">
      Open Dialog
    </v-btn>

    <v-dialog
      v-model="isActive"
      width="auto"
    >
      <v-card
        max-width="400"
        prepend-icon="mdi-update"
        text="Elenco delle azioni completate:"
        title="Batch notification"
      >

      <v-list lines="one">
      <v-list-item
        v-for="n in messages"
        :key="n"
        :title="n.message"
        :subtitle="n.status + ' ' + n.action "
      ></v-list-item>
      </v-list>

        <template v-slot:actions>
          <v-btn  class="ms-auto"    text="Clean!"    @click="onClean"    ></v-btn>
          <v-btn  class="ms-auto"    text="Ok"    @click="onOpen"    ></v-btn>
        </template>
      </v-card>
    </v-dialog>
</div>



</v-responsive>




</template>


<script setup>
  import { ref } from 'vue'

  const theme = ref('light')
  const counter = ref(0);
  const isActive = ref(false);
  const messages = ref([]);


  
  var cntMessages = 1;

  function onOpen () {
    console.log('onOpen');
    isActive.value = isActive.value === true ? false : true
  }

  function onClean () {
    console.log('onClean');
    messages.value = [];
    counter.value = 0;
  }

  function onClick () {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
  }



  console.log('app.vue initi GotMessage')
  window.Echo.channel('chat')
    .listen('GotMessage', (e) => {
      console.log('app.vue GotMessage');
      console.log(e);
      console.log(e.message);
      counter.value++;
      messages.value.push(e);
  });


</script>

