<script>
  export default {
    data() {
      return {
        selected: [],
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
        console.log(this.selected[0]);
        this.$router.push('/profile/' + this.selected[0]);
      },

    },
    mounted() {
      this.getData()
    }
  }
</script>

<template>

<v-data-table 
v-model="selected"
:items="listItems"
item-value="id"
items-per-page="5"
show-select
>
</v-data-table>
<pre>{{ selected }}</pre>

<button @click="goToProfile">Go to Profile</button>

</template>