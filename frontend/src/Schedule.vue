
<template>

  <div class='flex flex-col h-full ' id='scheduleContainer'>

    <!-- top tool bar -->
    <div class="flex flex-row h-[60px] w-full justify-between border-b-2" id='scheduleToolbar'>

      <!-- current year -->
      <div class="flex flex-row text-[30px] font-bold pt-3 pl-6" id='currentYear'></div>

      <!-- action buttons -->
      <div class="flex flex-row pt-1">
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
    <div class="w-full flex flex-col  overflow-y-scroll h-[0px]  border-l-0 border-gray-200 border-r-0  " id='bookingsTable' >  

      <div v-for="hour in counter(5, 24)" :key="hour" class="w-full flex flex-row  leading-[60px]  justify-center cursor-pointer border-b-2 border-gray-300 hover:bg-gray-100"  >
        <div class='w-[9%] tdBookingCell flex justify-center'>{{ hourFormat(hour, currentCountry) }}</div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay0{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay1{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay2{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay3{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay4{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay5{{hour}}'></div>
        <div class='w-[13%] tdBookingCell' id='bookingHourDay6{{hour}}'></div>
      </div>

    </div>

    <!-- help to pick date -->
    <div id="divCALENDAR"></div>
    <input type='hidden' id='lastChosenDate' value='' class="datepicker" style='visibility:hidden' /> 
  </div>

  

</template>


<script setup>
import { onMounted, ref , onUpdated  } from 'vue';
import { prepareLoadingAnimation, slidingMessage, counter, hourFormat  } from './js/utils.js'

const props = defineProps( ['expressions', 'currentCountry'] )



// date picker
const datePicker = ref(null)

//*****************************************************************************
//*****************************************************************************

onUpdated( () => {
  refreshBookingDatesAndContent()  // start displaying dates from current week and its reservations
})

//*****************************************************************************
//*****************************************************************************

onMounted( () => {
  refreshBookingDatesAndContent()  // start displaying dates from current week and its reservations
  prepareCalendar()

  $('.btnBOOKING_CALENDAR').on('click', function(event) {
    event.stopPropagation();
    datePicker.value.open();  
  }).on('mousedown', function(event) { event.preventDefault(); });
})


// BookingCalendar_CurrentDate will be used to control current week
let today = new Date(); 
let _today_ = new Date(today.getFullYear(), today.getMonth(), today.getDate());
let BookingCalendar_CurrentDate = _today_;



/************************************************************************************************************************************************************
 BookingCalendar_CurrentDate ==>  starts with today
 BookingCalendar_CurrentDate will be updated as the user backs or forwards weeks
************************************************************************************************************************************************************/

async function refreshBookingDatesAndContent() { 

  // the only way to make the 'bookingsTable' stop overflowing the parent div, was to put its height manually
  // have no more time to make it with css now, but there may be a way with css
  // make this only once
  if ( $('#bookingsTable').height()=='0' ) {
    let hgt1 = $('#scheduleToolbar').height()
    let hgt2 = $('#scheduleHeader').height()
    let hgtCONTAINER = $('#scheduleContainer').height()

    $('#bookingsTable').height( hgtCONTAINER - hgt1 - hgt2 - 10)
  }


  

  // necessario abrir evento assincrono para exibir div ajax loading, caso contrario navegador nao atualiza a tela
  //setTimeout(() => {showLoadingGif(); }, 1);

  let currentDate = new Date(BookingCalendar_CurrentDate.getFullYear(), BookingCalendar_CurrentDate.getMonth(), BookingCalendar_CurrentDate.getDate());

  let options = {month: '2-digit', day: '2-digit'} 

  // get back until finding the last sunday before current date (BookingCalendar_CurrentDate)
  while (currentDate.getDay()!=0)  {
    currentDate.setDate(currentDate.getDate() - 1);
  }

  // obtain weekday (3 letters) and the date itself
  let weekday, weekday_str

  let weekdays =  {0: props.expressions.sunday_short, 1: props.expressions.monday_short, 2: props.expressions.tuesday_short, 3: props.expressions.wednesday_short, 
                    4: props.expressions.thursday_short, 5: props.expressions.friday_short, 6: props.expressions.saturday_short} 

  
  let today = new Date();  
  let _today_ = new Date(today.getFullYear(), today.getMonth(), today.getDate());

  //let options = {month: '2-digit', day: '2-digit'} 

  let displayedYears = []

  let firstDayWeek = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
  let lastDayWeek

  for (weekday=0; weekday<7; weekday++) {

    lastDayWeek = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());

    weekday_str = weekdays[weekday]

    // display the date in its own '<div>'
    if ( props.currentCountry == 'usa') 
      $(`#datecolumn${weekday}`).html( weekday_str + ' ' +currentDate.toLocaleDateString('en-us', options ))    // mm/dd
    else 
      $(`#datecolumn${weekday}`).html( weekday_str + ' ' + currentDate.toLocaleDateString( 'pt-br', options ))  // dd/mm

    // use the 'invented' property  'realDate' to save the real date of the column
    $(`#datecolumn${weekday}`).attr('real_date', currentDate.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})  )    // yyyy-mm-dd

    // puts today in border red
    if (currentDate.getTime() == _today_.getTime()) {
      $(`#datecolumn${weekday}`).css('border-color', 'blue')
      $(`#datecolumn${weekday}`).attr('today', 'true')
    } else {
      $(`#datecolumn${weekday}`).css('border-color', 'transparent')
      $(`#datecolumn${weekday}`).attr('today', 'false')
    }

    // user puts/takes out mouse over the date <div>
    $(`#datecolumn${weekday}`).on('mouseleave', function()   {       
        if ( $(this).attr('today')!='true' )   $(this).css("border-color","transparent")
    });      
    $(`#datecolumn${weekday}`).on('mouseenter', function()   {       
        if ( $(this).attr('today')!='true' )   $(this).css("border-color","black")
        else    $(this).css("border-color","blue")
    });      

    // concatenate curtrent year to display in the title of the schedule
    if (displayedYears.indexOf(currentDate.getFullYear())==-1)  displayedYears.push(currentDate.getFullYear()  )

    // forward 1 day
    currentDate.setDate(currentDate.getDate() + 1);
  }


  // if the week is between 2 years, display both years 
  let _displayedYears_ = ''
  let y
  for (y=0; y<displayedYears.length; y++) {
    if (_displayedYears_!='')  _displayedYears_ += ' / '
    _displayedYears_ += displayedYears[y]
  }

  $('#currentYear').html(_displayedYears_)

  // load the reservations (backend) of the week being viewed

  let __firstDayWeek = firstDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    
  let __lastDayWeek = lastDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    

  // necessario abrir evento assincrono para exibir div ajax loading, caso contrario navegador nao atualiza a tela
  //setTimeout(() => {hideLoadingGif()  }, 300); 
}

/************************************************************************************************************************************************************
back/forward week 
************************************************************************************************************************************************************/
const browseBookingCalendar = (days) => {
  // obtain month/year of the date being handled currently
  let tmpDate = new Date(BookingCalendar_CurrentDate.getFullYear(), BookingCalendar_CurrentDate.getMonth(), BookingCalendar_CurrentDate.getDate());
  tmpDate.setDate(tmpDate.getDate() + days);

  BookingCalendar_CurrentDate = tmpDate

  refreshBookingDatesAndContent()
}


/************************************************************************************************************************************************************
prepare calendar to choose date
************************************************************************************************************************************************************/
function prepareCalendar() {

let months , weekdays, _today, _close_

if (props.currentCountry == 'usa') {
  months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] ,
  weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
  _today_ = 'Today'
  _close_ = 'Close'
} else {
  months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] ,
  weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
  _today_ = 'Hoje'
  _close_ = 'Fechar'
}
var $input = $( '.datepicker' ).pickadate({
  formatSubmit: 'dd/mm/yy',  
  container: '#divCALENDAR', 
  format: 'dd/mm/yyyy',  
  monthsFull: months,
  weekdaysShort: weekdays,
  today: _today_,
  clear: '',  
  close: _close_,
  closeOnSelect: true,  
  onClose: function() {     
    let chosenDate =  datePicker.value.get()   // dd/mm/yyyy 

    let currentDate = new Date(BookingCalendar_CurrentDate.getFullYear(), BookingCalendar_CurrentDate.getMonth(), BookingCalendar_CurrentDate.getDate());
    let _currentDate_ = currentDate.toLocaleDateString( 'pt-br' )

    if (chosenDate != _currentDate_ && chosenDate!='' && _currentDate_!='') {     
      BookingCalendar_CurrentDate = new Date(parseInt(chosenDate.substring(6, 10), 10), 
                      parseInt(chosenDate.substring(3, 5), 10)-1,
                      parseInt(chosenDate.substring(0, 2), 10) );       
      refreshBookingDatesAndContent()
    }    
  } 
});

datePicker.value = $input.pickadate('picker')
}
 






</script>


