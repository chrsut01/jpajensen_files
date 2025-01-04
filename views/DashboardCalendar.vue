<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin, { Draggable } from '@fullcalendar/interaction'
import { router, useForm, usePage } from '@inertiajs/vue3'
import vTooltip from 'primevue/tooltip'
import timeGridPlugin from '@fullcalendar/timegrid'
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import { UserIcon } from '@heroicons/vue/24/outline'
import {
  differenceInMinutes,
  formatDuration,
  intervalToDuration,
} from 'date-fns'

import { da } from 'date-fns/locale'

import {
  Listbox,
  ListboxButton,
  ListboxOption,
  ListboxOptions,
  Tab,
  TabGroup,
  TabList,
  TabPanel,
  TabPanels,
} from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon, ExclamationTriangleIcon } from '@heroicons/vue/20/solid'
import { Tippy } from 'vue-tippy'
import axios from 'axios'
import CalendarJobDetails from './CalendarJobDetails.vue'
import Modal from './Modal.vue'
import SecondaryButton from './SecondaryButton.vue'
import InputLabel from './InputLabel.vue'
import Legend from './Legend.vue'
import { fetchWeather } from './FetchWeather.vue'

const props = defineProps<{
  jobs: any
  users: any
  economicAgreements: any[]
}>()

const isJobModalOpen = ref(false)
const selectedJob = ref(null)

function closeModal() {
  isJobModalOpen.value = false
  selectedJob.value = null
}

function handleResize(eventResizeInfo) {
  const jobID = eventResizeInfo.event.extendedProps.job
  const form = useForm({
    id: jobID,
    end_date: '',
  })

  const end_date = eventResizeInfo.event.end

  form.end_date = new Date(end_date).toISOString()
  form.put(route('jobs.update_end', jobID), {
    onSuccess: () => {
      Swal.fire('Saved!', '', 'success')
      router.visit(route('dashboard'), {
        preserveScroll: true,
        preserveState: true,
      })
    },
  })
}

async function handleEventClick(clickInfo) {
  const loading = Swal.fire({
    toast: true,
    position: 'top-end',
    title: 'Henter data',
    showConfirmButton: false,
    customClass: {
      confirmButton: 'mp-confirm-button',
      cancelButton: 'mp-cancel-button',
      title: 'text-dark',
      container: 'bg-dark',
      htmlContainer: 'mp-text',
      timerProgressBar: 'mp-progress-bar',
    },
  })

  const response = await axios.get(
    route('api.job.show', { job: clickInfo.event.extendedProps.job }),
    { withCredentials: true },
  )
  loading.close()
  isJobModalOpen.value = true
  const job = response.data
  selectedJob.value = job
}

async function handleDrop(info) {
  // get event id
  const jobID = info.draggedEl.dataset.job

  const form = useForm({
    id: jobID,
    start_date: '',
  })
  // get date
  const date = info.dateStr

  const swal = await Swal.fire({
    title: 'Starttidspunkt',

    html: '<input type="time" name="start" id="start" value="08:00" >',
    showCloseButton: true,
    showCancelButton: true,
    focusConfirm: false,
    confirmButtonText: 'Indsæt',

    cancelButtonText: 'Annuller',
  })

  if (swal.isConfirmed) {
    const start = document.getElementById('start').value
    const start_date = `${date} ${start}`

    form.start_date = new Date(start_date).toISOString()
    form.put(route('jobs.update_time', jobID), {
      onSuccess: () => {
        Swal.fire('Gemt!', '', 'success')
        router.visit(route('dashboard'))
      },
    })
  }
  else {
    Swal.fire('Ændringerne blev ikke gemt', '', 'info')
  }
}

