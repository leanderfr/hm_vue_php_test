
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
        <CarsBrowser 
          :cars='cars'  
          :selectedCar='selectedCar' 
          @updateSelectedCar='updatedSelectedCar'            /> 
      </div>

      <!-- mainContainer is the place where dynamic content is viewed/replaced/etc  -->

      <!-- display schedule only if theres at least 1 car and 1 expression  -->
      <div v-if="toDisplaySchedule && cars.length!=0 && expressions.length!=0" id='mainContainer'  >
        <Schedule 
            :key='forceScheduleRedraw' 
            :expressions='expressions' 
            :currentCountry="isUSASelected ? 'usa' : 'brazil'" 
            :backendUrl='backendUrl'    
            :imagesUrl = 'imagesUrl'
            :selectedCar='selectedCar'
            @setDatatableToDisplay='setDatatableToDisplay'
            @showLoading="isLoading=true" 
            @hideLoading="isLoading=false" />
      </div>

      <!-- display datatable if user clicked in some type of record to list (cars or expressions)  -->
      <div v-if='toDisplayDatatable' id='mainContainer'  >

        <Datatable
            :currentViewedDatatable = currentViewedDatatable
            :setDatatableToDisplay='setDatatableToDisplay' 
            :expressions='expressions' 
            :currentCountry="isUSASelected ? 'usa' : 'brazil'" 
            :backendUrl='backendUrl'    
            @showLoading="isLoading=true" 
            @hideLoading="isLoading=false" 
            @displaySchedule='displaySchedule'
            :imagesUrl = 'imagesUrl' />
      </div>


      <!-- footer toolbar   -->
      <div v-if="cars.length!=0 && expressions.length!=0" class='bottomToolbar'  >        
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

    <!-- puppy icon bottom right corner  -->
    <div class='_doggy'  id='divDoggy'></div>
    <div class='_doggy_1' id='divDoggy_1'></div>
    <div class='_doggy_2' id='divDoggy_2'></div>
    <div v-if='isUSASelected' class='_doggy_3_english' id='divDoggy_3'></div>
    <div v-if='! isUSASelected' class='_doggy_3_portuguese' id='divDoggy_3'></div>


  </div>



</template>


<!-- composition API , way cleaner then options API -->
<script setup>
  import { ref, onMounted, watch  } from 'vue';
  import CarsBrowser from './CarsBrowser.vue';
  import Schedule from './Schedule.vue';
  import Datatable from './components/Datatable.vue';

  // some nice effects using jquery
  import 'jquery-ui-bundle';
  import 'jquery-ui-bundle/jquery-ui.min.css';

  import { prepareLoadingAnimation, slidingMessage , preparePuppyIcon } from './assets/js/utils.js'

  var scriptsToLoad = [
      "http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com/hiringmachine/externalJS/calendar/picker.js", 
      "http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com/hiringmachine/externalJS/calendar/picker.date.js",
      "http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com/hiringmachine/externalJS/calendar/legacy.js",
      "http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com/hiringmachine/externalJS/multiDraggable.js",
      "http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com/hiringmachine/externalJS/maskDateHour.js"
  ]

  // load js files one after another, to avoid calling a function not loaded yet
  loadScripts(scriptsToLoad).done(function() {
      //console.log('read')
  });

 
  const forceScheduleRedraw = ref(0)  
  const isUSASelected = ref(true)
  const expressions = ref([])
  const cars = ref([])
  const isLoading = ref(true)
  const error = ref(null)

  // it changes depending if the app is running as a container (AWS EC2) or locally
  const backendUrl = ref('http://ec2-54-233-183-5.sa-east-1.compute.amazonaws.com:8073')  
  //const backendUrl = ref('http://127.0.0.1')  

  const imagesUrl = ref('https://devs-app.s3.sa-east-1.amazonaws.com/hiring_machine/')  

  // currently selected car (starts with -1= show schedule of all cars)
  const selectedCar = ref(0)

  // controls what datatable should be displayed
  const currentViewedDatatable = ref('')

  // controls if the schedule should be displayed
  const toDisplaySchedule = ref(true)

  // controls if some datatable should be displayed
  const toDisplayDatatable = ref(false)





  //******************************************************************************
  // load js files one after another, to avoid calling a function not loaded yet
  //******************************************************************************

   function loadScripts(scripts) {
       var promises = [];
       scripts.forEach(function(script) {
           promises.push($.getScript(script));
       });
       return $.when.apply($, promises);
   }

  //***************************************************************************
  //***************************************************************************
  const updatedSelectedCar = (carId) => {
    selectedCar.value = carId
  }

  //***************************************************************************
  //  user clicked in a given table in the schedule top bar
  //***************************************************************************
  const setDatatableToDisplay = (datatable) => {
    toDisplaySchedule.value = false;
    toDisplayDatatable.value = true;

    currentViewedDatatable.value = datatable
  }

  //***************************************************************************
  //  user clicked on the schedule button inside the datatalbe component
  //***************************************************************************
  const displaySchedule = () => {
    toDisplaySchedule.value = true
    toDisplayDatatable.value = false
  }




  //***************************************************************************
  //***************************************************************************
  onMounted( () => {
      // handler to keyboard
      window.onkeydown = (event) => {
        onKeyDown(event)
      };


      // for now, I have no resolution to re(draw) components but refresh the whole window
      window.onresize = (event) => {
        setTimeout(() => {
          window.location.reload();  
        }, 500);
      }
 
      preparePuppyIcon()
      prepareLoadingAnimation()

      isLoading.value = true

      Promise.all( [fetchExpressions(), fetchCars()] ).then(() => {
        isLoading.value = false
      })
  })

  //***************************************************************************
  // if user changes current language, (re) fetch expressions (port/english)
  //*************************************************************************** 
  watch([isUSASelected, selectedCar], () => {
      isLoading.value = true;

      Promise.all( [fetchExpressions()] ).then(() => {
        isLoading.value = false
        forceScheduleRedraw.value++;
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


/************************************************************************************************************************************************************
 handle key pressed throughout the entire application
************************************************************************************************************************************************************/

const onKeyDown = (e) =>  {

  // if user presses Enter or arrow down/up, backs or forwards the focus to the next/previous field
  if ( (e.which == 13 || e.which == 38 || e.which == 40)  && $('.text_formFieldValue').is(':focus') )   { 
        // I had to invent the 'sequence' property, because VITE is bugging about tabIndex

        let tab =  $(':focus').attr("sequence");
        if (e.which==13 || e.which == 40)  tab++;
        else if (e.which==38)  tab--;

        e.preventDefault()

        // put the focus in the next/previous field based on the pre determined 'sequence' property
        $("[sequence='"+tab+"']").focus();          
  }

  


  // if user presses F2 or Esc, being any form edit screen opened
  if (e.which == 27 || e.which == 113)   { 
        let bookingRecordForm = typeof $('#bookingRecordForm').attr("id")

        // triggers close button
        if (bookingRecordForm != 'undefined')  {
          if (e.which == 27)   $('#btnCLOSE').trigger('click')

          // the following forms contain the button 'F2= save'
          if (bookingRecordForm != 'undefined')  {
            if (e.which == 113)   $('#btnSAVE').trigger('click')   // f2 foi pressionado
          }
        }
  }
}


</script>


<style scoped>
</style>
