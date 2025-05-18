
<template>

  <div> <!-- this is just a fragment to wrap the template -->

    <div class='headerBar'>

      <div class='headerLogo' @click='expressions=null' ></div>

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
    <div class='carsBrowser'><CarsBrowser /></div>

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



<script>
import CarsBrowser from './CarsBrowser.vue';

// some nice effects using jquery
import 'jquery-ui-bundle';
import 'jquery-ui-bundle/jquery-ui.min.css';

import { prepareLoadingAnimation, slidingMessage  } from './js/utils.js'


export default {
  data() {
    return {
      isUSASelected: true,
      expressions: [],  // english/portuguese
      cars: [], 
      isLoading: true,
      error: null,
      backendUrl: 'http://localhost',  // it changes depending if it is containerized or not
    }
  },

  mounted() {
    prepareLoadingAnimation()

    this.isLoading = true;

    Promise.all( [this.fetchExpressions(), this.fetchCars()] ).then(() => {
      this.isLoading = false
    })
    
  },

  watch: {
    // user changes current language, (re)fetch expressions
    isUSASelected: {
      handler() {
        this.isLoading = true;

        Promise.all( [this.fetchExpressions()] ).then(() => {
          this.isLoading = false
        })        
      },
      immediate: false,
    },
  },

  methods: {
    async fetchExpressions() {
      let language = this.isUSASelected ? 'english' : 'portuguese';

      await fetch(`${this.backendUrl}/expressions/${language}`)

      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        this.expressions = data;        
      })
      .catch((error) => {
        this.isLoading = false;
        slidingMessage('Fatal error= '+error, 3000)        
      })  
    },

    async fetchCars() {
let language = this.isUSASelected ? 'english' : 'portuguese';
      await fetch(`${this.backendUrl}/expressions/${language}`)

      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
//        this.expressions = data;        
console.log('ssssssss expressions='+data)
      })
      .catch((error) => {
        this.isLoading = false;
        slidingMessage('Fatal error= '+error, 3000)        
      })  

    },

  },

  components: {
    CarsBrowser
  }
}

</script>




<style scoped>
</style>