async function handleMove(info) {
  const form = useForm({
    id: info.event.extendedProps.job,
    start_date: new Date(info.event.start).toISOString(),
  })

  const swal = await Swal.fire({
    title: 'Er du sikker?',
    text: 'Dette kan ikke gøres om!',

    showCancelButton: true,

    cancelButtonText: 'Annuller',
    confirmButtonText: 'Ja, flyt opgave!',
    customClass: {
      confirmButton: 'mp-confirm-button',
      cancelButton: 'mp-cancel-button',
      title: 'text-dark',
      container: 'mp-container',
      htmlContainer: 'mp-text',
    },
  })

  if (swal.isConfirmed) {
    form.put(route('jobs.update_time', form.id), {
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire({
          toast: true,
          position: 'top-end',
          title: 'Opgaven er flyttet!',
          showConfirmButton: false,
          customClass: {
            confirmButton: 'mp-confirm-button',
            cancelButton: 'mp-cancel-button',
            title: 'text-dark',
            htmlContainer: 'mp-text',
            timerProgressBar: 'mp-progress-bar',
          },
          timer: 2000,
          timerProgressBar: true,
        })
      },
    })
  }
  else {
    // revert
    info.revert()
  }
}
function calculateUrgency(time) {
  if (time === null)
    return
  // date fns
  // calculate time to deadline
  const now = new Date()
  const end = new Date(time)
  const interval = differenceInMinutes(end, now)

  if (interval <= 0)
    return 'overdue'
  else if (interval <= 60 * 24)
    return 'iminent'
  else if (interval <= 60 * 24 * 3)
    return 'soon'
  else if (interval <= 60 * 24 * 7)
    return 'commingWeek'
  else
    return ''
}

const customSnapDuration = ref('00:05:00')

const calendar = ref(null)

const needPlanning = computed(() => {
  return props.jobs?.filter((job) => {
    return job.start_date === null
  })
})

// need planning urgency by date
const needPlanningUrgency = computed(() => {
  // sort by deadline closest to today with nulls last and overdue first
  // and give them a class based on how close they are to today
  const sorted = needPlanning.value.sort((a, b) => {
    if (a.deadline === null)
      return 1
    if (b.deadline === null)
      return -1
    if (a.deadline < b.deadline)
      return -1
    if (a.deadline > b.deadline)
      return 1
    return 0
  })

  const urgency = sorted.map((job) => {
    if (job.deadline === null) {
      return {
        urgency: 'null',
        job,
      }
    }
    else {
      const now = new Date()
      const deadline = new Date(job.deadline)

      const interval = differenceInMinutes(deadline, now)
      if (interval <= 0) {
        return {
          urgency: 'overdue',
          job,
        }
      }
      else if (interval <= 60 * 24) {
        return {
          urgency: 'iminent',
          job,
        }
      }
      else if (interval <= 60 * 24 * 3) {
        return {
          urgency: 'soon',
          job,
        }
      }
      else if (interval <= 60 * 24 * 7) {
        return {
          urgency: 'commingWeek',
          job,
        }
      }
      else {
        return {
          urgency: '',
          job,
        }
      }
    }
  })

  return urgency
})

const events = ref(props.jobs?.map(job => ({

  title: job.title,
  start: job.start_date,
  end: job.end_date,
  editable: job.status !== 'Færdig',
  // color: '#1e1e1f',

  classNames: [
    job.status,
    job.status !== 'Færdig' ? calculateUrgency(job.deadline) : '',
  ],
  extendedProps: {
    job: job.id,
    address: job.address,
    description: job.internal_description,
    colors: job.user.map(user => user.color),
    status: job.status,
    users: job.user.map(user => user),
    customer_name: job.customer?.name,
    economic_agreement_id: job?.economic_agreement_id,
    deadline: job.deadline,
    forecasts: [],
  },
})) || [])

function GetallUserIdFromJobs(userList) {
  if (userList == null)
    return []

  const userIds = []
  userList.forEach((user) => {
    userIds.push(user.id)
  })

  return userIds
}



const selectedPerson = ref()

const selectedEcAgreement = ref()

const selectedDeadline = ref()


// Create a separate ref for filtered events
const filteredEvents = computed(() => {
  let filtered = [...events.value]

  if (selectedPerson.value) {
    filtered = filtered.filter((event) => {
      return event.extendedProps.users.some(user => user.id === selectedPerson.value.id)
    })
  }

  if (selectedEcAgreement.value) {
    filtered = filtered.filter((event) => {
      return event.extendedProps.economic_agreement_id === selectedEcAgreement.value.id
    })
  }

  if (selectedDeadline.value) {
    filtered = filtered.filter((event) => {
      return event.classNames.includes(selectedDeadline.value)
    })
  }

  return filtered
})

