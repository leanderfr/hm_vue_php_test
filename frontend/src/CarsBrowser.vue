
<template>

  <div class='carsBrowser'> 

    <!-- car images hosted in AWS S3 -->
    <div class='carCard' v-for='car in cars' :key='car.id' 
        :style="{ 
          backgroundImage: `url(https://devs-app.s3.sa-east-1.amazonaws.com/hiring_machine/car_${car.id}.png)` ,
          backgroundRepeat: 'no-repeat',
          backgroundSize: '140px 90px',
          backgroundPositionY: 'center'

        }" >
        <span>{{car.description}}</span>
        <span>{{car.plate}}</span>
    </div>

  </div>

</template>



<script setup>
const props = defineProps( ['cars'] )


</script>




<style scoped>
</style>
