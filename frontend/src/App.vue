<script >

</script>

<template>
  <div class='headerBar'>

    <div class='HiringMachineLogo'></div>
    <div>2</div>
    <div>

        <!-- language/country selector  -->
        <div class="countrySelect">    

          <div :class="! isUSASelected ? 'flagClicked' : 'flagUnclicked' "   id='flagBRAZIL'  @click="isUSASelected = false"  >         
            <img src="./assets/images/brazil_flag.svg" alt='' />
          </div>

          <label for="chkLanguageSelector" class="switch_language"  >
            <input id="chkLanguageSelector" type="checkbox"  v-model="isUSASelected"   />
            <span class="slider_language round"></span>
          </label>

          <div :class="isUSASelected ? 'flagClicked' : 'flagUnclicked' "   id='flagUSA'  @click="isUSASelected = true"  >         
            <img src="./assets/images/usa_flag.svg" alt='' />
          </div>

        </div>

    </div>

  </div>
</template>

<script>

  export default {
    data() {
      return {
        isUSASelected: true,
      }
    }
  }

</script>



<style scoped>
</style>
