
<template>

    <div>   <!-- required (by Vue) fragment -->


    <div class='flex flex-col h-full ' id='datatableContainer'>

      <!-- top tool bar -->
      <div class="flex flex-row h-[60px] w-full justify-between border-b-2 " id='datatableToolbar'>


          <div></div>

          <!-- navigation buttons -->
          <div class="flex flex-row pt-1 w-full justify-between">
              <div class='pl-4 pt-2 text-2xl'> {{ title }} </div>

              <div class='flex flex-row'>
                  <!-- schedule icon  -->
                  <div  class='btnSCHEDULE_TABLE putPrettierTooltip' :title="expressions.schedule" @click="forceHideTolltip();emit('toDisplaySchedule')" aria-hidden="true"></div>   

                  <!-- cars table icon -->
                  <div  class='btnCARS_TABLE putPrettierTooltip' 
                    :title="expressions.cars" 
                    @click="forceHideTolltip();emit('setDatatableToDisplay', 'cars')" 
                    aria-hidden="true"></div>   


                  <!-- expressions table icon -->
                  <div  class='btnEXPRESSIONS_TABLE putPrettierTooltip' :title="expressions.expressions" @click="forceHideTolltip();emit('setDatatableToDisplay', 'expressions')" aria-hidden="true"></div>   
                </div>
          </div> 

      </div>

      <div class='datatableTitle' >
        <div class='flex flex-row w-full'>

            <!--  search box --> 
            <div class="flex flex-col" >  
              <input type="text" class='txtTABLE_SEARCHBOX'  id='txtTableSearchText'  autocomplete="off" 
                  @focus='showTipSearchbox=true' 
                  @blur='showTipSearchbox=false' 
                  @mouseenter="focusSearchBox" />

              <div class="flex flex-row pt-1 text-xs"  >  
                  <div v-if="showTipSearchbox">
                    <span class="text-blue-900 font-bold">Enter</span>
                    <span class="text-black">= {{ expressions.search_verb }}</span>
                  </div>
                  <div v-else>&nbsp;</div> 
              </div>

              <button id='teste' v-show="false" @click='fetchData()'></button>
            </div>

            <!-- button to reset filter --> 
            <div id='btnResetTextTableFilter'   
                :class="filterApplied ? 'btnTABLE_CANCEL_FILTER_ACTIVE' : 'btnTABLE_CANCEL_FILTER_INACTIVE'"
                @click="filterApplied.value=false"  aria-hidden="true">
            </div> 
          
        </div>


        <!-- action buttons -->
        <div class=' flex flex-row items-start  h-full gap-5 pt-3 '>

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
      <template v-if='records'>
        <div id='rowsContainer'>
          <div  class="DatatableRows" v-for='record in records' :key="record"  @click="rowClicked('tr_'+record.id)" :id="'tr_'+record.id"> 

              <!-- if the record status= true (activate), row will be normal color, otherwise (inactive), color will be red -->
              <div :class="record.active ? 'DatatableRow' : 'DatatableRowInactiveRecord'"   >         

                <!-- loop through the columns -->
                <template v-for='(column, index) in columns'   >         

                  <div v-if='index < (columns.length-1)'  
                      :style="{width: column.width, paddingLeft: '15px'}" 
                      :key="'1tr-'+index" 
                     :class="record.active==0 ? 'text-red-500 font-bold' : 'text-black'"  
                     class='datatableColumn'  >
                    {{ record[column.fieldname] }}
                  </div>

                  <!-- if the last column was printed and the current record is active, now put the 3 action icons (edit, delete and change status) -->
                  <div v-if='index === columns.length-1 && record.active==1' class='actionColumn' :style="{width: column.width}" :key="'1tr-'+index" >
                      <div class='actionIcon' @click='editForm(record.id)' ><img alt=''  src='../assets/images/edit.svg' /></div>
                      <div class='actionIcon'  @click='deleteRecord'><img alt=''   src='../assets/images/delete.svg' /></div>
                      <div class='actionIcon' @click='changeStatus(record.id)'><img alt=''  src='../assets/images/active.svg' /></div>
                  </div>   

                  <!-- if the last column was printed and the current record is inactive, put only the icon to reactivate -->
                  <div v-if='index === columns.length-1 && record.active==0' class='actionColumn' :style="{width: column.width}" :key="'1tr-'+index"  >
                      <div class='actionIconNull'>&nbsp;</div>
                      <div class='actionIconNull'>&nbsp;</div>
                      <div class='actionIcon' @click='changeStatus(record.id)'><img alt=''   src='../assets/images/inactive.svg' /></div>
                  </div> 

              </template>
            </div>
          </div>
      </div>
      </template>

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


  <div v-if="showExpressionForm" id='backDrop' class='w-full h-full  absolute flex items-center justify-center left-0 top-0 z-10 bg-[rgba(0,0,0,0.5)]' @click.self='showExpressionForm=false' aria-hidden="true"  >  
    <ExpressionForm 
        :expressions='expressions' 
        :backendUrl='props.backendUrl' 
        :currentId='currentId'
        :formHttpMethodApply = 'formHttpMethodApply'  
        @closeExpressionForm="showExpressionForm=false"  
        @showLoading="emit('showLoading')" 
        @hideLoading="emit('hideLoading')" 
        @refreshDatatable = "fetchData();emit('toRefreshExpreCarsBrowser');"   />
  </div>



