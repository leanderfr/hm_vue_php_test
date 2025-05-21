
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
      <div class="DatatableRows" v-for='record in records' :key="record"> 

          <div :class="record.active ? 'DatatableRow' : 'DatatableRowInactiveRecord'" :key='record.id'  > 
    
            
            <div v-if='j===length-1'>

                    <div v-if='record.active' className='actionColumn' :style="{width: col.width}"  >
                        <div className='actionIcon'  ><img alt='' src='./assets/images/edit.svg' /></div>
                        <div className='actionIcon'  ><img alt='' src='./assets/images/delete.svg' /></div>
                        <div className='actionIcon' ><img alt='' src='./assets/images/activate.svg' /></div>
                    </div>   

                    <div v-if='! record.active' className='actionColumn' :style="{width: col.width}"  >
                        <div className='actionIconNull'>&nbsp;</div>
                        <div className='actionIconNull'>&nbsp;</div>
                        <div className='actionIcon' ><img alt='' src='./assets/images/activate.svg' /></div>
                  </div> 
              </div> 

              <div v-else>
                  <div :style="{width: col.width, paddingLeft: '5px'}">Sim</div>
              </div>
    
            </div>

      </div>

    </div>

  </div>

</template>


<script setup>
import { slidingMessage, forceHideTolltip  } from '../assets/js/utils.js'
import { onMounted  } from 'vue';

const emit = defineEmits( ['showLoading', 'hideLoading','updateSelectedCar','setDatatableToDisplay','displaySchedule'] );
const props = defineProps( ['currentViewedDatatable', 'currentCountry', 'backendUrl', 'imagesUrl', 'expressions' ] )


// colunas que serao exibidias dependendo da tabela sendo vista (_currentMenuItem)
let columns = []

// car's datatable
let title = ''

if (props.currentViewedDatatable === 'cars')   {
  columns.push({ fieldname: "id", width: "5%", title: 'Id', id: 'col1', boolean: false },
              { fieldname: "name", width: "calc(95% - 150px)", title: 'Nome', id: 'col2', boolean: false} )
  title = props.expressions.cars_table
}


// ultima coluna, acoes (editar, excluir, etc)
columns.push( {name: 'actions', width: '150px', title: '', id: 3} )


//*****************************************************************************
//*****************************************************************************

onMounted( () => {

  // the only way to make the 'bookingsTable' stop overflowing the parent div, was to put its height manually
  // have no more time to make it with css now, but there may be a way with css
  // make this only once
  if ( $('.DatatableRows').height()=='0' ) {
    let hgt1 = $('#scheduleToolbar').height() 
    let hgt2 = $('#scheduleHeader').height()
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


})



//***************************************************************************
//*************************************************************************** 
async function fetchData() {
  emit('showLoading')

  let language = isUSASelected.value ? 'english' : 'portuguese';

  await fetch(`${backendUrl.value}/${props.currentViewedDatatable}/${language}`)

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.json()
  })
  .then((data) => {
    emit('hideLoading')
    records = data;        
  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  
}

</script>