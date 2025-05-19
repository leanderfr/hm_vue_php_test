
<template>

<!-- booking form container  -->
<div  class="flex flex-col w-[95%] max-w-[1300px] overflow-hidden pt-8 "  id='bookingRecordForm'>

  <div  class="flex flex-col w-full bg-white relative rounded-lg"  >

    <!-- title and close button  -->
    <div id='divWINDOW_TOP'>
      
      <div id='divWINDOW_TITLE'>

        <div v-if="formHttpMethodApply=='POST'">
          {{ expressions.new_booking }}
        </div>
        <div v-if="formHttpMethodApply=='PATCH'">
          {{ expressions.edit_booking }}
        </div>

      </div>

      <div class='flex flex-row '>
          <div class='bg-icon-div-draggable w-7 bg-transparent bg-no-repeat bg-contain bg-center mr-6 containsTooltip'   >
            &nbsp;
          </div>


          <div class='divWINDOW_BUTTON mr-2'  aria-hidden="true" @click='userNeedsHelp' >
            &nbsp;&nbsp;[ ? ]&nbsp;&nbsp;
          </div>

          <div class='divWINDOW_BUTTON mr-6'  @click="this.$emit('closeBookingForm')"  aria-hidden="true" > 
            &nbsp;&nbsp;[ X ]&nbsp;&nbsp;
          </div>
      </div>
    
    </div>

    <!-- form fields below -->
    <div class="flex flex-col w-full h-auto  px-4 my-6 " >

      <!-- pick up/drop off date/time, driver name -->
      <div class="flex flex-row w-full gap-[10px] border-b-2 pb-1">

          <!-- pick up date/time -->
        <div class="flex flex-col w-[calc(40%-10px)] ">
          <div class="flex flex-row w-full  ">  
            <div class='w-[69%]'>{{ expressions.pick_up_car }}:</div>
            <div class='w-[30%]'>{{ expressions._hour_ }}</div>
          </div>

          <div class="flex flex-row w-full  ">  

              <div class="flex w-[70%] pr-3">  
                
                  <!-- data -->
                  <div class='flex flex-col w-full'  >
                      <div class='flex flex-row w-full' >
                        <input type="text" autocomplete="off" sequence="1"   id="txtPickUpDate" :placeholder='expressions.date_placeholder'  maxlength='10' class='text_formFieldValue w-full' @focus="$event.target.select()" >  
                      </div>
                  </div>

              </div>

              <!-- hora -->
              <div class="flex w-[30%]">  
                <div class='flex flex-col w-full'  >
                  <input type="text" autocomplete="off" sequence="2"  id="txtPickUpHour" :placeholder='expressions.hour_placeholder' maxlength='8' class='text_formFieldValue w-full' @focus="$event.target.select()" >
                </div>
              </div>

          </div>
        </div>

          <!-- data/hora devolucao do veiculo -->
        <div class="flex flex-col w-[calc(40%-10px)]">

          <div class="flex flex-row w-full  ">  
            <div class='w-[69%]'>{{ expressions.drop_off_car }}:</div>
            <div class='w-[30%]'>{{ expressions._hour_ }}</div>
          </div>
          <div class="flex flex-row w-full ">  

                <div class="flex w-[70%]  pr-3">  

                    <!-- data -->
                    <div class='flex flex-col w-full'  >
                        <div class='flex flex-row w-full' >
                            <input type="text" autocomplete="off" sequence="3"  id="txtDropOffDate" :placeholder='expressions.date_placeholder' maxlength='10' class='text_formFieldValue w-full' @focus="$event.target.select()" >
                        </div>  
                    </div>  

                </div>  

                <!-- hora -->
                <div class="flex w-[30%]">  
                  <div class='flex flex-col w-full'  >
                      <input type="text" autocomplete="off" sequence="4"  id="txtDropOffHour" :placeholder='expressions.hour_placeholder' maxlength='5' class='text_formFieldValue w-full' @focus="$event.target.select()" >
                  </div>
                </div>  
                
          </div>
        </div>

        <!-- nome motorista -->
        <div class="flex flex-col w-[20%]">
          <div class='w-full'>{{ expressions.driver_name }}:</div>
          <div class="flex flex-row w-full">  
                <div class="flex flex-col w-full">  
                  <input type="text" autocomplete="off" sequence="5"  id="txtDriverName" maxlength='50' class='text_formFieldValue w-full' @focus="$event.target.select()" >
                </div>
          </div>
        </div>

      </div>

      <!-- 1a linha, data/hora retirada e devolucao veiculo, nome do motorista -->
      <div class="flex flex-row w-full gap-[10px] h-[350px] items-end">

            <!-- putWhiteTooltip  nao temm propriedades CSS, é usado somente para endereçar TOOLTIP no jscript!   -->

            <!-- logo fabricante, modelo carro, foto carro -->
            <div class="flex w-[60%] h-full ">

                  <div class="w-full flex  items-start  flex-col pt-3 pl-3 h-full   ">

                      <!--  foto do carro --> 
                      <div class="flex flex-row w-full h-[80%] justify-center "> 

                          <div class="bg-no-repeat bg-center bg-contain w-full h-full items-center flex justify-center" id='carDetails_Picture'>
                          
