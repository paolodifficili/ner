<script>
  export default {
    data() {
      return {
        items: ['foo', 'bar', 'fizz', 'buzz', 'fizzbuzz', 'foobar'],
        value: ['foo', 'bar', 'fizz'],
        engines_selected: [],
        engines: ['NER', 'SPACY', 'HF01', 'HF02', 'SCALA'],
        files_selected: [],
        files: ['F1', 'f2', 'f3', 'f4', 'f5'],


        listItems: []
      }
    },
    methods: {
      async getData() {
        const res = await fetch("https://jsonplaceholder.typicode.com/posts");
        const finalRes = await res.json();
        this.listItems = finalRes;
      },

      goToProfile() {
        console.log('goToProfile');
        this.$router.push('/profile/1')
      },
    },
    mounted() {
      this.getData()
    }
  }
</script>

<template>

<div class="text-h4">Batch manager</div>


<div class="text-h5">Batch manager</div>
<v-text-field
  v-model="model"
  hide-details="auto"
  label="Batch Name"
  clearable
></v-text-field>


<div class="text-h5">Batch manager</div>

<v-select
v-model="engines_selected"
:items="engines"
hint="Pick your favorite engine"
label="Select Engine"
multiple
persistent-hint
></v-select>

<v-select
v-model="files_selected"
:items="files"
hint="Pick your favorite file"
label="Select File"
multiple
persistent-hint
></v-select>


<v-list lines="one">
<v-list-item
  v-for="n in listItems"
  :key="n"
  :title="'Item '+ n.id + ' ' + n.title"
  :subtitle="n.body"
></v-list-item>
</v-list>


</template>