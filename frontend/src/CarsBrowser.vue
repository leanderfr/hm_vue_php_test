
<template>

  <div class='carsBrowser'> 

    <!-- car images hosted in AWS S3 -->
    <template v-if='cars.length!=0' >
        <div :id="'carCard' + car.id" class='carCard' :class='{carCardSelected: props.selectedCar==car.id}' v-for='car in cars' :key='car.id' 
            :style="{ 
              backgroundImage: `url(https://devs-app.s3.sa-east-1.amazonaws.com/hiring_machine/car_${car.id}.png?${strToAvoidCache})` ,
              backgroundRepeat: 'no-repeat',
              backgroundSize: '140px 90px',
              backgroundPositionY: 'center'

            }" 
            @click="emit('setNewSelectedCar', car.id)"   >
            <span>{{car.description}}</span>
            <span>{{car.plate}}</span>
        </div>
      </template>

    </div>

</template>


<script setup>
import { onMounted, ref  } from 'vue';
import { slidingMessage } from './assets/js/utils.js'
const props = defineProps( ['selectedCar', 'backendUrl'] )

const cars = ref([])
const strToAvoidCache = ref('')

// user clicks a car card
const emit = defineEmits(['setNewSelectedCar','hideLoading','showLoading']);


//*****************************************************************************
//*****************************************************************************
onMounted( () => {
  strToAvoidCache.value = (Math.random() + 1).toString(36).substring(7);
    
  fetchCars();
})
    

//*****************************************************************************
//*****************************************************************************

async function fetchCars()  {
    emit('showLoading')
    await fetch(`${props.backendUrl}/cars/active`)

    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      cars.value = data;        
      emit('hideLoading')

    })
    .catch((error) => {
      emit('hideLoading')
      slidingMessage('Fatal error= '+error, 3000)        
    })  
}


</script>




<style scoped>
</style>