<!--                                  {#if $imagesStillLoading} 
                                  <img id='carDetails_Picture'  alt=''  class='gifImageLoading' src='loading.gif'>
                              {/if}
-->


                          </div>
                          
                      </div>

                  </div>

            </div>


        </div>  

    </div>

    <!-- botoes salvar/sair -->
    <div class="flex flex-row w-full justify-between px-6 border-t-[1px] border-t-gray-300 py-2">
      <button  id="btnCLOSE" class="btnCANCEL" @click="this.$emit('closeBookingForm')" >{{ expressions.button_cancel }}</button>

      <button  id="btnSAVE" class="btnSAVE" @click="performSaveBookingRecord()" aria-hidden="true">{{ expressions.button_book_car }}</button>
    </div>


  </div> 

</div> 

</template>


<script setup>
import { onMounted  } from 'vue';
import { slidingMessage, dateToIsoStringConsideringLocalUTC  } from './js/utils.js'
const emit = defineEmits( ['showLoading', 'hideLoading', 'closeBookingForm'] );

import moment from 'moment';

const props = defineProps( ['expressions', 'backendUrl', 'currentCountry', 'formHttpMethodApply', 'bookingIdEdit'] )

onMounted( () => {
  getBookingFormPopulatedAndReady()
})

//************************************************************************************************************************************************************
//************************************************************************************************************************************************************
const userNeedsHelp = () => {
  slidingMessage(props.expressions.user_needs_help, 3000)
}


/************************************************************************************************************************************************************
get data from the booking record
************************************************************************************************************************************************************/

async function getBookingFormPopulatedAndReady() { 

  // if booking form was not called to record insertion, first fetch record data
  if ( props.formHttpMethodApply != 'POST')   {

    emit('showLoading')

    try {
        let _route_ = `${props.backendUrl}/bookings/${props.currentCountry}/${props.bookingIdEdit}`

        await fetch(_route_, {method: 'GET'})

        .then( (response) => {

          if (!response.ok) {
            throw new Error(`Booking Read Err Fatal= ${response.status}`);
          }
          return response.json();
        })

        .then( (booking) => {
          emit('hideLoading')
          $('#txtPickUpDate').val( booking.pickup_date )
          $('#txtDropOffDate').val( booking.dropoff_date )
          $('#txtPickUpHour').val( booking.pickup_hour )
          $('#txtDropOffHour').val( booking.dropoff_hour )
          $('#txtDriverName').val( booking.driver_name )

          putFocusInFirstInputText_AndOthersParticularitiesOfTheBookingForm() 
        })


    } 
    catch(err) {
      emit('hideLoading')
      throw new Error(`Bookings Prepare Err Fatal= ${err.message}`);
    }

  }

  // booking form was called to add new record
  if ( props.formHttpMethodApply === 'POST')   {
    putFocusInFirstInputText_AndOthersParticularitiesOfTheBookingForm()
  }

}


/************************************************************************************************************************************************************
put focus first field and prepare masks
************************************************************************************************************************************************************/
const putFocusInFirstInputText_AndOthersParticularitiesOfTheBookingForm = () => { 

  // hour typing
  if ( props.currentCountry=='usa') {
    $.mask.definitions['h'] = "[A^Pa^p]"
    $('#txtPickUpHour, #txtDropOffHour').mask('99:99 hm', {placeholder:"hh:mm _m "})    // USA hour format
  } else {
    $('#txtPickUpHour, #txtDropOffHour').mask('99:99', {placeholder:"hh:mm"})           // Brazil
  }

  $('#txtPickUpDate, #txtDropOffDate').mask('99/99/99');   
  setTimeout(() => {
    $('#txtPickUpDate').focus()    
  }, 500);
}





/********************************************************************************************************************************************************
 valida dados do formulario e se tudo ok, tenta gravar
********************************************************************************************************************************************************/
async function  performSaveBookingRecord()  {

  let error = ''

  // validate dates / hours 

  //******************************************************************************************/
  // pick up date
  //******************************************************************************************/
  let _txtPickUpDate_ = $('#txtPickUpDate').val()
  let txtPickUpDate = _txtPickUpDate_.split('/')  
  let pickUpIso8601Format

  if (props.currentCountry == 'usa')  
    pickUpIso8601Format = '20'+txtPickUpDate[2] +'-' + txtPickUpDate[0] + '-'+txtPickUpDate[1]    // yyyy-mm-dd

  if (props.currentCountry == 'brazil')   
    pickUpIso8601Format = '20'+txtPickUpDate[2] +'-' + txtPickUpDate[1] + '-'+txtPickUpDate[0]    // yyyy-mm-dd


  if (! moment(pickUpIso8601Format).isValid())     error = props.expressions.pickup_date_error 

  //******************************************************************************************/
  // pick up hour
  //******************************************************************************************/

  let regex_usa = /^([0]\d|[1][0-2]):([0-5]\d)\s?(?:AM|PM)$/i;          
  let regex_brazil = /^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/;

  let txtPickUpHour = $('#txtPickUpHour').val()

  if   (   (props.currentCountry == 'usa'  &&  ! regex_usa.test(txtPickUpHour))     ||     (props.currentCountry == 'brazil' &&  ! regex_brazil.test(txtPickUpHour))    )
    error = props.expressions.pickup_hour_error

  

  
  //******************************************************************************************/
  // drop off date
  //******************************************************************************************/

  let _txtDropOffDate_ = $('#txtDropOffDate').val()
  let txtDropOffDate = _txtDropOffDate_.split('/')  
  let dropOffIso8601Format

  if (props.currentCountry == 'usa')  
    dropOffIso8601Format = '20'+txtDropOffDate[2] +'-' + txtDropOffDate[0] + '-'+txtDropOffDate[1]    // yyyy-mm-dd

  if (props.currentCountry == 'brazil')    
    dropOffIso8601Format = '20'+txtDropOffDate[2] +'-' + txtDropOffDate[1] + '-'+txtDropOffDate[0]    // yyyy-mm-dd

  if (! moment(dropOffIso8601Format).isValid())     error = props.expressions.dropoff_date_error


  //******************************************************************************************/
  // drop off hour
  //******************************************************************************************/

  let txtDropOffHour = $('#txtDropOffHour').val()

  if   (   (props.currentCountry == 'usa'  &&  ! regex_usa.test(txtDropOffHour))     ||     (props.currentCountry == 'brazil' &&  ! regex_brazil.test(txtDropOffHour))    )
    error = props.expressions.dropoff_hour_error

  // driver's name
  if ( $('#txtDriverName').val().trim().length < 3 )  error = props.expressions.missing_driver_name


  // show any error detected
  if (error!='') {
    slidingMessage(error, 3000)
    return;
  }


  var formData = new FormData(); 


  let pickUpHour, pickUpMinute, dropOffHour, dropOffMinute, pickupAlmostReady, dropoffAlmostReady

  
  // converts USA hour format (12 AM/PM) to HH:MM
  if (props.currentCountry == 'usa')  {
    pickUpHour = moment(txtPickUpHour, ["h:mm A"]).format("HH");
    pickUpMinute = moment(txtPickUpHour, ["h:mm A"]).format("mm");

    dropOffHour = moment(txtDropOffHour, ["h:mm A"]).format("HH");
    dropOffMinute = moment(txtDropOffHour, ["h:mm A"]).format("mm");

    pickupAlmostReady = new Date('20'+txtPickUpDate[2], parseInt(txtPickUpDate[0], 10)-1, txtPickUpDate[1], pickUpHour, pickUpMinute)   // mm/dd/yy
    dropoffAlmostReady = new Date('20'+txtDropOffDate[2], parseInt(txtDropOffDate[0], 10)-1, txtDropOffDate[1], dropOffHour, dropOffMinute)
  }

  if (props.currentCountry == 'brazil')  { 
    pickUpHour = moment(txtPickUpHour, ["HH:mm"]).format("HH");
    pickUpMinute = moment(txtPickUpHour, ["HH:mm"]).format("mm");

    dropOffHour = moment(txtDropOffHour, ["HH:mm"]).format("HH");
    dropOffMinute = moment(txtDropOffHour, ["HH:mm"]).format("mm");

    pickupAlmostReady = new Date('20'+txtPickUpDate[2], parseInt(txtPickUpDate[1], 10)-1, txtPickUpDate[0], pickUpHour, pickUpMinute)  // dd/mm/yy
    dropoffAlmostReady = new Date('20'+txtDropOffDate[2], parseInt(txtDropOffDate[1], 10)-1, txtDropOffDate[0], dropOffHour, dropOffMinute)
  }

  // drop off date must be greater than pick up
  let datesDifference = ( dropoffAlmostReady - pickupAlmostReady ) / 36e5;
  if (datesDifference < 0) {
    slidingMessage(props.expressions.dropoff_greater_error, 3000)
    $('#txtDropOffDate').focus() 
    return
  }

  // boooking must be at least  1 hour in advance
  let _minimumDate = new Date();  
  let minimumDate = _minimumDate.getTime() + (1*60*60*1000);   // add 1 hour

  let datesDifference1 = ( dropoffAlmostReady - minimumDate ) / 36e5;
  let datesDifference2 = ( pickupAlmostReady - minimumDate ) / 36e5;

  if (datesDifference1 < 0 || datesDifference2 < 0) {
    slidingMessage(props.expressions.booking_hour_advance, 3000)
    $('#txtDropOffDate').focus() 
    return
  }

  formData.append('dropoff_datetime', dateToIsoStringConsideringLocalUTC(dropoffAlmostReady))    //  iso8601 datetime format
  formData.append('pickup_datetime', dateToIsoStringConsideringLocalUTC(pickupAlmostReady))    //  iso8601 datetime format
  formData.append('driver_name', $('#txtDriverName').val())
  formData.append('car_id', 1)


  let route = ''
  if (props.formHttpMethodApply=='POST') 
    route += 'booking'        
  if (props.formHttpMethodApply=='PATCH') 
    route += `booking/${props.bookingIdEdit}`   

  // formHttpMethodApply= POST, PATCH ou DELETE
  setTimeout(() => {
    emit('showLoading')    
  }, 10);
  
  // PHP doesnt work weel with PATCH (laravel does), need to send all with POST here
  //await fetch(`${props.backendUrl}/booking`, {method: props.formHttpMethodApply, body: formData})
  await fetch(`${props.backendUrl}/${route}`, {method: 'POST', body: formData})

  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.text()
  })
  .then((msg) => {
    slidingMessage(props.expressions.boooking_recorded, 3000)        
    emit('hideLoading')
    setTimeout(() => {
      emit('closeBookingForm')  
    }, 3100);
    
  })
  .catch((error) => {
    emit('hideLoading')
    slidingMessage('Fatal error= '+error, 3000)        
  })  


}













</script>
