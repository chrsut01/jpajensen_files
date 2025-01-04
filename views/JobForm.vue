<script setup lang="ts" generic="TForm extends object">
import { type InertiaForm, router, usePage } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { da } from 'date-fns/locale'

import { computed, onMounted, ref, watch } from 'vue'
import VueDatePicker from '@vuepic/vue-datepicker'
import MultiSelect from 'primevue/multiselect'
import { CheckCircleIcon, ExclamationTriangleIcon, MapPinIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue'
import EconomicAgreementSelect from '@/Components/EconomicAgreementSelect.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextInput from '@/Components/TextInput.vue'
import TextArea from '@/Components/TextArea.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import EmployeeAssign from '@/Components/EmployeeAssign.vue'
import Address from '@/Components/Address.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Saved from '@/Components/Saved.vue'
import CoordinatePlace from '@/Components/CoordinatePlace.vue'
import FormContainer from '@/Components/FormContainer.vue'
import Modal from '@/Components/Modal.vue'
import CustomerSelect from '@/Components/CustomerSelect.vue'
import CreateCustomerForm from '@/Components/CreateCustomerForm.vue'
import { fetchWeather } from '@/Components/FetchWeather.vue'

const props = defineProps<{
  job?: any
  users: any
  economicAgreements: any[]
  categories: any[]
  machines: any[]
  customers: any[]
  TopDesiredMarginPercentages: any[]
  weatherConditions?: any
  modelValue: any
}>()

const emit = defineEmits<{
  'submit': [value: TForm, event: Event]
  'update:modelValue': [value: any]
}>()

const availableWeatherConditions = computed(() => {
  return props.weatherConditions || []
})

const model = defineModel<InertiaForm<TForm>>({ required: true })

const address = ref(null)

const coordinates = ref({
  lat: null,
  lng: null,
})

const isLoadingWeather = ref(false)

const isPlacingCoordinates = ref(false)

const mapUpdateKey = ref(null)

const startTime = ref({ hours: 8, minutes: 0 })

if (!model.value.weather_condition_ids) {
  model.value.weather_condition_ids = []
}

const weatherForecasts = ref([])

if (model.value.coordinates)
  coordinates.value = model.value.coordinates

async function fetchWeatherData(startDate, endDate) {
  if (!coordinates.value.lat || !coordinates.value.lng)
    return
  isLoadingWeather.value = true
  try {
    const weatherData = await fetchWeather({
      lat: coordinates.value.lat,
      lng: coordinates.value.lng,
      start_date: startDate,
      end_date: endDate,
      job_id: props.job?.data?.id || null,
    })

    if (weatherData?.forecasts) {
      weatherForecasts.value = weatherData.forecasts

      // If we have a job, update the job data
      if (props?.job.data) {
        props.job.data.weather_forecast = weatherData.forecasts
      }
    }
  }
  catch (error) {
    console.error('Error fetching weather:', error)
  }
  finally {
    isLoadingWeather.value = false
  }
}

onMounted(async () => {
  if (props.job?.lat && props.job?.lng) {
    coordinates.value = {
      lat: props.job.lat,
      lng: props.job.lng,
    }
    // If we have coordinates and dates, fetch weather
    if (props.job.start_date && props.job.end_date) {
      try {
        const weatherData = await fetchWeather({
          lat: coordinates.value.lat,
          lng: coordinates.value.lng,
          start_date: props.job.start_date,
          end_date: props.job.end_date,
          job_id: props.job?.id || null,
        })

        if (weatherData?.forecasts) {
          weatherForecasts.value = weatherData.forecasts
        }
      }
      catch (error) {
        console.error('Error fetching weather:', error)
        weatherForecasts.value = []
      }
    }
  }
})

// watch model.economicAgreements
watch(() => model.value.economicAgreements, (value) => {
  // filter where id is in economicAgreements id

  // console.log
  const filteredAgreement = props.economicAgreements.find(({ id }) => value.includes(id))
  if (filteredAgreement && filteredAgreement.category_id)
    model.value.category_id = filteredAgreement.category_id
})

watch(address, async (newAddress) => {
  if (newAddress) {
    model.value.address = newAddress?.tekst
    coordinates.value.lat = newAddress.data.y
    coordinates.value.lng = newAddress.data.x
    model.value.coordinates = coordinates.value
    mapUpdateKey.value = Math.random()

    // If we don't have dates set but have an address, use temporary dates for weather fetch
    if (!model.value.start_date && !model.value.end_date) {
      const tempStartDate = new Date()
      const tempEndDate = new Date(Date.now() + 8 * 24 * 60 * 60 * 1000) // 8 days from now
      await fetchWeatherData(tempStartDate, tempEndDate)
    }
    else {
      // Use the dates from the form if they exist
      await fetchWeatherData(model.value.start_date, model.value.end_date)
    }
  }
})

// watch(() => coordinates.value, (newCoordinates) => {
//   model.value.coordinates = newCoordinates
// })

// Watch for changes in coordinates OR model dates
watch([
  coordinates,
  () => model.value.start_date,
  () => model.value.end_date,
], async ([newCoords]) => {
  // Clear existing forecasts if we don't have coordinates
  if (!newCoords.lat || !newCoords.lng) {
    weatherForecasts.value = []
    return
  }

  if (newCoords.lat
    && newCoords.lng
    && model.value.start_date
    && model.value.end_date) {
    await fetchWeatherData(model.value.start_date, model.value.end_date)
  }
}, { deep: true }) // Add deep: true to detect nested changes in coordinates

watch(() => model.value.customer_id, (newCustomerId) => {
  model.value.machine_id = null
})

async function customerAddress(customer: any) {
  if (customer == undefined || customer == null) {
    model.value.customer_id = ''
    model.value.address = ''
    coordinates.value.lat = null
    coordinates.value.lng = null
    return
  }

  model.value.customer_id = customer.id

  if (customer.address && customer.zip) {
    const response = await fetch(
      `https://api.dataforsyningen.dk/autocomplete?q=${
        customer.address
      }&postnr=${
        customer.zip}`,
      {
        method: 'get',
      },
    )
      .then(async response => await response.json())
      .then((result) => {
        address.value = result[0]
      })
  }
  else {
    address.value = null
    coordinates.value.lat = null
    coordinates.value.lng = null
    model.value.address = ''
  }
}

function handleSubmit(event: Event) {
  emit('submit', model.value.data(), event)
}

const customerMachines = computed(() => {
  if (!props.machines)
    return []
  return props.machines.filter(machine => machine.customer_id === model.value.customer_id)
})

const showProjectInfo = ref(false)

const filteredCustomers = computed(() => {
  // customer.economic_agreement_id === model.value.economicAgreements

  return props.customers?.data?.filter(customer => customer.economic_agreement_id == model.value.economicAgreements)
})

const selectedCustomerModel = ref(model.value.customer_id)

const sortedTopDesiredMarginPercentages = computed(() => {
  return props.TopDesiredMarginPercentages.sort((a, b) => a - b)
})

async function customerCreated() {
  await router.reload({
    only: ['customers'],
  })
}

watch(() => props.customers.data, () => {
  const customer = props.customers.data.filter(customer => customer.customer_number == usePage().props.flash?.newly_created)
  if (customer.length > 0) {
    selectedCustomerModel.value = customer[0]
    customerAddress(customer[0])
  }
})

function formatDate(date) {
  if (!date)
    return ''
  try {
    const dateObj = new Date(date)
    if (isNaN(dateObj.getTime())) {
      console.warn('Invalid date:', date)
      return ''
    }
    return format(dateObj, 'd/M', { locale: da })
  }
  catch (error) {
    console.error('Error formatting date:', error)
    return ''
  }
}

const newCustomerModal = ref(false)
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="mt-6 space-y-6">
      <div class="grid gap-4 divide-x xl:grid-cols-4">
        <div class="space-y-4 xl:col-span-3">
          <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <FormContainer>
              <InputLabel for="category" value="Kategori" required />
              <select
                id="category" v-model="model.category_id" required name="category"
                class=" block w-full rounded-md border !border-gray-300  text-sm text-gray-900 ring-0 ring-inset focus:ring-0  "
              >
                <option v-for="category in categories" :key="category" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <InputError class="mt-2" :message="model.errors.category_id" />
            </FormContainer>
            <FormContainer class="relative">
              <InputLabel value="Søg efter kunde" required />

              <CustomerSelect v-model="selectedCustomerModel" :customers="filteredCustomers" @update:model-value="customerAddress" />
              <div class="absolute right-4 top-4 flex items-center gap-1 text-xs" @click="newCustomerModal = true">
                <span>Ny kunde?</span>
              </div>

              <CreateCustomerForm :selected-agreement="model.economicAgreements" :open-modal="newCustomerModal" @close="newCustomerModal = false" @customer-created="customerCreated" />

              <InputError class="mt-2" :message="model.errors.customer_id" />
            </FormContainer>
          </div>
          <FormContainer class="space-y-4">
            <div>
              <InputLabel for="title" value="Titel" required />
              <TextInput
                id="title" v-model="model.title" type="text" class="mt-1 block w-full" placeholder="Opgavens titel" required autofocus
                autocomplete="title"
              />
              <InputError class="mt-2" :message="model.errors.title" />
            </div>
            <div>
              <InputLabel for="description" value="Beskrivelse" />

              <TextArea id="description" v-model="model.internal_description" class="mt-1 block w-full" placeholder="Her kan du skrive en beskrivelse af opgaven" />
              <InputError class="mt-2" :message="model.errors.internal_description" />
            </div>
          </FormContainer>
          <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <FormContainer>
              <InputLabel for="start_date" value="Start dato" />

              <VueDatePicker
                v-model="model.start_date" :minutes-increment="15" time-picker-inline :start-time="startTime" locale="da" week-num-name="U"
                select-text="Vælg" cancel-text="Annuller" :required="false" :month-change-on-scroll="false" format="dd/MM/yyyy HH:mm"
              />

              <InputError class="mt-2" :message="model.errors.start_date" />
            </FormContainer>
            <FormContainer>
              <InputLabel for="end_date" value="Slut dato" />

              <VueDatePicker
                v-model="model.end_date" :minutes-increment="15" time-picker-inline :start-time="startTime" locale="da" week-num-name="U"
                select-text="Vælg" cancel-text="Annuller" :required="false" :month-change-on-scroll="false" format="dd/MM/yyyy HH:mm" :min-date="model.start_date"
              />

              <InputError class="mt-2" :message="model.errors.end_date" />
            </FormContainer>
            <FormContainer>
              <InputLabel for="deadline" value="Deadline" />

              <VueDatePicker
                v-model="model.deadline" locale="da" :minutes-increment="15" time-picker-inline week-num-name="U"
                :min-date="model.start_date" select-text="Vælg" cancel-text="Annuller" :required="false" :month-change-on-scroll="false" format="dd/MM/yyyy HH:mm"
              />

              <InputError class="mt-2" :message="model.errors.deadline" />
            </FormContainer>
            <FormContainer class="space-y-4">
              <div>
                <InputLabel>Søg efter adresse</InputLabel>
                <Address v-model="address" class="w-full" />
              </div>
              <div>
                <InputLabel class="mt-2">
                  Valgt adresse
                </InputLabel>
                <TextInput
                  id="address" v-model="model.address" type="text" class="mt-1 block w-full bg-gray-200"
                  readonly
                />
                <InputError class="mt-2" :message="model.errors.address" />
              </div>
              <SecondaryButton :disabled="isPlacingCoordinates" class="mt-2" @click="isPlacingCoordinates = true">
                <MapPinIcon class="mr-2 size-5" />Placer koordinater
              </SecondaryButton>
            </FormContainer>
            <FormContainer class="overflow-hidden !p-0">
              <CoordinatePlace v-model="coordinates" :update-key="mapUpdateKey" :is-placing-coordinates="isPlacingCoordinates" @change="model.address = null, isPlacingCoordinates = false" />
            </FormContainer>

            <!-- Shows weather forecasts and alert icons   -->
            <div v-if="usePage().props.features?.weather?.enabled">
              <FormContainer>
                <InputLabel for="weather-conditions" value="Vejrudsigt" class="mb-4" />
                <div class="mt-2 space-y-2">
                  <div v-if="isLoadingWeather" class="text-gray-500">
                    Henter vejrudsigter...
                  </div>
                  <div v-else-if="!weatherForecasts.length" class="text-gray-400">
                    Indtast adresse for at se vejrudsigten
                  </div>
                  <div
                    v-for="forecast in weatherForecasts"
                    :key="forecast.date"
                    class="grid grid-cols-[28px_32px_auto] items-center gap-4"
                  >
                    <img
                      :src="`https://openweathermap.org/img/wn/${forecast.icon}.png`"
                      :alt="forecast.description"
                      class="size-8"
                    >
                    <span>{{ formatDate(new Date(forecast.date)) }}</span>

                    <div class="flex items-center gap-7">
                      <span>{{ forecast.description }}</span>

                      <ExclamationTriangleIcon
                        v-if="forecast.hasAlert"
                        v-tooltip="`${forecast.alert_reasons.join(', ')}`"
                        class="size-6 text-amber-600"
                      />
                    </div>
                  </div>
                </div>
              </FormContainer>
            </div>
          </div>
        </div>
        <div class="col-span-1 flex flex-col gap-4 p-0 md:pl-4	">
          <FormContainer class="space-y-4">
            <div>
              <InputLabel value="Aftale" />
              <EconomicAgreementSelect v-model="model.economicAgreements" :economic-agreements="props.economicAgreements" :is-editing="true" />
              <InputError class="mt-2" :message="model.errors.economicAgreements" />
            </div>
          </FormContainer>
          <FormContainer class="space-y-4">
            <div>
              <InputLabel value="Reference" />
              <TextInput v-model="model.ref_name" class="w-full" placeholder="Referenceperson" />
              <InputError class="mt-2" :message="model.errors.ref_name" />
            </div>

            <div>
              <TextInput v-model="model.ref_number" class="w-full" placeholder="Referencenummer" />
              <InputError class="mt-2" :message="model.errors.ref_number" />
            </div>

            <div>
              <TextInput v-model="model.ref_phone" class="w-full" placeholder="Reference telefon" />
              <InputError class="mt-2" :message="model.errors.ref_phone" />
            </div>
          </FormContainer>

          <FormContainer v-if="usePage().props.features?.machines?.enabled">
            <InputLabel for="machine" value="Maskine" />

            <select
              v-if="customerMachines.length > 0"
              id="machine" v-model="model.machine_id" name="machine"
              class=" block w-full rounded-md border !border-gray-300  text-sm text-gray-900 ring-0 ring-inset focus:ring-0  "
            >
              <option v-for="machine in customerMachines" :key="machine.id" :value="machine.id">
                {{ machine.name }}
              </option>
            </select>
            <span v-else>
              <p class="text-gray-500">Ingen <span class="lowercase">{{ usePage().props.features?.machines?.machine_name_plural }}</span> tilknyttet kunden</p>
            </span>
            <InputError class="mt-2" :message="model.errors.machine_id" />
          </FormContainer>
          <FormContainer v-if="usePage().props.features?.expenses?.enabled" class="space-y-4">
            <div class="border-b pb-4">
              <InputLabel value="Projektnr." />
              <TextInput v-model="model.project_number" class="w-full" placeholder="Projektnr i e-conomic" />
              <InputError class="mt-2" :message="model.errors.project_number" />
              <p class="mt-2 text-xs text-gray-500">
                Læs mere om <a href="https://www.e-conomic.dk/apps-og-udvidelser/e-conomic-projektstyring" target="_blank" class="text-main">projektstyring</a>
              </p>
            </div>
            <div>
              <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                  <input id="expense_sync" v-model="model.expense_sync" aria-describedby="expense_sync-description" name="expense_sync" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
                </div>
                <div class="ml-3 text-sm leading-6">
                  <InputLabel for="expense_sync" class="font-medium text-gray-900">
                    Synkroniser udgifter
                  </InputLabel>
                  <p id="expense_sync-description" class="text-gray-500">
                    Viser udgifter fra e-conomic på opgaven.
                  </p>
                  <p v-if="usePage().props.features?.desired_margin_percentage?.enabled" id="expense_sync-description" class="text-gray-500">
                    <span class="text-main">Ønsket avance</span> kan beregnes ud fra udgifter når denne er  <span class="text-main">slået til</span>.
                  </p>
                  <p class="mt-2 cursor-pointer text-xs text-main underline" @click="showProjectInfo = true">
                    Se muligheder for udgiftssynkronisering
                  </p>
                </div>
              </div>
            </div>
          </FormContainer>
          <FormContainer v-if="usePage().props.features?.desired_margin_percentage?.enabled && usePage().props.features?.expenses?.enabled && model.expense_sync" class="space-y-4">
            <div class="">
              <InputLabel value="Ønsket avance  (%)" />
              <TextInput v-model="model.desired_margin_percentage" class="w-full" placeholder="Ønsket avance i procent" />
              <InputError class="mt-2" :message="model.errors.desired_margin_percentage" />
            </div>
            <div class="flex flex-wrap gap-2">
              <div v-for="percentage in sortedTopDesiredMarginPercentages" :key="percentage" class="flex items-center gap-2">
                <SecondaryButton
                  class="!p-1 !px-3" :class="[
                    model.desired_margin_percentage == percentage ? 'bg-main text-white' : 'bg-gray-200 text-gray-900',
                  ]"
                  @click="model.desired_margin_percentage = percentage"
                >
                  {{
                    new Intl.NumberFormat("da", {
                      trailingZeroDisplay: "stripIfInteger",
                    }).format(percentage)
                  }}%
                </SecondaryButton>
              </div>
            </div>
            <div class="flex justify-end">
              <p class=" text-xs text-gray-500">
                Intelligente avanceforslag baseret på tidligere opgaver
              </p>
            </div>
          </FormContainer>
          <Modal :show="showProjectInfo" @close="showProjectInfo = false">
            <div class=" p-4 text-sm">
              <h2 class="text-lg font-semibold">
                Sådan kommer du i gang <span class="text-xs font-medium">Udgiftssynkronisering</span>
              </h2>
              <FormContainer>
                <TabGroup>
                  <TabList class="mb-4 flex gap-4">
                    <Tab v-slot="{ selected }" class="w-full">
                      <SecondaryButton class="w-full" :class="!selected ? 'bg-opacity-0' : ''">
                        Projektstyring
                        <span v-if="selected" class="absolute -right-2 -top-2 rounded-full bg-green-600 p-[2px]"><CheckCircleIcon class="size-4 text-white" /></span>
                      </SecondaryButton>
                    </Tab>
                    <Tab v-slot="{ selected }" class="w-full">
                      <SecondaryButton class="w-full" :class="!selected ? 'bg-opacity-0' : ''">
                        Bilagsmatch
                        <span v-if="selected" class="absolute -right-2 -top-2 rounded-full bg-green-600 p-[2px]"><CheckCircleIcon class="size-4 text-white" /></span>
                      </SecondaryButton>
                    </Tab>
                  </TabList>
                  <TabPanels>
                    <TabPanel class="space-y-4">
                      <p>
                        Krav til projektstyring:
                      </p>
                      <ul class="ml-4 list-disc	text-sm">
                        <li>Projektstyring skal være tilkøbt i <a href="https://www.e-conomic.dk/apps-og-udvidelser/e-conomic-projektstyring" target="_blank" class="cursor-pointer underline">e-conomic</a></li>
                        <li>Projektnummer skal være udfyldt i "Projektnr." feltet</li>
                        <li>Projektnummer skal være et gyldigt projekt i e-conomic</li>
                        <li>Bilag skal være bogført i e-conomic</li>
                        <li>"Synkroniser udgifter" skal være slået til</li>
                      </ul>
                      <p>
                        Når projektstyring er aktiveret, vil udgifter fra e-conomic blive vist på opgaven. Vi henter alle "Leverandørfakturaer" fra e-conomic og viser dem på opgaven, hvorefter de kan indgå i opgavens økonomi.
                        Herefter vil avance kunne ses på opgaven.
                      </p>
                      <p class="text-sm">
                        Bemærk: Der kan kun være ét projekt pr. opgave og alle udgifter fra e-conomic vil blive vist på opgaven.
                      </p>
                    </TabPanel>
                    <TabPanel class="space-y-4">
                      <p>
                        Krav til bilagsmatch:
                      </p>
                      <ul class="ml-4 list-disc	text-sm">
                        <li>Bilag skal indeholde "Opgavenr -" i starten af teksten <span class="text-xs italic">eks. (456 - Køb af kontorartikler)</span></li>
                        <li>Type af bilag skal være "Leverandørfaktura"</li>
                        <li>Bilag skal være bogført i e-conomic</li>
                        <li>"Synkroniser udgifter" skal være slået til</li>
                      </ul>
                      <p>
                        Alle bilag fra e-conomic der matcher opgavenr. vil blive vist på opgaven, hvorefter de kan indgå i opgavens økonomi. Herefter vil avance kunne ses på opgaven.
                        Det er vigigt at bilagene indeholder "Opgavenr -" i starten af teksten, så vi kan matche bilagene med opgaven.
                      </p>
                      <p class="text-sm">
                        Bemærk: Bilag kan kun matche ét opgavenr.
                      </p>
                    </TabPanel>
                  </TabPanels>
                </TabGroup>
              </FormContainer>
            </div>
          </Modal>
          <FormContainer>
            <div>
              <InputLabel :value="model.users?.length > 1 ? 'Ansvarlige' : 'Ansvarlig'" />
              <EmployeeAssign v-model="model.users" :users="users" />
              <InputError class="mt-2" :message="model.errors.users" />
            </div>
          </FormContainer>

          <div v-if="usePage().props.features?.weather?.enabled">
            <FormContainer>
              <InputLabel for="weather-conditions" value="Alarm ved vejr" />
              <MultiSelect
                v-model="model.weather_condition_ids"
                :options="availableWeatherConditions"
                option-label="alert_condition"
                selected-items-label="{0} vejralarm valgt"
                option-value="id"
                placeholder="Alarmer mig hvis vejr bliver…"
                empty-message="Ingen vejralarm fundet"
                class="w-full rounded-lg border border-slate-300  text-sm focus:border-transparent 
                focus:outline-none focus:ring-2 focus:ring-slate-300 disabled:cursor-not-allowed 
                disabled:opacity-50"
              />
            </FormContainer>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton :disabled="model.processing || !model.isDirty">
          <span v-if="model.isDirty">Gem ændringer</span>
          <span v-else-if="model.recentlySuccessful">Gemt!</span>
          <span v-else-if="model.processing">Gemmer...</span>
          <span v-else>Gem</span>
        </PrimaryButton>

        <Transition
          enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
          leave-active-class="transition ease-in-out" leave-to-class="opacity-0"
        >
          <p v-if="model.recentlySuccessful" class="text-sm text-gray-600">
            <Saved />
          </p>
        </Transition>
      </div>
    </div>
  </form>
</template>
