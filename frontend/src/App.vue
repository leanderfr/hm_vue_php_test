<script >

</script>

<template>
  <div class='headerBar'>

    <div class='HiringMachineLogo'></div>

    <div class='headerText'>
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
</template>

<script>

  export default {
    data() {
      return {
        isUSASelected: true,
        expressions: [],  // english/portuguese
        isLoading: true,
        error: null,
        backendUrl: 'http://localhost',
      }
    },

    mounted() {
      this.fetchExpressions();
    },

    methods: {
      async fetchExpressions() {
        this.isLoading = true;

        // if language already defined and portuguese, any other situation, english
        //let language = typeof isUSASelected != 'undefined' && ! isUSASelected  ? 'portuguese' : 'english'
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
