
<template>

  <div class='carsBrowser'> 

    <!-- car images hosted in AWS S3 -->
    <div class='carCard' :class='{carCardSelected: props.selectedCar==car.id}' v-for='car in cars' :key='car.id' 
        :style="{ 
          backgroundImage: `url(https://devs-app.s3.sa-east-1.amazonaws.com/hiring_machine/car_${car.id}.png)` ,
          backgroundRepeat: 'no-repeat',
          backgroundSize: '140px 90px',
          backgroundPositionY: 'center'

        }" 
        @click="emit('setNewSelectedCar', car.id)"   >
        <span>{{car.description}}</span>
        <span>{{car.plate}}</span>
    </div>

  </div>

</template>


<script setup>
import { onMounted  } from 'vue';
const props = defineProps( ['cars', 'selectedCar'] )

// user clicks a car card
const emit = defineEmits(['setNewSelectedCar']);


//*****************************************************************************
//*****************************************************************************
onMounted( () => {
    
console.log('broiw de novo')
  

  })
</script>




<style scoped>
</style>
