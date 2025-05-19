
<template>

  <div class='flex flex-col h-full ' id='scheduleContainer'>

    <!-- top tool bar -->
    <div class="flex flex-row h-[60px] w-full justify-between border-b-2" id='scheduleToolbar'>

      <!-- current year -->
      <div class="flex flex-row text-[30px] font-bold pt-3 pl-6" id='currentYear'></div>

      <!-- action buttons -->
      <div class="flex flex-row pt-1">
          <!-- new booking -->
          <div  class='btnBOOKING_ADD_CAR_RESERVATION putPrettierTooltip'  :title="expressions.new_booking"   @click="newBookingRecord" aria-hidden="true"></div>   

          <!-- display calendar -->
          <div  class='btnBOOKING_CALENDAR putPrettierTooltip' :title="expressions.choose_date" @click="showCalendar=true" aria-hidden="true"></div>    

          <!-- display all the cars reservations -->
          <div  class='btnBOOKING_ALL_CARS putPrettierTooltip'  :class="{btnBOOKING_ALL_CARS_ACTIVE: displayAllCarsReservations}"
                :title="expressions.display_all_cars" 
              @click="displayAllCarsReservations = ! displayAllCarsReservations;" aria-hidden="true"></div>    

          <!-- back 1 week   -->
          <div  class='btnBOOKING_LEFT_ARROW putPrettierTooltip'  :title="expressions.previous_week" @click="browseBookingCalendar(-7)" aria-hidden="true"></div>   
          <!-- forward 1 week -->
          <div  class='btnBOOKING_RIGHT_ARROW putPrettierTooltip' :title="expressions.next_week" @click="browseBookingCalendar(+7)" aria-hidden="true"></div>   
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

const props = defineProps( ['expressions', 'currentCountry', 'backendUrl'] )

// date picker
const datePicker = ref(null)
const displayAllCarsReservations = ref(false)


//*****************************************************************************
//*****************************************************************************