const defaultWeatherData = ref([])

// Get weather data for a specific date regardless of job or
function getWeatherForDate(date) {
  if (!defaultWeatherData.value?.length)
    return null

  // Convert input date to ISO string date part for comparison
  const dateStr = date.toISOString().split('T')[0]

  // Only check default weather data
  return defaultWeatherData.value.find(forecast =>
    forecast.date.split('T')[0] === dateStr,
  ) || null
}

async function updateEventsWithWeather(calendarApi) {
  if (!events.value)
    return

  // Default values for base weather display
  const defaultLocation = {
    economicAgreement: 'jpajensen',
    address: 'A R Kjærbysvej 2; 6280 Højer',
    lng: 8.692036,
    lat: 54.961144,
  }

  // Get date ranges
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const maxForecastDate = new Date(today)
  maxForecastDate.setDate(today.getDate() + 8)

  // Get calendar's visible range but limit it to forecast availability
  const viewStart = new Date(Math.max(today, calendarApi.view.activeStart))
  const viewEnd = new Date(Math.min(maxForecastDate, calendarApi.view.activeEnd))

  const updatedEvents = [...events.value]

  try {
    // Fetch default location weather for the entire visible period
    const defaultWeatherResponse = await fetchWeather({
      lat: defaultLocation.lat,
      lng: defaultLocation.lng,
      start_date: viewStart.toISOString().split('T')[0],
      end_date: viewEnd.toISOString().split('T')[0],
    })

    // Create background weather events for each day
    if (defaultWeatherResponse.forecasts) {
      defaultWeatherData.value = defaultWeatherResponse.forecasts.map(forecast => ({
        ...forecast,
        date: forecast.date,
        icon: forecast.icon,
        description: forecast.description,

      }))
    }

    // Process job-specific weather
    for (const [i, event] of updatedEvents.entries()) {
      const job = props.jobs[i]

      if (job) {
        // Skip if job is entirely in the past
        const jobStart = new Date(job.start_date)
        const jobEnd = new Date(job.end_date)

        // Skip if job ends before today or starts after max forecast date
        if (jobEnd < today || jobStart > maxForecastDate)
          continue

        try {
          const weatherStart = new Date(Math.max(today, jobStart))
          const weatherEnd = new Date(Math.min(maxForecastDate, jobEnd))

          const jobWeatherData = await fetchWeather({
            lat: job.lat,
            lng: job.lng,
            start_date: weatherStart.toISOString().split('T')[0],
            end_date: weatherEnd.toISOString().split('T')[0],
            job_id: job.id,
          })

          if (jobWeatherData.forecasts) {
            event.extendedProps = {
              ...event.extendedProps,
              forecasts: jobWeatherData.forecasts.map(forecast => ({
                ...forecast,
                date: forecast.date,
                icon: forecast.icon,
                description: forecast.description,
                alert_reasons: forecast.alert_reasons,

              })),
            }
          }
        }
        catch (error) {
          console.error(`Error fetching weather for job ${job.id}:`, error)
        }
      }
    }
  }
  catch (error) {
    console.error('Error fetching default weather:', error)
  }

  // Update the events
  events.value = updatedEvents
}

console.log(events.value)
const externalEvents = ref(null)

onMounted(async () => {
  if (!props.jobs?.length) {
    console.warn('No jobs available to render in calendar.')
    return
  }
  // Initialize draggable
  if (externalEvents.value) {
    new Draggable(externalEvents.value, {
      itemSelector: '.fc-event',
    })
  }
  // Update weather data after initial render
  await updateEventsWithWeather()
})

// Watch for changes in filtered events and update calendar
watch(filteredEvents, (newEvents) => {
  calendarOptions.value = {
    ...calendarOptions.value,
    events: newEvents,
  }
}, { deep: true })

const defaultAddress = ref('A R Kjærbysvej 2; 6280 Højer')

