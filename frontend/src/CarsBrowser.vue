
<template>

  <div class='carsBrowser'> 

    <div class='carCard' v-for='car in cars' :key='car.id'>
        <span>{{car.description}}</span>
        <span>{{car.plate}}</span>
    </div>

  </div>

</template>



<script>

import { prepareLoadingAnimation, slidingMessage  } from './js/utils.js'


export default {
  props: ['cars'],

  data() {
    return {
    }
  },

  mounted() {
  },


  methods: {

  }
}

</script>




<style scoped>
</style>