onMounted( () => {
  refreshBookingDatesAndContent()  // start displaying dates from current week and its reservations
  prepareCalendar()

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

  let __firstDayWeek = firstDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    
  let __lastDayWeek = lastDayWeek.toLocaleDateString("fr-CA", {year:"numeric", month: "2-digit", day:"2-digit"})    

// *********************************************************************************************************************************
  // load the reservations (backend) of the week being viewed
  // *********************************************************************************************************************************
  
  try {
      let _route_ = `${props.backendUrl.value}/bookings/${bookingSelectedCarId}/${__firstDayWeek}/${__lastDayWeek}`
      logAPI('GET', _route_)
      await fetch(_route_, {method: 'GET'})

      .then( (response) => {

        if (!response.ok) {
          throw new Error(`Bookings Prepare Err Fatal= ${response.status}`);
        }
        return response.json();
      })

      .then( (bookings) => {

        // exemplo json retornado
        /*
        [
            {
                "booking_id": 19,
                "car_id": 7,
                "pickup_formatted": "04/12 09:30",
                "pickup_reference": "2024-12-04|09:30",
                "dropoff_formatted": "05/12 15:00",
                "dropoff_reference": "2024-12-05|15:00",
                "driver_name": "teste 1",
                "car_image": "car_000007.png"
            },
            {
                "booking_id": 20,
                "car_id": 7,
                "pickup_formatted": "04/12 08:55",
                "pickup_reference": "2024-12-04|08:55",
                "dropoff_formatted": "06/12 18:30",
                "dropoff_reference": "2024-12-06|18:30",
                "driver_name": "agora vai",
                "car_image": "car_000007.png"
            }
        ]
        */


        // coloca na propriedade (inventada) 'bookings_this_day', contida no header de cada coluna de dia da semana, 
        // as reservas feitas para aquele dia, dessa forma o jscript vai poder mais adiante exibir <div>'s com as reservas naquela coluna

        // as reservas do dia serao concatenadas em um string, cada reserva separada por '^', e cada campo da reserva, separado por '|'
        // e colocadas na propriedade (inventada) 'bookings_this_day'

        let bookingsThisDay 
        let weekday

        // remove informacao usada antes, sobre quais reservas devem ser exibidas em cada coluna de dia da semana
        for (weekday=0; weekday<7; weekday++) {
          jq(`#datecolumn${weekday}`).attr('bookings_this_day','') 
        }

        let availableColors = [ ['rgb(204, 224, 255)', 'rgb(77, 148, 255)'],
                                    ['rgb(214, 245, 214)', 'rgb(0, 128, 0)'],
                                    ['rgb(255, 235, 204)', 'rgb(255, 165, 0)'],
                                    ['rgb(255, 235, 230)', 'rgb(255, 0, 0)'],
                                    ['rgb(242, 230, 217)', 'rgb(172, 115, 57)'] ]
        let whichColor = 0

        for (let bks = 0; bks < bookings.length; bks++)   {
        
            let pickup = bookings[bks]['pickup_reference'].split('|')[0].split('-')   // pega a data yyyy-mm-dd   (pickup_reference= yyyy-mm-dd|HH:mm)
            let dropoff = bookings[bks]['dropoff_reference'].split('|')[0].split('-')

            let _pickup = new Date(pickup[0], parseInt(pickup[1], 10)-1, pickup[2]);
            let _dropoff = new Date(dropoff[0], parseInt(dropoff[1], 10)-1, dropoff[2]); 

            let pickupHour = bookings[bks]['pickup_reference'].split('|')[1]   // pega a hora HH:mm   (pickup_reference= yyyy-mm-dd|HH:mm)
            let dropoffHour = bookings[bks]['dropoff_reference'].split('|')[1]

            // percorre cada dia da semana e verifica se a reserva atual deve ser exibida no dia
            let currentDay = new Date(firstDayWeek.getFullYear(), firstDayWeek.getMonth(), firstDayWeek.getDate());


            for (weekday=0; weekday<7; weekday++) {

              // se a data da coluna atual esta dentro do intervalo da reserva sendo lida, registra (bookings_this_day) que a <div> da reserva deve aparecer aqui
              // o tamanho da <div> vai depender da qtde de horas reservadas no dia atual
              if (currentDay >= _pickup && currentDay <= _dropoff)   {

                bookingsThisDay = jq(`#datecolumn${weekday}`).attr('bookings_this_day') 

                // se o inicio de reserva nao é no dia atual, considera reservado desde o começo do dia (05:00), pois ela comecou em um dia anterior ao atual
                let startingHour = 5, startingMinute = 0                  

                // se o inicio da reserva é o dia atual, obtem a hora do inicio para informar ao jscript onde iniciar a exibicao da <div> com a reserva
                if (currentDay.getTime() == _pickup.getTime())  {
                  startingHour = parseInt( pickupHour.substring(0,2), 10)  // obtem o numero da hora inicial (HH)
                  startingMinute = parseInt( pickupHour.substring(3), 10)  // obtem o minuto inicial (mm)  
                }

                // se o fim de reserva nao é no dia atual, considera reservado ate o fim do dia (23:59), pois ela terminará somente em um dia posterior ao atual
                // usa a hora invalida 24 para avisar o algoritmo que essa reserva nao terminara no dia atual, só em um proximo dia
                let endingHour = 24, endingMinute = 0
                // se o fim da reserva é o dia atual, obtem a hora do fim para informar ao jscript onde finalizar a exibicao da <div> com a reserva
                if (currentDay.getTime() == _dropoff.getTime())  {
                  endingHour = parseInt( dropoffHour.substring(0,2), 10 )  // obtem o numero da hora final  (HH)
                  endingMinute = parseInt( dropoffHour.substring(3), 10 )  // obtem o minuto da hora final  (mm)
                }

                bookingsThisDay += bookingsThisDay=='' ? '' : '^'
                bookingsThisDay +=  bookings[bks]['booking_id'] + '|' + 
                                    bookings[bks]['car_id'] + '|' +  
                                    bookings[bks]['pickup_formatted'] + '|' + 
                                    bookings[bks]['dropoff_formatted'] + '|' + 
                                    bookings[bks]['driver_name'] + '|' +
                                    startingHour + '|' + startingMinute + '|' +
                                    endingHour + '|' + endingMinute + '|' +
                                    bookings[bks]['car_image'] + '|' +
                                    availableColors[whichColor][0] + '|' + availableColors[whichColor][1]    // bgcolor|border color

                // memoriza que na coluna de data atual, esta reserva deve ser exibida
                jq(`#datecolumn${weekday}`).attr('bookings_this_day', bookingsThisDay) 
              }

              // avanca 1 dia
              currentDay.setDate(currentDay.getDate() + 1);
            }

            whichColor = (whichColor < availableColors.length-1) ? (whichColor + 1) : 0   // pega proxima cor didsponivel para <div>
        }

        // exibe as <div>'s de reserva 
        postItBookingDivs()

    })

  } 
  catch(err) {
    throw new Error(`Bookings Prepare Err Fatal= ${err.message}`);
  }




  
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

let months , weekdays, _today_, _close_

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
      $('#lastChosenDateGotFocus').focus();
      refreshBookingDatesAndContent()
    }    

    setTimeout(() => {
      $('#bookingsTable').focus()  
    }, 1000);

  } 
});

datePicker.value = $input.pickadate('picker')
}
 






</script>


