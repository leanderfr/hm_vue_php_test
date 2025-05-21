
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
        <div style='padding-left:10px'> {{ title }} </div>
        <div style='padding-top: 10px; padding-right:20px;font-size: 14px'>{{expressions.legend}}: <span style=' background-color: red'>&nbsp;&nbsp;&nbsp;</span>= {{expressions.inactive}}</div>
      </div>

      <!-- loop to display each column -->
      <div class="datatableHeader" > 
            <div v-for='column in columns' :key="column" :style="{width: column.width, paddingLeft: '5px'}">{{ column.title }}</div> 
      </div>          

      <!-- display records from the current table -->
      <div v-if='records'>
        <div id='rowsContainer'>
          <div  class="DatatableRows" v-for='record in records' :key="record" id="DatatableRows"> 

              <!-- if the record status= true (activate), row will be normal color, otherwise (inactive), color will be red -->
              <div :class="record.active ? 'DatatableRow' : 'DatatableRowInactiveRecord'"   >         

                <!-- loop through the columns -->
                <template v-for='(column, index) in columns'   >         

                  <div v-if='index < (columns.length-1)'  
                      :style="{width: column.width, paddingLeft: '15px'}" 
                      :key="'1tr-'+index" 
                      :class="record.active==0 ? 'text-red-500 font-bold' : 'text-black'"  >
                    {{ record[column.fieldname] }}
                  </div>

                  <!-- the last column was printed above and the current record is active, now put the 3 action icons (edit, delete and change status) -->
                  <div v-if='index === columns.length-1 && record.active==1' className='actionColumn' :style="{width: column.width}" :key="'1tr-'+index" >
                      <div className='actionIcon'  ><img alt='' @click='callRecordEdit(record.id)' src='../assets/images/edit.svg' /></div>
                      <div className='actionIcon'  ><img alt=''  src='../assets/images/delete.svg' /></div>
                      <div className='actionIcon' ><img alt='' src='../assets/images/activate.svg' /></div>
                  </div>   

                  <!-- the last column was printed above and the current record is inactive, put only the icon to reactivate -->
                  <div v-if='index === columns.length-1 && record.active==0' className='actionColumn' :style="{width: column.width}" :key="'1tr-'+index"  >
                      <div className='actionIconNull'>&nbsp;</div>
                      <div className='actionIconNull'>&nbsp;</div>
                      <div className='actionIcon' ><img alt='' src='../assets/images/activate.svg' /></div>
                  </div> 

              </template>
            </div>
          </div>
      </div>
      </div>

  </div>


  <div v-if="showCarForm" id='backDrop' class='w-full h-full  absolute flex items-center justify-center left-0 top-0 z-10 bg-[rgba(0,0,0,0.5)]' @click.self='showCarForm=false' aria-hidden="true"  >  
    <CarForm 
        :expressions='expressions' 
        :backendUrl='props.backendUrl' 
        :recordIdSelected='recordIdSelected'
        :imagesUrl='props.imagesUrl'
        :formHttpMethodApply = 'formHttpMethodApply'  
        @closeCarForm="showBookingForm=false" 
        @showLoading="emit('showLoading')" 
        @hideLoading="emit('hideLoading')" 
        @refreshDatatable = "refreshDatatable"   />
  </div>



</div>

</template>


<script setup>
import { slidingMessage, forceHideTolltip , divStillVisible } from '../assets/js/utils.js'
import { onMounted, ref  } from 'vue';
import CarForm from './CarForm.vue';

const emit = defineEmits( ['showLoading', 'hideLoading','updateSelectedCar','setDatatableToDisplay','displaySchedule'] );
const props = defineProps( ['currentViewedDatatable', 'currentCountry', 'backendUrl', 'imagesUrl', 'expressions' ] )

// records that are gonna be showed in the datatable
const records = ref(null)  

// controls if the edit form need to be opened
const showCarForm = ref(false)  

// colunas que serao exibidias dependendo da tabela sendo vista (_currentMenuItem)
let columns = []

// car's datatable
let title = ''

if (props.currentViewedDatatable === 'cars')   {
  columns.push({ fieldname: "id", width: "5%", title: 'Id', id: 'col1', boolean: false },
              { fieldname: "description", width: "calc(75% - 150px)", title: props.expressions.description, id: 'col2', boolean: false},
              { fieldname: "plate", width: "20%", title: props.expressions.plate, id: 'col2', boolean: false} )
  title = props.expressions.cars_table
}

// ultima coluna, acoes (editar, excluir, etc)
columns.push( {name: 'actions', width: '150px', title: '', id: 3} )


// id of the current car being edited or viewed
const recordIdSelected = ref(null)

// method being used with the booking form
const formHttpMethodApply = ref(null)



//*****************************************************************************
//*****************************************************************************
onMounted( () => {
    

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

  await fetch(`${props.backendUrl}/${props.currentViewedDatatable}/all`)

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.json()
  })
  .then((data) => {
    emit('hideLoading')
    records.value = data;        

    // strecth the div containing the records to the maximum
    setTimeout(() => {
        if( divStillVisible('rowsContainer') ) {
          while ( divStillVisible('rowsContainer') ) { $('#rowsContainer').height( $('#rowsContainer').height()+5 );     }
        }        
    }, 300);



  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  
}

//***************************************************************************
//*************************************************************************** 

const callRecordEdit = (id) => {
  recordIdSelected.value = id;

  if (props.currentViewedDatatable === 'cars')   {  
    showCarForm.value = true
    formHttpMethodApply.value = 'PATCH'
  }

}



</script>