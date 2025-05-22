
<template>

<!-- car form container  -->
<div  class="flex flex-col w-[95%] max-w-[1300px] overflow-hidden pt-8 "  id='carRecordForm'>

  <div  class="flex flex-col w-full bg-white relative rounded-lg"  >

    <!-- title and close button  -->
    <div id='divWINDOW_TOP'>
      
      <div id='divWINDOW_TITLE'>

        <div v-if="formHttpMethodApply=='POST'">
          {{ expressions.new_car }}
        </div>
        <div v-if="formHttpMethodApply=='PATCH'">
          {{ expressions.edit_car }}
        </div>

      </div>

      <div class='flex flex-row '>
          <div id='divWINDOW_DRAG' class='mr-8'   >
            &nbsp;
          </div>

          <div class='divWINDOW_BUTTON mr-2'  aria-hidden="true" @click='userNeedsHelp' >
            &nbsp;&nbsp;[ ? ]&nbsp;&nbsp;
          </div>

          <div class='divWINDOW_BUTTON mr-6'  @click="this.$emit('closeCarForm')"  aria-hidden="true" > 
            &nbsp;&nbsp;[ X ]&nbsp;&nbsp;
          </div>
      </div>
    
    </div>

    <!-- form fields below -->
    <div class="flex flex-col w-full h-auto  px-4 my-6" >

      <!-- pick up/drop off date/time, driver name -->
      <div class="flex flex-row w-full gap-[10px] border-b-2 pb-4">

          <!-- pick up date/time -->
        <div class="flex flex-col w-full ">
          <div class="flex flex-row w-full pb-2  gap-6">   
            <div class='w-1/2'>{{ expressions.description }}:</div>
            <div class='w-1/2'>{{ expressions.plate }}</div>
          </div>

          <div class="flex flex-row w-full pb-2 gap-6 ">  
            <div class='w-1/2'>
              <input type="text" autocomplete="off" sequence="1"   id="txtDescription" maxlength='100' class='text_formFieldValue w-full' @focus="$event.target.select()" >  
            </div>
            <div class='w-1/2'>
              <input type="text" autocomplete="off" sequence="2"   id="txtPlate" maxlength='50' class='text_formFieldValue w-full' @focus="$event.target.select()" >  
            </div>
          </div>

        </div>


      </div>

      <!-- car's picture -->
      <div class="flex flex-row w-full gap-[10px] h-[200px] items-end ">
          <img id='carPicture'  alt='' style='width:400px;height:200px;border-white' >
       </div>


    </div>

    <!-- botoes salvar/sair -->
    <div class="flex flex-row w-full justify-between px-6 border-t-[1px] border-t-gray-300 py-2">
      <button  id="btnCLOSE" class="btnCANCEL" @click="emit('closeCarForm')" >{{ expressions.button_cancel }}</button>

      <button  id="btnSAVE" class="btnSAVE" @click="performSaveCarRecord()" aria-hidden="true">{{ expressions.button_save }}</button>
    </div>


  </div> 

</div> 

</template>


<script setup>
import { onMounted  } from 'vue';
import { makeWindowDraggable, slidingMessage, dateToIsoStringConsideringLocalUTC, formatDate  } from '../assets/js/utils.js'
const emit = defineEmits( ['showLoading', 'hideLoading', 'closeCarForm'] );

import moment from 'moment';

const props = defineProps( ['expressions', 'backendUrl', 'currentCountry', 'formHttpMethodApply', 'currentId', 'imagesUrl'] )

onMounted( () => {
  getCarFormPopulatedAndReady()
})

//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
const userNeedsHelp = () => {
  slidingMessage(props.expressions.user_needs_help, 3000)
}


/************************************************************************************************************************************************************
get data from the car record
************************************************************************************************************************************************************/

async function getCarFormPopulatedAndReady() { 

  // if car form was not called to record insertion, first fetch record data
  if ( props.formHttpMethodApply != 'POST')   {

    emit('showLoading')

    try {
        let _route_ = `${props.backendUrl}/cars/${props.currentId}`

        await fetch(_route_, {method: 'GET'})

        .then( (response) => {

          if (!response.ok) {
            throw new Error(`Car Read Err Fatal= ${response.status}`);
          }
          return response.json();
        })

        .then( (car) => {
          emit('hideLoading')
          $('#txtDescription').val( car.description )
          $('#txtPlate').val( car.plate )

          $('#carPicture').attr('src', props.imagesUrl + car.car_image )

          putFocusInFirstInputText_AndOthersParticularitiesOfTheCarForm() 


        })


    } 
    catch(err) {
      emit('hideLoading')
      throw new Error(`Cars Prepare Err Fatal= ${err.message}`);
    }

  }

  // car form was called to add new record
  if ( props.formHttpMethodApply === 'POST')   {
    putFocusInFirstInputText_AndOthersParticularitiesOfTheCarForm()
  }

}


/************************************************************************************************************************************************************
put focus first field and prepare masks
************************************************************************************************************************************************************/
const putFocusInFirstInputText_AndOthersParticularitiesOfTheCarForm = () => { 

  setTimeout(() => {
    $('#txtDescription').focus()    
  }, 500);

  makeWindowDraggable('divWINDOW_TOP', 'carRecordForm')
}





/********************************************************************************************************************************************************
 validate dado data from the form and try to save it
********************************************************************************************************************************************************/
async function  performSaveCarRecord()  {

  let error = ''

  
  if ( $('#txtDescription').val().trim().length < 3 )  error = props.expressions.missing_car_description
  if ( $('#txtPlate').val().trim().length < 3 )  error = props.expressions.missing_driver_name


  // show any error detected
  if (error!='') {
    slidingMessage(error, 3000)
    return;
  }

  var formData = new FormData(); 

  let route = ''
  if (props.formHttpMethodApply=='POST') 
    route += 'car'        
  if (props.formHttpMethodApply=='PATCH') 
    route += `car/${props.currentId}`   

  // formHttpMethodApply= POST, PATCH ou DELETE
  setTimeout(() => {
    emit('showLoading')    
  }, 10);
  
  // PHP doesnt work weel with PATCH (laravel does), need to send all with POST here
  await fetch(`${props.backendUrl}/${route}`, {method: 'POST', body: formData})

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.text()
  })
  .then((msg) => {
    slidingMessage(props.expressions.car_recorded, 3000)        
    emit('hideLoading')
    setTimeout(() => {
      emit('closeCarForm')  
//      emit('refreshBookingDatesAndContent')  

    }, 3100);
    
  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  

}



</script>
