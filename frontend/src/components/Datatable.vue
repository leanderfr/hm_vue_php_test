
<template>

    <div>   <!-- required (by Vue) fragment -->


    <div class='flex flex-col h-full ' id='datatableContainer'>

      <!-- top tool bar -->
      <div class="flex flex-row h-[60px] w-full justify-between border-b-2 " id='datatableToolbar'>


          <div></div>

          <!-- action buttons -->
          <div class="flex flex-row pt-1">

              <!-- schedule  -->
              <div  class='btnSCHEDULE_TABLE putPrettierTooltip' :title="expressions.schedule" @click="forceHideTolltip();emit('toDisplaySchedule')" aria-hidden="true"></div>   

              <!-- cars table -->
              <div  class='btnCARS_TABLE putPrettierTooltip' :title="expressions.cars" @click="forceHideTolltip();emit('setDatatableToDisplay', 'cars')" aria-hidden="true"></div>   

              <!-- expressions table -->
              <div  class='btnEXPRESSIONS_TABLE putPrettierTooltip' :title="expressions.expressions" @click="forceHideTolltip();emit('setDatatableToDisplay', 'expressions')" aria-hidden="true"></div>   

          </div> 

      </div>

      <div class='datatableTitle' >
        <div style='padding-left:10px'> {{ title }} </div>
        <div class='gap-5 flex flex-row'>

          <div v-if="currentStatus=='active'" class='btnTABLE_ONLY_ACTIVE_RECORDS_ON putPrettierTooltip' 
                  :title="expressions.only_active" 
                  @click="forceHideTolltip();currentStatus=''" 
                  aria-hidden="true"></div>   

          <div v-else class='btnTABLE_ONLY_ACTIVE_RECORDS_OFF putPrettierTooltip' 
                :title="expressions.only_active" 
                @click="forceHideTolltip();currentStatus='active'" 
                aria-hidden="true"></div>   

          <div v-if="currentStatus=='inactive'" class='btnTABLE_ONLY_INACTIVE_RECORDS_ON putPrettierTooltip' 
                :title="expressions.only_inactive" 
                @click="forceHideTolltip();currentStatus=''" 
                aria-hidden="true"></div>   

          <div v-else class='btnTABLE_ONLY_INACTIVE_RECORDS_OFF putPrettierTooltip' 
              :title="expressions.only_inactive" 
                  @click="forceHideTolltip();currentStatus='inactive'" 
              aria-hidden="true"></div>   

          <div  class='btnTABLE_NEW_RECORD putPrettierTooltip' :title="expressions.add_record" @click="editForm();" aria-hidden="true"></div>   


        </div>
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
                      <div className='actionIcon' @click='editForm(record.id)' ><img alt=''  src='../assets/images/edit.svg' /></div>
                      <div className='actionIcon'  @click='deleteRecord'><img alt=''   src='../assets/images/delete.svg' /></div>
                      <div className='actionIcon' @click='changeStatus(record.id)'><img alt=''  src='../assets/images/active.svg' /></div>
                  </div>   

                  <!-- the last column was printed above and the current record is inactive, put only the icon to reactivate -->
                  <div v-if='index === columns.length-1 && record.active==0' className='actionColumn' :style="{width: column.width}" :key="'1tr-'+index"  >
                      <div className='actionIconNull'>&nbsp;</div>
                      <div className='actionIconNull'>&nbsp;</div>
                      <div className='actionIcon' @click='changeStatus(record.id)'><img alt=''   src='../assets/images/inactive.svg' /></div>
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
        :currentId='currentId'
        :imagesUrl='props.imagesUrl'
        :formHttpMethodApply = 'formHttpMethodApply'  
        @closeCarForm="showCarForm=false"  
        @showLoading="emit('showLoading')" 
        @hideLoading="emit('hideLoading')" 
        @refreshDatatable = "fetchData();emit('toRefreshCarsBrowser');"   />
  </div>



</div>

</template>


<script setup>
import { slidingMessage, forceHideTolltip , divStillVisible } from '../assets/js/utils.js'
import { onMounted, ref, watch  } from 'vue';
import CarForm from './CarForm.vue';

const emit = defineEmits( ['showLoading', 'hideLoading','setDatatableToDisplay','displaySchedule', 'toRefreshCarsBrowser'] );
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

if (props.currentViewedDatatable === 'expressions')   {
  columns.push({ fieldname: "id", width: "5%", title: 'Id', id: 'col1', boolean: false },
              { fieldname: "item", width: "calc(25% - 150px)", title: props.expressions.item, id: 'col2', boolean: false},
              { fieldname: "english", width: "35%", title: props.expressions.field_english, id: 'col3', boolean: false},
              { fieldname: "portuguese", width: "35%", title: props.expressions.field_portuguese, id: 'col4', boolean: false} )
  title = props.expressions.expressions_table
}


// ultima coluna, acoes (editar, excluir, etc)
columns.push( {name: 'actions', width: '150px', title: '', id: 3} )


// id of the current car being edited or viewed
const currentId = ref(null)

// method being used with the booking form
const formHttpMethodApply = ref(null)

// which type of status should be viewed at the moment, active or inactive
const currentStatus = ref('active')  



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
// if user changes current status, refresh table base on the last choice
//*************************************************************************** 
watch([currentStatus], () => { 
  fetchData()
  },
  { immediate: false }
)



//***************************************************************************
//*************************************************************************** 
async function fetchData() {
  emit('showLoading')

  // if no status set, fetch all records
  let realStatus = currentStatus.value
  if (realStatus==='') realStatus='all'

  // 1 exception, expressions must inform what country/language
  let route
  if ( props.currentViewedDatatable === 'expressions')   
    route = `${props.backendUrl}/expressions/json/${props.currentCountry}/${realStatus}`

  else 
    route = `${props.backendUrl}/${props.currentViewedDatatable}/${realStatus}`

  await fetch(route) 
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
// user click in a given record to edit or ask to add new record (id='')
//*************************************************************************** 
const editForm = (id='') => {
  currentId.value = id;

  if (props.currentViewedDatatable === 'cars')   {  
    showCarForm.value = true
    if (id=='') formHttpMethodApply.value = 'POST'  // add record
    else formHttpMethodApply.value = 'PATCH'  // update record
  }
}

//***************************************************************************
// user click in a given record to change its status (active/inactive)
//*************************************************************************** 
async function changeStatus (id) {

  let route = ''
  if (props.currentViewedDatatable === 'cars')   {  
    route = `${props.backendUrl}/${props.currentViewedDatatable}/status/${id}`
  }

  emit('showLoading')

  await fetch(route, {method: 'POST'})

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.text()
  })
  .then((data) => {
    slidingMessage(props.expressions.status_changed, 3000)         
    fetchData()
    // ask App.vue to refresh cars list, once one record's been activate/deactivated
    emit('toRefreshCarsBrowser') 
  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  
}


//***************************************************************************
// user click in a given record to delete
//*************************************************************************** 
const deleteRecord = (id) => {

  slidingMessage(props.expressions.unavailable_option, 3000)        

}


</script>