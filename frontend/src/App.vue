
<template>

  <div>  <!-- fragment -->

    <div class='appBody'>  

      <div class='headerBar'>

        <div class='headerLogo' ></div>

        <div class='headerText' >
            <div>{{ expressions.app_header_title }}</div>
        </div>

        <!-- language/country selector  -->
        <div class="headerRight">    

          <div :class="! isUSASelected ? 'flagClicked' : 'flagUnclicked' "   id='flagBRAZIL'  @click="isUSASelected = false"  >         
            <img src="./assets/images/brazil_flag.svg" alt='' />
          </div>

          <label for="chkLanguageSelector" class="switch_language"  >
            <input id="chkLanguageSelector" type="checkbox"  v-model="isUSASelected"    />
            <span class="slider_language round"></span>
          </label>

          <div :class="isUSASelected ? 'flagClicked' : 'flagUnclicked' "   id='flagUSA'  @click="isUSASelected = true"  >         
            <img src="./assets/images/usa_flag.svg" alt='' />
          </div>

        </div>

      </div>

      <!-- horizontal cars browser -->
      <div class='carsBrowserContainer' v-if="cars.lenght!=0" >
        <CarsBrowser :cars='cars' />
      </div>

      <!-- the schedule  -->
      <div class='mainContainer' v-if="cars.length!=0 && expressions.length!=0" >
        <Schedule :expressions='expressions' :currentCountry="isUSASelected ? 'usa' : 'brazil'" />
      </div>

      <div class='h-[60px] bg-red-300 flex' >
        DIV FINAL
      </div>


    </div>


    <!-- waiting backend response animation, thanx God Vue has 'v-show', React doesnt and the animation has to be (re)prepared all the time  -->
    <div v-show="isLoading" class='backdropTransparent'  >
      <div id='divLoading' >&nbsp;</div>
    </div>

    <!-- show sliding error messages -->
    <div id="messagesSlidingDiv" >
      &nbsp;
    </div>

    <audio id="alertBeep" >
      <source src="./assets/sounds/error_beep.mp3" type="audio/mpeg">    
    </audio>

  </div>


</template>


<!-- composition API , way cleaner then options API -->
<script setup>
  import { ref, onMounted, watch  } from 'vue';
  import CarsBrowser from './CarsBrowser.vue';
  import Schedule from './Schedule.vue';

  // some nice effects using jquery
  import 'jquery-ui-bundle';
  import 'jquery-ui-bundle/jquery-ui.min.css';

  import { prepareLoadingAnimation, slidingMessage  } from './js/utils.js'

  const isUSASelected = ref(true)
  const expressions = ref([])
  const cars = ref([])
  const isLoading = ref(true)
  const error = ref(null)
  const backendUrl = ref('http://localhost')  // it changes depending if the app is running as a container (AWS EC2) or locally

  //***************************************************************************
  //***************************************************************************
  onMounted( () => {
      prepareLoadingAnimation()

      isLoading.value = true

      Promise.all( [fetchExpressions(), fetchCars()] ).then(() => {
        isLoading.value = false
      })
  })

  //***************************************************************************
  // if user changes current language, (re) fetch expressions (port/english)
  //*************************************************************************** 
  watch(isUSASelected, () => {
      isLoading.value = true;

      Promise.all( [fetchExpressions()] ).then(() => {
        isLoading.value = false
      })        
    },
    { immediate: false }
  )


  //***************************************************************************
  //*************************************************************************** 
  async function fetchExpressions() {
    let language = isUSASelected.value ? 'english' : 'portuguese';

    await fetch(`${backendUrl.value}/expressions/${language}`)

    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      expressions.value = data;        
    })
    .catch((error) => {
      isLoading.value = false;
      slidingMessage('Fatal error= '+error, 3000)        
    })  
  }

  //***************************************************************************
  //*************************************************************************** 
  async function fetchCars() {
    await fetch(`${backendUrl.value}/cars`)

    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      cars.value = data;        
    })
    .catch((error) => {
      isLoading.value = false;
      slidingMessage('Fatal error= '+error, 3000)        
    })  
  }


</script>




<style scoped>
</style>
