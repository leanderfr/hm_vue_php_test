
<template>

  <div class='flex flex-col h-full ' id='scheduleContainer'>

    <!-- tool bar -->
    <div class="flex flex-row h-[60px] w-full justify-between border-b-2" id='scheduleToolbar'>

      <!-- current year -->
      <div class="flex flex-row text-2xl font-bold pt-4" id='currentYear'>ss</div>

      <!-- action buttons -->
      <div class="flex flex-row">
          <!-- new booking -->
          <div  class='btnBOOKING_ADD_CAR_RESERVATION putWhiteTooltip'  :title="expressions.new_booking"   @click="newBookingRecord" aria-hidden="true"></div>   

          <!-- display calendar -->
          <div  class='btnBOOKING_CALENDAR putWhiteTooltip' :title="expressions.choose_date" @click="showCalendar=true" aria-hidden="true"></div>    

          <!-- display all the cars reservations -->
          <div  class='btnBOOKING_ALL_CARS putWhiteTooltip' :title="expressions.display_all_cars" @click="bookingSelectedCarId=-1; showCarBooking()" aria-hidden="true"></div>    

          <!-- back 1 week   -->
          <div  class='btnBOOKING_LEFT_ARROW putWhiteTooltip'  :title="expressions.previous_week" @click="browseBookingCalendar(-7)" aria-hidden="true"></div>   

          <!-- forward 1 week -->
          <div  class='btnBOOKING_RIGHT_ARROW putWhiteTooltip' :title="expressions.next_week" @click="browseBookingCalendar(+7)" aria-hidden="true"></div>   
      </div> 

    </div>

    <!-- week days  -->
    <div class="w-full border-b-2 border-b-gray-300 text-lg py-1 " id='scheduleHeader' >  
        <div class="w-[calc(100%-22px)] flex flex-row text-gray-500  text-lg font-bold text-center h-12 justify-center cursor-pointer items-center" >
          <div class='w-[9%] tdBookingHeader'>&nbsp;</div>
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn0' bookings_this_day='' real_date=''>s</div> 
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn1' bookings_this_day='' real_date=''></div>
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn2' bookings_this_day='' real_date=''></div>
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn3' bookings_this_day='' real_date=''></div>
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn4' bookings_this_day='' real_date=''></div> 
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn5' bookings_this_day='' real_date=''></div>
          <div class='w-[13%] tdBookingHeader rounded-2xl' id='datecolumn6' bookings_this_day='' real_date=''></div>
        </div>
    </div>

    <!-- loop to display times from 05:00 to 23:00  -->
    <div class="w-full flex flex-col  overflow-y-scroll h-[20px]  border-l-0 border-gray-200 border-r-0  " id='bookingsTable' >  

      <div v-for="hour in counter(5, 24)" :key="hour" class="w-full flex flex-row  leading-[60px]  justify-center cursor-pointer border-b-2 border-gray-300 hover:bg-gray-100"  >
        <div class='w-[9%] tdBookingCell flex justify-center'>{{ hourFormat(hour, currentCountry) }}</div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay0{{hour}}' ></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay1{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay2{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay3{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay4{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay5{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay6{{hour}}'></div>
      </div>

    </div>



    </div>

  



</template>




<script setup>
import { onMounted  } from 'vue';
import { prepareLoadingAnimation, slidingMessage, counter, hourFormat  } from './js/utils.js'

const props = defineProps( ['expressions', 'currentCountry'] )

onMounted( () => {
  refreshBookingDatesAndContent()
})


// auxilia a tela de reservas de carros no componente Bookings.svelte
let today = new Date(); 
let _today_ = new Date(today.getFullYear(), today.getMonth(), today.getDate());
let BookingCalendar_CurrentDate = _today_;




/************************************************************************************************************************************************************
 monta o titulo do calendario
 BookingCalendar_CurrentDate ==>  inicia sendo= hoje  
 BookingCalendar_CurrentDate sera atualizada a medida que usuario avançar/retroceder semanas
************************************************************************************************************************************************************/

