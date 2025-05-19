
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


              <div class='divWINDOW_BUTTON mr-2'  aria-hidden="true" >
                &nbsp;&nbsp;[ ? ]&nbsp;&nbsp;
              </div>

              <div class='divWINDOW_BUTTON mr-6'  on:click={closeBookingModalWindow} aria-hidden="true" >
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
          <button  id="btnCLOSE" class="btnCANCEL" on:click={closeBookingModalWindow} >{{ expressions.button_cancel }}</button>

          <button  id="btnSAVE" class="btnSAVE" @click="performSaveBookingRecord()" aria-hidden="true">{{ expressions.button_book_car }}</button>
        </div>


    </div> 

</div> 

</template>


<script setup>
import { onMounted  } from 'vue';

const props = defineProps( ['expressions', 'backendUrl', 'currentCountry', 'formHttpMethodApply', 'bookingIdEdit'] )

onMounted( () => {
  getBookingFormPopulatedAndReady()
})

const teste = (e) => {

console.log(e)

}


/************************************************************************************************************************************************************
get data from the booking record
************************************************************************************************************************************************************/

async function getBookingFormPopulatedAndReady() { 

  // if booking form was not called to record insertion, first fetch record data
  if ( props.formHttpMethodApply != 'POST')   {

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
          $('#txtPickUpDate').val( booking.pickup_date )
          $('#txtDropOffDate').val( booking.dropoff_date )
          $('#txtPickUpHour').val( booking.pickup_hour )
          $('#txtDropOffHour').val( booking.dropoff_hour )
          $('#txtDriverName').val( booking.driver_name )

          setTimeout(() => { $('#txtPickUpDate').focus()  }, 500);
          
        })


    } 
    catch(err) {
      throw new Error(`Bookings Prepare Err Fatal= ${err.message}`);
    }

  }
 
}



</script>
