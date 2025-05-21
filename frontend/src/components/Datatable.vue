
<template>

    <div>   <!-- required (by Vue) fragment -->


    <div class='flex flex-col h-full ' id='datatableContainer'>

      <!-- top tool bar -->
      <div class="flex flex-row h-[60px] w-full justify-between border-b-2 " id='datatableToolbar'>


          <div></div>

          <!-- action buttons -->
          <div class="flex flex-row pt-1">

              <!-- schedule  -->
              <div  class='btnSCHEDULE_TABLE putPrettierTooltip' :title="expressions.schedule" @click="forceHideTolltip();emit('displaySchedule')" aria-hidden="true"></div>   

              <!-- cars table -->
              <div  class='btnCARS_TABLE putPrettierTooltip' :title="expressions.cars" @click="forceHideTolltip();emit('setDatatableToDisplay', 'cars')" aria-hidden="true"></div>   

              <!-- expressions table -->
              <div  class='btnEXPRESSIONS_TABLE putPrettierTooltip' :title="expressions.expressions" @click="forceHideTolltip();emit('setDatatableToDisplay', 'expressions')" aria-hidden="true"></div>   

          </div> 

      </div>

      <div class='datatableTitle' >
        <div> {{ title }} </div>
        <div style='padding-top: 10px; font-size: 14px'>Legenda: <span style=' background-color: red'>&nbsp;&nbsp;&nbsp;</span>= inactive</div>
      </div>

      <!-- loop to display each column -->
      <div class="datatableHeader" > 
            <div v-for='column in columns' :key="column" :style="{width: column.width}">{{ column.title }}</div> 
      </div>          

      <!-- display records from the current table -->
      <div v-if='records'>
          <div  class="DatatableRows" v-for='record in records' :key="record"> 

              <div :class="record.active ? 'DatatableRow' : 'DatatableRowInactiveRecord'"   >         

                <template v-for='(column, index) in columns'   >         

                  <div v-if='index < (columns.length-1)' :style="{width: column.width, paddingLeft: '5px'}" :key="'1tr-'+index"  >
                    {{ record[column.fieldname] }}
                  </div>


              </template>
            </div>
          </div>
      </div>

  </div>
</div>

</template>


<script setup>
import { slidingMessage, forceHideTolltip  } from '../assets/js/utils.js'
import { onMounted, ref  } from 'vue';

const emit = defineEmits( ['showLoading', 'hideLoading','updateSelectedCar','setDatatableToDisplay','displaySchedule'] );
const props = defineProps( ['currentViewedDatatable', 'currentCountry', 'backendUrl', 'imagesUrl', 'expressions' ] )

const records = ref(null)  

// colunas que serao exibidias dependendo da tabela sendo vista (_currentMenuItem)
let columns = []

// car's datatable
let title = ''

if (props.currentViewedDatatable === 'cars')   {
  columns.push({ fieldname: "id", width: "5%", title: 'Id', id: 'col1', boolean: false },
              { fieldname: "description", width: "calc(75% - 150px)", title: 'Nome', id: 'col2', boolean: false},
              { fieldname: "plate", width: "20%", title: 'Nome', id: 'col2', boolean: false} )
  title = props.expressions.cars_table
}


// ultima coluna, acoes (editar, excluir, etc)
columns.push( {name: 'actions', width: '150px', title: '', id: 3} )


//*****************************************************************************
//*****************************************************************************

onMounted( () => {

  // the only way to make the 'DatatableRows' stop overflowing the parent div, was to put its height manually
  // have no more time to make it with css now, but there may be a way with css
  // make this only once
  if ( $('.DatatableRows').height()=='0' ) {
    let hgt1 = $('.datatableTitle').height() 
    let hgt2 = $('#datatableToolbar').height()
    let hgtCONTAINER = $('#datatableContainer').height()

    $('.DatatableRows').height( hgtCONTAINER - hgt1 - hgt2 - 10)
  }


  setTimeout( () => {
    // define tooltip of top buttons
    if (typeof $('.putPrettierTooltip').tooltip !== "undefined") {
      $('.putPrettierTooltip').tooltip({ 
        tooltipClass: 'prettierTitle_black',
        show: false,  
        hide: false,  
        position: { my: "left top", at: "left top-40", collision: "flipfit" }
      })
    }
  }, 500)    

  fetchData()


})



//***************************************************************************
//*************************************************************************** 
async function fetchData() {
  emit('showLoading')

  await fetch(`${props.backendUrl}/${props.currentViewedDatatable}`)

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.json()
  })
  .then((data) => {
    emit('hideLoading')
    records.value = data;        
  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  
}

</script>