async function refreshBookingDatesAndContent() { 

  // the only way to make the 'bookingsTable' stop overflowing the parent div, put its height manually
  let hgt1 = $('#scheduleToolbar').height()
  let hgt2 = $('#scheduleHeader').height()
  let hgtCONTAINER = $('#scheduleContainer').height()

  $('#bookingsTable').height( hgtCONTAINER - hgt1 - hgt2 - 10)

  

  // necessario abrir evento assincrono para exibir div ajax loading, caso contrario navegador nao atualiza a tela
  //setTimeout(() => {showLoadingGif(); }, 1);

  let currentDate = new Date(BookingCalendar_CurrentDate.getFullYear(), BookingCalendar_CurrentDate.getMonth(), BookingCalendar_CurrentDate.getDate());

  // retrocede ate achar o ultimo domingo antes da data atual (BookingCalendar_CurrentDate)
  while (currentDate.getDay()!=0)  {
    currentDate.setDate(currentDate.getDate() - 1);
  }

  // exibe o dia semana (3 letras)  e a data dd/mm 
  let weekday, weekday_str

  let weekdays =  {0: props.expressions.sunday_short, 1: props.expressions.monday_short, 2: props.expressions.tuesday_short, 3: props.expressions.wednesday_short, 
                    4: props.expressions.thursday_short, 5: props.expressions.friday_short, 6: props.expressions.saturday_short} 

  
  let today = new Date();  // hoje
  let _today_ = new Date(today.getFullYear(), today.getMonth(), today.getDate());

  let options = {month: '2-digit', day: '2-digit'} 

  let displayedYears = []

  let firstDayWeek = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
  let lastDayWeek

  for (weekday=0; weekday<7; weekday++) {

    lastDayWeek = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());

    weekday_str = weekdays[weekday]

    // exibe a data na respectiva <div>
    if ( props.currentCountry == 'usa') 
      $(`#datecolumn${weekday}`).html( weekday_str + ' ' +currentDate.toLocaleDateString('en-us', options ))    // mm/dd
    else 
      $(`#datecolumn${weekday}`).html( weekday_str + ' ' + currentDate.toLocaleDateString( 'pt-br', options ))  // dd/mm

    // usa a propriedade (inventada) 'realDate' para memorizar a data real da coluna
    $(`#datecolumn${weekday}`).attr('real_date', currentDate.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})  )    // yyyy-mm-dd

    // marca o dia de hoje em vermelho
    if (currentDate.getTime() == _today_.getTime()) {
      $(`#datecolumn${weekday}`).css('border-color', 'blue')
      $(`#datecolumn${weekday}`).attr('today', 'true')
    } else {
      $(`#datecolumn${weekday}`).css('border-color', 'transparent')
      $(`#datecolumn${weekday}`).attr('today', 'false')
    }

    // usuario passou/retirou mouse sobre a data no titulo do calendario
    $(`#datecolumn${weekday}`).on('mouseleave', function()   {       
        if ( $(this).attr('today')!='true' )   $(this).css("border-color","transparent")
    });      
    $(`#datecolumn${weekday}`).on('mouseenter', function()   {       
        if ( $(this).attr('today')!='true' )   $(this).css("border-color","black")
        else    $(this).css("border-color","blue")
    });      

    // concatena ano atual para exibir no titulo do calendario de reservas
    if (displayedYears.indexOf(currentDate.getFullYear())==-1)  displayedYears.push(currentDate.getFullYear()  )

    // avanca 1 dia
    currentDate.setDate(currentDate.getDate() + 1);
  }


  // exibe no titulo do calendario , ano  ou anos atuais sendo visualizado(s), se estiver no inicio/final de um ano, aparecerão ambos os anos envolvidos  
  let _displayedYears_ = ''
  let y
  for (y=0; y<displayedYears.length; y++) {
    if (_displayedYears_!='')  _displayedYears_ += ' / '
    _displayedYears_ += displayedYears[y]
  }

  $('#currentYear').html(_displayedYears_)

  // carrega as reservas da semana que esta sendo visualizada

  let __firstDayWeek = firstDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    
  let __lastDayWeek = lastDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    

  // necessario abrir evento assincrono para exibir div ajax loading, caso contrario navegador nao atualiza a tela
  //setTimeout(() => {hideLoadingGif()  }, 300);
  
}





</script>