const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
  customButtons: {
    addJobButton: {
      text: 'Opret opgave',
      click() {
        router.get(route('jobs.create'))
      },
    },
  },

  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'addJobButton,dayGridMonth,timeGridWeek,timeGridDay',
  },
  initialView: 'dayGridMonth',
  weekends: true, // initial value
  eventClick: handleEventClick,
  eventDisplay: 'block',
  aspectRatio: 2,
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  },
  fixedMirrorParent: document.body,
  navLinks: true,
  locale: 'da-DK',
  allDaySlot: false,
  slotMinTime: '00:00:00',
  slotMaxTime: '23:00:00',
  buttonText: {
    today: 'I dag',
    month: 'Måned',
    week: 'Uge',
    day: 'Dag',
    list: 'liste',
  },
  eventColor: '#378006',
  editable: true,
  droppable: true,
  drop: handleDrop,
  // moved
  eventDrop: handleMove,
  eventResize: handleResize,
  snapDuration: customSnapDuration,
  contentHeight: 'auto',
  height: 'auto',
  firstDay: 1,
  events: filteredEvents,
  defaultAddress: defaultAddress.value,
  datesSet: (info) => {
    updateEventsWithWeather(info.view.calendar)
  },
})

// calendarOptions.value.events = filteredEvents

calendarOptions.value = {
  ...calendarOptions.value,
  events: events.value, // Start with base events
}

const ongoingJobs = computed(() => {
  return props.jobs?.filter((job) => {
    return job.status == 'Påbegyndt'
  })
})

function timeTo(deadline) {
  if (deadline === null)
    return
  const now = new Date()
  const end = new Date(deadline)

  const interval = intervalToDuration({ start: now, end })
  const formatted = formatDuration(interval, {
    format: ['days', 'hours', 'minutes'],
    locale: da,
  })

  return formatted
}

function openJobModal(job) {
  selectedJob.value = job
  isJobModalOpen.value = true
}

function formatDate(dateString) {
  if (!dateString)
    return ''
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) {
      console.warn('Invalid date:', dateString)
      return ''
    }
    const day = date.getDate()
    const month = date.getMonth() + 1
    return `${day}/${month}`
  }
  catch (error) {
    console.error('Error formatting date:', error)
    return ''
  }
}
</script>

