
<template>

  <div> <!-- this is just a fragment -->

    <div class='headerBar'>

      <div class='headerLogo'></div>

      <div class='headerText' @click='isLoading=true'>
          {{ expressions.app_header }}
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

    <div v-show="isLoading" class='backdropTransparent'  >
      <div id='divLoading' >&nbsp;</div>
    </div>
  </div>

</template>



<script>
// some nice effects using jquery
import 'jquery-ui-bundle';
import 'jquery-ui-bundle/jquery-ui.min.css';

import { prepareLoadingAnimation, slidingMessage  } from './js/utils.js'


export default {
  data() {
    return {
      isUSASelected: true,
      expressions: [],  // english/portuguese
      isLoading: true,
      error: null,
      backendUrl: 'http://localhost',  // it changes depending if it is containerized or not
    }
  },

  mounted() {
    prepareLoadingAnimation()
    this.fetchExpressions()
  },

  methods: {
    async fetchExpressions() {
      this.isLoading = true;

      let language = this.isUSASelected ? 'english' : 'portuguese';

      fetch(`${this.backendUrl}/expressions/${language}`)
      .then((response) => response.json())
      .then((data) => {
        this.expressions = data;
        this.isLoading = false;
      })
      .catch((error) => console.log('erro='+error));        

    },
  }
}

</script>




<style scoped>
</style>