</div>

</template>


<script setup>
import { slidingMessage, forceHideTolltip , divStillVisible } from '../assets/js/utils.js'
import { onMounted, ref, watch  } from 'vue';
import CarForm from './CarForm.vue';
import ExpressionForm from './ExpressionForm.vue';

const emit = defineEmits( ['showLoading', 'hideLoading','setDatatableToDisplay','toDisplaySchedule', 'toRefreshCarsBrowser'] );
const props = defineProps( ['currentViewedDatatable', 'currentCountry', 'backendUrl', 'imagesUrl', 'expressions' ] )

// records that are gonna be showed in the datatable
const records = ref(null)  

// controls if the car form need to be opened
const showCarForm = ref(false)  

// controls if the expression form need to be opened
const showExpressionForm = ref(false)

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
const currentStatus = ref('')  

// if must show the 'Press Enter to search' message underneath the search box
const showTipSearchbox = ref(false)

// controls if the searchbox filter was applied
const filterApplied = ref(false)  



//*****************************************************************************
//*****************************************************************************
const applyFilter = () => {

  juca
  filterApplied.value=true


}
//*****************************************************************************
//*****************************************************************************
const rowClicked = (id) =>   {
  let tr = $(`#${id}`)

  $('.DatatableRow_selected').removeClass('DatatableRow_selected')
  tr.addClass('DatatableRow_selected')

}

//*****************************************************************************
// if users hovers mouse over search box, put the focus in it 
//*****************************************************************************
const focusSearchBox = (e) => {
  if (! $(e.target).is(':focus') ) $(e.target).focus()
}

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
watch([currentStatus, filterApplied], () => { 
  fetchData()
  },
  { immediate: false }
)



//***************************************************************************
//*************************************************************************** 
async function fetchData() {
  emit('showLoading')

  $('#rowsContainer').height('0')
  // if no status set, fetch all records
  let realStatus = currentStatus.value
  if (realStatus==='') realStatus='all'

  // 1 exception, expressions must inform what country/language
  // if user fullfilled searchbox, consider it
  // what field will be searched in the backend depends on the table, the backend decides
  // if a text is specified to search for, the status will be ignored in the backend
  let stringSearch = $.trim( $('#txtTableSearchText').val() )
  let route
  if ( props.currentViewedDatatable === 'expressions')   
    route = `${props.backendUrl}/expressions/json/${props.currentCountry}/${realStatus}` +  (stringSearch!='' ? `/${stringSearch}` : '')

  else 
    route = `${props.backendUrl}/${props.currentViewedDatatable}/${realStatus}`  +  (stringSearch!='' ? `/${stringSearch}` : '')

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

  if (id=='') formHttpMethodApply.value = 'POST'  // add record
  else formHttpMethodApply.value = 'PATCH'  // update record

  if (props.currentViewedDatatable === 'cars')   { 
    showCarForm.value = true
  }

  if (props.currentViewedDatatable === 'expressions')   {  
    showExpressionForm.value = true
  }

}

//***************************************************************************
// user click in a given record to change its status (active/inactive)
//*************************************************************************** 
async function changeStatus (id) {

  let route = `${props.backendUrl}/${props.currentViewedDatatable}/status/${id}`

  emit('showLoading')

  await fetch(route, {method: 'PATCH'})

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