<template>
  <div class="flex flex-col-reverse  lg:flex-row">
    <div
      class=" overflow-hidden  pb-4 lg:w-1/5"
    >
      <div class="mx-4 h-full border-2 md:mr-2">
        <TabGroup>
          <TabList class=" grid w-full grid-cols-1 overflow-hidden ">
            <Tab v-slot="{ selected }" class="grow">
              <div
                class="flex w-full items-center justify-center truncate  border-b-2 p-2 text-main"
              >
                Nye opgaver
              </div>
            </Tab>
          </TabList>
          <TabPanels class="flex grow flex-col">
            <TabPanel :unmount="false" class="flex flex-col ">
              <div ref="externalEvents" class="relative h-full grow">
                <ul
                  role="list"
                  class="divide-white/2 flex flex-col gap-1 divide-y overflow-y-auto p-2"
                >
                  <li v-if="needPlanningUrgency.length === 0" class="text-center text-xs ">
                    Ingen planlagte opgaver
                  </li>
                  <li
                    v-for="item in needPlanningUrgency"
                    :key="item.job.id"
                    :data-job="item.job.id"
                    class="fc-event relative flex cursor-pointer items-center space-x-4 rounded-sm bg-dark p-1 px-2"
                    :class="item.urgency"
                    @click="openJobModal(item.job)"
                  >
                    <div class="min-w-0 flex-auto">
                      <div class="flex items-center gap-x-0">
                        <h2
                          class="min-w-0  text-sm font-semibold leading-6 text-white"
                        >
                          <div class="flex flex-col">
                            <span class="truncate">{{
                              item.job.title
                            }}</span>
                            <span
                              v-if="
                                item.urgency
                                  && item.urgency
                                    != 'overdue' && item.job?.deadline
                              "
                              class="-mt-2 text-[12px] font-light opacity-80"
                            >{{
                              timeTo(
                                item.job
                                  ?.deadline,
                              )
                            }}</span>
                          </div>
                        </h2>
                      </div>
                      <div
                        class="flex items-center gap-x-2.5 text-xs leading-5 text-background-color"
                      >
                        <p class="truncate">
                          {{ item.job.description }}
                        </p>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </TabPanel>
          </TabPanels>
        </TabGroup>
      </div>
    </div>
    <div class="w-full  px-4  lg:pb-4 lg:pl-2 lg:pr-4">
      <div class="">
        <FullCalendar ref="calendar" :options="calendarOptions" :events="events">
          <!-- New dayCellContent template that uses the unified weather data -->
          <template #dayCellContent="{ date, dayNumberText }">
            <div class="mt-0 grid size-full grid-cols-[0_auto] pt-0">
              <!-- Weather icon on the left -->
              <div v-if="getWeatherForDate(date) && usePage().props.features?.weather?.enabled" class="-ml-9 -mt-1">
                <img
                  v-tooltip="{
                    value: `<div style='font-size: 1.0 rem; line-height: 0.6'>
                    <div style='margin: 0'>${getWeatherForDate(date).description}</div>
                    <div style='margin-top: 2px'>
                      <div style='white-space: nowrap'>${defaultAddress.split(';')[0]}</div>
                      <div style='white-space: nowrap'>${defaultAddress.split(';')[1]}</div>
                    </div>
                  </div>`,
                    escape: false,
                    fitContent: true,
                    class: 'compact-tooltip',
                    position: 'right',
                    autoZIndex: true,
                    showOnDisabled: false,
                  }"
                  :src="`https://openweathermap.org/img/wn/${getWeatherForDate(date).icon}.png`"
                  :alt="getWeatherForDate(date).description"
                  class="size-8"
                >
              </div>
              <!-- Date aligned to the right -->
              <div class="flex justify-end pr-1">
                {{ dayNumberText }}
              </div>
            </div>
          </template>

          <template #eventContent="arg" as="div">
            <div v-if="Object.keys(arg.event.extendedProps.users).length" class="my-1 flex w-36 max-w-full overflow-hidden rounded-form">
              <template v-for="user in arg.event.extendedProps.users" :alt="user.name">
                <Tippy :content="user.name" class="grow">
                  <div class="h-2 grow bg-white" :style="`background-color:${user.color}`" />
                </Tippy>
              </template>
            </div>
            <!-- {{ arg.timeText }} -->
            <div>
              <span><b>{{ arg.event.title }}</b> ({{ arg.event.extendedProps.customer_name }})</span>
            </div>

            <div v-if="arg.event.extendedProps.forecasts?.length && usePage().props.features?.weather?.enabled" class="flex items-center gap-2">
              <div class="group relative inline-flex">
                <!-- Shows the forecast.icon for the first date of the job -->
                <img
                  v-tooltip="{
                    value: `<div style='line-height: 1.5; min-width: 200px; padding-right: 12px'>${arg.event.extendedProps.forecasts[0].description} &nbsp; ${formatDate(arg.event.extendedProps.forecasts[0].date)}\n${arg.event.extendedProps.address.split(';')[0]}</div>`,
                    escape: false,
                    class: 'compact-tooltip',
                    position: 'right',
                    autoZIndex: true,
                    showOnDisabled: false,
                    fitContent: true,
                  }"
                  :src="`https://openweathermap.org/img/wn/${arg.event.extendedProps.forecasts[0].icon}.png`"
                  :alt="arg.event.extendedProps.forecasts[0].description"
                  class="weather-icon size-8"
                >
                <div
                  v-if="arg.event.extendedProps.forecasts?.length > 1"
                  class="fixed z-[9999] hidden w-max rounded bg-white p-2 text-black shadow-lg group-hover:block"
                  :style="{
                    // hover block goes up instead of down
                    transform: 'translateY(-100%)' }"
                >
                  <div
                    v-for="(forecast, index) in arg.event.extendedProps.forecasts.slice(1)"
                    :key="forecast.date"
                    class="mb-2 flex items-center gap-2"
                  >
                    <div class="flex items-center gap-2">
                      <img
                        v-tooltip="forecast.description"
                        :src="`https://openweathermap.org/img/wn/${forecast.icon}.png`"
                        :alt="forecast.description"
                        class="size-8"
                      >
                      <span>
                        {{ formatDate(new Date(forecast.date)) }}
                      </span>
                      <!-- Also show alert icon if there is an alert -->
                      <div
                        v-if="forecast.hasAlert"
                        v-tooltip="forecast.alert_reasons.join(', ')"
                        class="inline-flex gap-1"
                      >
                        <ExclamationTriangleIcon class="text-white-600 size-5" />
                      </div>
                    </div>
                  </div>
                  <!-- <div class="flex flex-col text-sm font-medium text-gray-500" />
                    </div> -->
                </div>
              </div>
              <!-- hover shows description and date -->
              <div
                v-if="arg.event.extendedProps.forecasts.slice(1).some(forecast => forecast.hasAlert)"
                v-tooltip=" `${formatDate(arg.event.extendedProps.forecasts.slice(1).find(forecast => forecast.hasAlert).date)}` + ` ` + `${arg.event.extendedProps.forecasts.find(forecast => forecast.hasAlert).alert_reasons.join(', ')}`"
                class="inline-flex"
              >
                <ExclamationTriangleIcon
                  class="text-white-600 -mb-1 size-4"
                />
              </div>
            </div>
          </template>
        </FullCalendar>

        <div class="my-2 flex flex-col gap-2 md:flex-row md:items-center">
          <InputLabel>Deadline:</InputLabel>
          <Legend v-model="selectedDeadline" />
        </div>
        <div class="mt-4 flex">
          <div class="flex flex-col">
            <InputLabel>Filtrer efter medarbejder:</InputLabel>
            <Listbox v-model="selectedPerson">
              <div class="relative mt-1">
                <ListboxButton
                  class="relative h-10 w-60 border border-gray-300 py-2 pl-3 pr-10 text-left shadow-sm"
                >
                  <span class="block truncate">{{
                    selectedPerson?.name ?? "Alle"
                  }}</span>
                  <span
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                  >
                    <ChevronUpDownIcon
                      class="size-5 text-gray-400"
                      aria-hidden="true"
                    />
                  </span>
                </ListboxButton>

                <transition
                  leave-active-class="transition duration-100 ease-in"
                  leave-from-class="opacity-100"
                  leave-to-class="opacity-0"
                >
                  <ListboxOptions
                    class="absolute z-30 mt-1 max-h-60 w-full overflow-auto bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                  >
                    <ListboxOption
                      v-for="person in users"
                      v-slot="{ active, selected }"
                      :key="person.name"
                      :value="person"
                      as="template"
                    >
                      <li
                        class="relative cursor-default select-none py-2 pl-10 pr-4"
                        :class="[
                          active
                            ? 'bg-highlight/80 text-white'
                            : 'text-gray-900',
                        ]"
                      >
                        <span
                          class="block truncate"
                          :class="[
                            selected
                              ? 'font-medium'
                              : 'font-normal',
                          ]"
                        >{{ person.name }}</span>
                        <span
                          v-if="selected"
                          class="absolute inset-y-0 left-0 flex items-center pl-3 text-highlight"
                        >
                          <CheckIcon
                            class="size-5"
                            aria-hidden="true"
                          />
                        </span>
                      </li>
                    </ListboxOption>
                  </ListboxOptions>
                </transition>
              </div>
            </Listbox>
          </div>

          <SecondaryButton
            v-if="selectedPerson || selectedEcAgreement"
            class="ml-4 mt-auto h-10"
            @click="selectedPerson = null; selectedEcAgreement = null"
          >
            Ryd
          </SecondaryButton>
        </div>
      </div>
    </div>
    <Modal :show="isJobModalOpen" @close="closeModal">
      <CalendarJobDetails :job="selectedJob" />
    </Modal>
  </div>
</template>

<style scoped>
::-webkit-scrollbar {
    @apply w-1;
}
::-webkit-scrollbar-track {
    /* Customize the scrollbar track */
}

::-webkit-scrollbar-thumb {
    @apply rounded-lg bg-dark/20;
}
</style>
