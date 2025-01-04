<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { format } from 'date-fns'
import { da } from 'date-fns/locale'
import {
  ArrowPathIcon,
  CheckCircleIcon,
  ChevronUpIcon,
  DocumentMagnifyingGlassIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  KeyIcon,
  MapPinIcon,
  PencilSquareIcon,
  TrashIcon,
  UserIcon,
} from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2'
import { Table, setIconResolver, type useTable } from 'inertiaui/table'

import { Disclosure, DisclosureButton, DisclosurePanel, Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Map from '@/Components/Map.vue'
import BackButton from '@/Components/BackButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import FileUploader from '@/Components/FileUploader.vue'
import MediaGrid from '@/Components/MediaGrid.vue'
import InputLabel from '@/Components/InputLabel.vue'
import EmployeeAssign from '@/Components/EmployeeAssign.vue'
import NoteToCustomer from '@/Components/NoteToCustomer.vue'
import JobStatus from '@/Components/JobStatus.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Saved from '@/Components/Saved.vue'
import CreateInvoiceFlow from '@/Components/CreateInvoiceFlow.vue'
import InternalDescription from '@/Components/InternalDescription.vue'
import SalaryModal from '@/Components/salaryModal.vue'
import Filterbar from '@/Components/Filterbar.vue'
import FilterButton from '@/Components/FilterButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Filter from '@/Components/Filter.vue'
import BoxContainer from '@/Components/BoxContainer.vue'
import BoxHeadline from '@/Components/BoxHeadline.vue'
import FormContainer from '@/Components/FormContainer.vue'
import CurrencyShow from '@/Components/CurrencyShow.vue'
import ListRow from '@/Components/ListRow.vue'
import ProductCart from '@/Wrappers/ProductCart.vue'
import { fetchWeather } from '@/Components/FetchWeather.vue'

const props = defineProps<{
  job: any
  products: any
  invoice_lines: any
  customer: any
  users: any
  hours: any
  hourTypes: any
  canSeeProductsPrice: boolean
  manhour: any
  machinehour: any
  entries: any
  entries_sum: any
  machine: any
  items: any
  userMostUsedProducts: any
  agreementMostUsedProducts: any
}>()

const page = usePage()
const permissions = page.props.auth.permissions

const userPermissions = computed(() => {
  // return only names of permissions for the user
  return page.props.auth.permissions.map(permission => permission.name)
})

const canConvert = computed(() => {
  return userPermissions.value.includes('kan konvertere montørtimer')
})

// Check user has Edit job permission
const canEdit = computed(() => {
  return userPermissions.value.includes('redigere job')
})

function hasPermission(checkpermission) {
  return permissions.some((permission) => {
    return permission.name === checkpermission
  })
}

const productkey = ref(0)
const computedHours = computed(() => {
  // get duration from hours
  const hours = props.hours

  // calculate duration by adding all hours together with same hour.type
  const types = hours.reduce((acc, hour) => {
    if (acc[hour.type])
      acc[hour.type] += hour.duration / 60
    else
      acc[hour.type] = hour.duration / 60

    return acc
  }, {})

  return types
})

function addComputedHours() {
  const data = computedHours.value

  const form = useForm({
    hourdata: data,
  })

  form.post(route('job.sum', props.job.data.id), {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        title: 'Arbejdstimer tilføjet',
        icon: 'success',
      })
    },
  })
}

const userIds = computed(() => {
  return props.job.data.user.map(user => user.id)
})

const sidebarForm = useForm({
  status: props.job.data.status,
  users: userIds.value || null,
})

function submit() {
  sidebarForm.put(route('jobs.update', props.job.data.id), {
    preserveScroll: true,
    onSuccess: () => {
      isEditingSidebar.value = false
    },
  })
}

onMounted(async () => {
  if (!props.job.data.weather_forecast) {
    const weatherData = await fetchWeather({
      lat: props.job.data.lat,
      lng: props.job.data.lng,
      start_date: props.job.data.start_date,
      end_date: props.job.data.end_date,
      job_id: props.job.data.id,
    })
    if (weatherData) {
      props.job.data.weather_forecast = weatherData.forecasts
    }
  }
})

const isEditingSidebar = ref(false)
function editSidebar() {
  if (sidebarForm.isDirty) {
    Swal.fire({
      title: 'Er du sikker på du vil lukke uden at gemme?',
      confirmButtonText: 'Ja',
      cancelButtonText: 'Nej',
      showCancelButton: true,
      icon: 'warning',
    }).then((res) => {
      if (res.isConfirmed) {
        sidebarForm.reset()
        isEditingSidebar.value = !isEditingSidebar.value
      }
    })
  }
  else {
    isEditingSidebar.value = !isEditingSidebar.value
  }
}

const invoicekey = ref(0)
const lines = ref(props.invoice_lines)

const showSalaryModal = ref(false)

const selectedHour = ref(null)

const key = ref(0)

function editHour(id) {
  // get hour from job.data.hours where id = id
  const hour = props.job.data.hours.find(hour => hour.id === id)
  selectedHour.value = hour
  showSalaryModal.value = true
}

function closeHourModal() {
  showSalaryModal.value = false
  selectedHour.value = null
}

const documentation = computed(() => {
  return props.job.data.media.filter(
    media => media.collection_name === 'documentation',
  )
})

setIconResolver((icon: string, _context: unknown) => ({
  eye: EyeIcon,
  pencil: PencilSquareIcon,
  trash: TrashIcon,
  man: UserIcon,
  machine: KeyIcon,
}[icon]))

function removeFilter(table: ReturnType<typeof useTable>, filter: FilterType) {
  table.state.value.filters[filter.attribute].value = null
  table.removeFilter(filter)
}

function clearFilter(table: ReturnType<typeof useTable>) {
  table.state.value.search = ''
  table.state.value.sort = ''
  page.props.jobhours.filters.forEach((filter: FilterType) => {
    removeFilter(table, filter)
  })
}

function addFilter(state: ReturnType<typeof useTable>['state'], filter: FilterType) {
  state.value.filters[filter.attribute].enabled = true
  state.value.filters[filter.attribute].new = true
}

function format(date: string) {
  if (!date)
    return 'Ingen data'

  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

// Shows date as day/month
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

const totalAmount = computed(() => {
  let total = 0

  props.invoice_lines?.forEach((line) => {
    if (!line.is_static) {
      let price = line.price * line.quantity

      if (line.discount_type === 'percentage')
        price = price - (price * line.discount) / 100
      else
        price = price - line.discount

      total += price
    }
  })

  return total
})

function syncExpenses() {
  const form = useForm({
    job_id: props.job.data.id,
  })

  form.post(route('expenses.sync', props.job.data.id), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload(
        {
          only: ['entries', 'entries_sum'],
        },
      )
    },
  })
}

function handleEditAction(action, keys, onFinish) {
  // keys is an array of the selected row
  editHour(keys[0])

  onFinish()
}

function formatMinutes(totalMinutes: number): string {
  const minutesPerHour = 60
  const hours = Math.floor(totalMinutes / minutesPerHour)
  const minutes = totalMinutes % minutesPerHour

  // Function for Danish pluralization
  const pluralize = (value: number, singular: string, plural: string) =>
    value === 0 ? '' : `${value} ${value > 1 ? plural : singular}`

  // Build the parts
  const parts = [
    pluralize(hours, 'time', 'timer'),
    pluralize(minutes, 'minut', 'minutter'),
  ].filter(Boolean) // Filter out empty strings

  // Join parts with 'og'
  return parts.length > 1 ? `${parts.join(' og ')}` : parts[0] || 'Ingen'
}

/// //// USER STATE OF ACCORDIONS
/// //// MAYBE MAKE A COMPOSABLE FOR THIS
/// //// MUST BE MOVED

const accordionState = ref({
  sagsoplysninger: JSON.parse(localStorage.getItem('accordionState') || '{}').sagsoplysninger ?? true,
  kundeoplysninger: JSON.parse(localStorage.getItem('accordionState') || '{}').kundeoplysninger ?? true,

})

function toggleAccordion(accordion: string) {
  accordionState.value[accordion] = !accordionState.value[accordion]
  // save state to localstorage
  localStorage.setItem('accordionState', JSON.stringify(accordionState.value))
}
</script>

<template>
  <Head title="Detajler" />

  <AuthenticatedLayout>
    <div class="space-y-4">
      <BoxContainer>
        <div class="flex flex-col justify-between gap-4 md:flex-row">
          <BackButton class="mr-auto w-full md:w-fit">
            Tilbage til oversigt
          </BackButton>
          <Link
            v-if="canEdit " :href="route('jobs.edit', job.data.id)"
            class="w-full md:w-fit"
          >
            <SecondaryButton class="w-full md:w-fit">
              Rediger
            </SecondaryButton>
          </Link>

          <CreateInvoiceFlow :key="invoicekey" :job="job.data" @invoice-created="invoicekey++" />
        </div>
      </BoxContainer>

      <main>
        <div class="mx-auto w-full">
          <div class="flex flex-col-reverse gap-4 md:flex-row-reverse">
            <!-- Invoice summary -->
            <div class="sa md:max-w-1/4 flex w-full flex-col gap-4 md:w-1/4">
              <BoxContainer class="relative">
                <PencilSquareIcon
                  v-if="canEdit"
                  class="absolute right-4 top-4 size-5 cursor-pointer text-gray-500"
                  @click="editSidebar"
                />
                <BoxHeadline>
                  Status
                </BoxHeadline>
                <div class="mt-2 border-b border-gray-200 " />
                <div class="grid w-full gap-4">
                  <dl v-if="!isEditingSidebar" class="caseinfo mb-4 divide-y divide-gray-200">
                    <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                      <dt class="font-semibold text-black">
                        Nuværende status
                      </dt>
                      <dd class="text-gray-900 lg:whitespace-nowrap">
                        {{ job.data?.status }}
                      </dd>
                    </div>
                    <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                      <dt class="font-semibold text-black">
                        {{ job.data?.user.length > 1 ? 'Ansvarlige' : 'Ansvarlig' }}
                      </dt>
                      <dd class="text-gray-900 lg:whitespace-nowrap">
                        <p v-for="user in job.data.user" v-if="job.data.user.length">
                          {{ user.name }}
                        </p>
                        <p v-else>
                          Ikke tildelt
                        </p>
                      </dd>
                    </div>
                  </dl>

                  <div v-else class="space-y-4 pt-2">
                    <div>
                      <InputLabel>
                        Status
                      </InputLabel>
                      <JobStatus v-model="sidebarForm.status" />
                    </div>
                    <div>
                      <InputLabel>
                        Ansvarlig
                      </InputLabel>
                      <EmployeeAssign v-model="sidebarForm.users" :users="users" />
                    </div>
                  </div>
                </div>

                <div v-if="sidebarForm.isDirty" class="ml-auto mt-4 flex items-center justify-end">
                  <PrimaryButton
                    :disabled="sidebarForm.processing
                      || !sidebarForm.isDirty
                    " @click="submit"
                  >
                    <span v-if="sidebarForm.isDirty">Gem ændringer</span>
                    <span
                      v-else-if="sidebarForm.recentlySuccessful
                      "
                    >Gemt!</span>
                    <span v-else-if="sidebarForm.processing">Gemmer...</span>
                    <span v-else>Gem</span>
                  </PrimaryButton>

                  <Transition
                    enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0"
                  >
                    <p
                      v-if="sidebarForm.recentlySuccessful
                      " class="text-sm text-gray-600"
                    >
                      <Saved />
                    </p>
                  </Transition>
                </div>
              </BoxContainer>

              <BoxContainer v-if="job.data?.lat && job.data?.lng" class="!p-0">
                <div class="flex w-full ">
                  <Map :job="job.data" />
                </div>
                <a
                  :href="`https://www.google.com/maps/dir/?api=1&origin=&destination=${
                    job.data?.lat
                  },${
                    job.data?.lng}`
                  " target="_blank"
                  class="group"
                >
                  <div class="flex items-center gap-1 p-4">
                    <MapPinIcon class="size-8" />
                    <div class="flex flex-col">
                      <span>
                        {{ job.data?.address
                          ? job.data.address
                          : `${job.data.lat
                          }, ${
                            job.data.lng}` }}
                      </span>

                      <div class="text-xs transition-all duration-300 group-hover:tracking-widest">
                        Find vej
                        <span aria-hidden="true">&rarr;</span>
                      </div>
                    </div>
                  </div>
                </a>
              </BoxContainer>

              <!-- BoxContainer for showing Job's upcoming dates, weather forecast description and icon (or alert icon) -->
              <BoxContainer v-if="job.data.weather_forecast?.length > 0 && usePage().props.features?.weather?.enabled">
                <BoxHeadline>
                  Vejrudsigt
                </BoxHeadline>
                <div class="mb-3 mt-2 border-b border-gray-200" />
                <div class="grid w-full gap-4">
                  <div
                    v-for="forecast in job.data.weather_forecast"
                    :key="forecast.date"
                    class="grid grid-cols-[28px_32px_auto] items-center gap-4"
                  >
                    <img
                      :src="`https://openweathermap.org/img/wn/${forecast.icon}.png`"
                      :alt="forecast.description"
                      class="size-8 object-contain"
                    >

                    <span>{{ formatDate(new Date(forecast.date), "P", { locale: da }) }}</span>

                    <div class="flex items-center gap-7">
                      <span>{{ forecast.description }}</span>
                      <ExclamationTriangleIcon
                        v-if="forecast.hasAlert"
                        v-tooltip="`${forecast.alert_reasons.join(', ')}`"
                        class="-ml-2 size-6 text-amber-600"
                      />
                    </div>
                  </div>
                </div>

              </BoxContainer>

              <BoxContainer v-if="machine">
                <BoxHeadline>
                  Tilknyttet {{ usePage().props.features?.machines?.machine_name_singular }}:
                </BoxHeadline>
                <div class="mt-2 border-b border-gray-200 " />
                <div class="grid w-full gap-4">
                  <dl class="caseinfo divide-y divide-gray-200">
                    <ListRow label="Navn">
                      {{ machine.name }}
                    </ListRow>
                    <ListRow label="Mærke">
                      {{ machine.brand }}
                    </ListRow>
                    <ListRow label="Model">
                      {{ machine.model }}
                    </ListRow>
                    <ListRow label="Serienummer">
                      {{ machine.serial_number }}
                    </ListRow>
                    <ListRow label="Dato">
                      <!-- en-GB to show / instead of - | icky but change later -->
                      {{ new Date(machine.created_at).toLocaleDateString('en-GB') }}
                    </ListRow>
                    <ListRow label="Note" class="flex-col">
                      {{ machine.notes }}
                    </ListRow>
                  </dl>
                </div>
                <div class="my-2 border-b" />
                <div class="flex">
                  <Link :href="route('machines.show', machine.id)" class="ml-auto">
                    Gå til {{ usePage().props.features?.machines?.machine_name_singular }}
                  </Link>
                </div>
              </Boxcontainer>
            </div>

            <!-- Invoice -->
            <div
              class="relative flex w-full flex-col gap-4 md:w-3/4" :class="job.data.status === 'Faktureret' ? 'grayscale' : ''
              "
            >
              <div v-if="job.data.status === 'Faktureret'" class="absolute inset-0">
                <div class="absolute inset-0 z-[31] flex items-center justify-center text-2xl">
                  <span class="rounded-lg bg-dark p-4 text-white">Sagen er afsluttet</span>
                </div>
                <div
                  class="absolute inset-0 z-30 flex items-center justify-center rounded-lg opacity-40"
                  style="
                                        background: rgba(0, 0, 0, 0.8)
                                            url(/assets/images/diagonal-stripes.svg);
                                        background-size: 40%;
                                    "
                />
              </div>

              <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2">
                <BoxContainer class="h-fit">
                  <Disclosure v-slot="{ open }" :default-open="accordionState.sagsoplysninger">
                    <DisclosureButton class="w-full" @click="toggleAccordion('sagsoplysninger')">
                      <div class="flex items-center justify-between">
                        <BoxHeadline>
                          Sagsoplysninger
                        </BoxHeadline>
                        <ChevronUpIcon
                          class="size-5"
                          :class="open ? 'transform rotate-180' : ''"
                        />
                      </div>
                    </DisclosureButton>
                    <transition
                      enter-active-class="transition duration-100 ease-out"
                      enter-from-class="transform scale-95 opacity-0"
                      enter-to-class="transform scale-100 opacity-100"
                      leave-active-class="transition duration-75 ease-out"
                      leave-from-class="transform scale-100 opacity-100"
                      leave-to-class="transform scale-95 opacity-0"
                    >
                      <DisclosurePanel>
                        <div class="mt-2 border-b border-gray-200 " />
                        <div class="grid w-full gap-4">
                          <dl class="caseinfo divide-y divide-gray-200">
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Titel
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                {{ job.data.title }}
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Sagsnr
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                {{ job.data.id }}
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Kategori
                              </dt>
                              <dd class=" text-gray-900">
                                {{ job.data.category?.name }}
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Oprettet
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                {{
                                  format(
                                    new Date(
                                      job.data.created_at,
                                    ),
                                    "P HH:mm",
                                    { locale: da },
                                  )
                                }}
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Start dato
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                <p v-if="job.data.start_date" class="ml-2 inline">
                                  {{
                                    format(
                                      new Date(
                                        job.data.start_date,
                                      ),
                                      "P HH:mm",
                                      {
                                        locale: da,
                                      },
                                    )
                                  }}
                                </p>
                                <p v-else>
                                  -
                                </p>
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Deadline
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                <p v-if="job.data.deadline" class="ml-2 inline">
                                  {{
                                    format(
                                      new Date(
                                        job.data.deadline,
                                      ),
                                      "P HH:mm",
                                      {
                                        locale: da,
                                      },
                                    )
                                  }}
                                </p>
                                <p v-else>
                                  -
                                </p>
                              </dd>
                            </div>
                          </dl>

                          <div
                            v-if="job.data?.ref_name
                              || job.data?.ref_number
                              || job.data?.ref_phone"
                          >
                            <BoxHeadline>
                              Reference
                            </BoxHeadline>
                            <dl

                              class="caseinfo divide-y divide-gray-200"
                            >
                              <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                                <dt class="font-semibold text-black">
                                  Navn
                                </dt>
                                <dd class="text-gray-900 lg:whitespace-nowrap">
                                  <p v-if="job.data?.ref_name">
                                    {{ job.data?.ref_name }}
                                  </p>
                                  <p v-else>
                                    -
                                  </p>
                                </dd>
                              </div>
                              <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                                <dt class="font-semibold text-black">
                                  Telefon
                                </dt>
                                <dd class="text-gray-900 lg:whitespace-nowrap">
                                  <p v-if="job.data?.ref_phone">
                                    {{ job.data?.ref_phone }}
                                  </p>
                                  <p v-else>
                                    -
                                  </p>
                                </dd>
                              </div>
                              <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                                <dt class="font-semibold text-black">
                                  Referencenr.
                                </dt>
                                <dd class="text-gray-900 lg:whitespace-nowrap">
                                  <p v-if="job.data?.ref_number">
                                    {{ job.data?.ref_number }}
                                  </p>
                                  <p v-else>
                                    -
                                  </p>
                                </dd>
                              </div>
                            </dl>
                          </div>
                        </div>
                      </DisclosurePanel>
                    </transition>
                  </Disclosure>
                </BoxContainer>

                <BoxContainer class="h-fit">
                  <Disclosure v-slot="{ open }" :default-open="accordionState.kundeoplysninger">
                    <DisclosureButton class="w-full" @click="toggleAccordion('kundeoplysninger')">
                      <div class="flex items-center justify-between">
                        <BoxHeadline>
                          Kundeoplysninger
                        </BoxHeadline>
                        <ChevronUpIcon
                          class="size-5"
                          :class="open ? 'transform rotate-180' : ''"
                        />
                      </div>
                    </DisclosureButton>
                    <transition
                      enter-active-class="transition duration-100 ease-out"
                      enter-from-class="transform scale-95 opacity-0"
                      enter-to-class="transform scale-100 opacity-100"
                      leave-active-class="transition duration-75 ease-out"
                      leave-from-class="transform scale-100 opacity-100"
                      leave-to-class="transform scale-95 opacity-0"
                    >
                      <DisclosurePanel>
                        <div class="mt-2 border-b border-gray-200 " />
                        <div class="grid w-full gap-4">
                          <dl class="caseinfo divide-y divide-gray-200">
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Navn
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                {{ job.data.customer?.name }}
                                <span class="opacity-50">
                                  ({{
                                    job.data.customer?.customer_number
                                  }})
                                </span>
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                CVR
                              </dt>
                              <dd class="text-gray-900 lg:whitespace-nowrap">
                                <p v-if="job.data.customer?.cvr">
                                  {{
                                    job.data.customer?.cvr
                                  }}
                                </p>
                                <p v-else>
                                  -
                                </p>
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Adresse
                              </dt>
                              <dd class=" text-gray-900">
                                <p
                                  v-if="job.data.lat != null
                                    && job.data.lng != null
                                  "
                                >
                                  {{
                                    job.data?.address
                                      ? job.data.address
                                      : `${job.data.lat
                                      }, ${
                                        job.data.lng}`
                                  }}
                                </p>
                                <p v-else-if="job.data.customer?.address">
                                  {{
                                    job.data.customer?.address
                                  }}
                                </p>
                                <p v-else>
                                  -
                                </p>
                              </dd>
                            </div>
                            <div class="flex flex-col justify-between px-2 py-1 text-sm font-medium xl:flex-row">
                              <dt class="font-semibold text-black">
                                Telefon
                              </dt>
                              <dd class=" text-gray-900">
                                <p v-if="job.data.customer?.phone">
                                  {{
                                    job.data.customer?.phone
                                  }}
                                </p>

                                <p v-else>
                                  -
                                </p>
                              </dd>
                            </div>
                          </dl>
                        </div>
                      </DisclosurePanel>
                    </transition>
                  </Disclosure>
                </BoxContainer>
              </div>

              <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2">
                <BoxContainer>
                  <div class="relative !bg-white">
                    <BoxHeadline>Sagsbeskrivelse</BoxHeadline>
                    <InternalDescription :job="job.data" />
                  </div>
                </BoxContainer>
                <BoxContainer>
                  <div class="relative !bg-white">
                    <BoxHeadline>Besked til kunde</BoxHeadline>
                    <NoteToCustomer :job="job.data" />
                  </div>
                </BoxContainer>
              </div>

              <BoxContainer>
                <div>
                  <TabGroup>
                    <TabList class="mb-4 flex gap-4">
                      <Tab v-slot="{ selected }" class="w-full">
                        <SecondaryButton class="w-full" :class="!selected ? 'bg-opacity-0' : ''">
                          Indtægter
                          <span v-if="selected" class="absolute -right-2 -top-2 rounded-full bg-green-600 p-[2px]"><CheckCircleIcon class="size-4 text-white" /></span>
                        </SecondaryButton>
                      </Tab>
                      <Tab v-if="usePage().props.features?.expenses?.enabled && usePage().props.auth?.permissions_v2?.can_see_expenses" v-slot="{ selected }" class="w-full" :disabled="!job.data.expense_sync">
                        <SecondaryButton class="w-full" :class="!selected ? 'bg-opacity-0' : ''" :disabled="!job.data.expense_sync">
                          Udgifter
                          <span v-if="selected" class="absolute -right-2 -top-2 rounded-full bg-green-600 p-[2px]"><CheckCircleIcon class="size-4 text-white" /></span>
                        </SecondaryButton>
                      </Tab>
                    </TabList>
                    <TabPanels>
                      <TabPanel>
                        <FormContainer>
                          <ProductCart :job="job.data" :products="products" :items="items" :user-most-used-products="userMostUsedProducts" :agreement-most-used-products="agreementMostUsedProducts" />
                        </FormContainer>
                        <!-- <ProductSelecter
                          v-model="props.invoice_lines"
                          class="mt-8"
                          :can-see-products-price="canSeeProductsPrice" :job="job.data" :products="products" :manhour="manhour" :machine-hour-product="machineHourProduct"
                        /> -->
                      </TabPanel>
                      <TabPanel>
                        <Table :resource="entries" class="-mt-4">
                          <template #topbar>
                            <span class="sr-only " />
                          </template>
                          <template #cell(date)="{ value }">
                            {{ format(value) }}
                          </template>
                          <template #cell(media)="{ value }">
                            <div v-if="value">
                              <a :href="value" target="_blank" class="group">

                                <DocumentMagnifyingGlassIcon class="size-5" />
                              </a>
                            </div>
                          </template>
                          <template #cell(amountInBaseCurrency)="{ value }">
                            <CurrencyShow :price="value" />
                          </template>
                        </Table>
                        <div v-if="job.data.expense_sync" class="group ml-auto mt-1 w-fit cursor-pointer" @click="syncExpenses">
                          Synkroniser <ArrowPathIcon class="inline size-4 group-hover:animate-spin" />
                        </div>
                      </TabPanel>
                    </TabPanels>
                  </TabGroup>
                  <FormContainer v-if="job.data.expense_sync && usePage().props.features?.expenses?.enabled && usePage().props.auth?.permissions_v2?.can_see_expenses" class="mt-4">
                    <div class="ml-auto w-fit space-y-2">
                      <!-- Udgifter -->
                      <FormContainer>
                        <div class="ml-auto flex items-center justify-between">
                          <div class="font-normal text-gray-700">
                            Udgifter
                          </div>
                          <div class="pb-0 pl-8 pr-0 text-right tabular-nums text-gray-900">
                            <CurrencyShow :price="entries_sum" />
                          </div>
                        </div>
                        <!-- Indtægter -->
                        <div class="flex items-center justify-between">
                          <div class="font-normal text-gray-700">
                            Indtægter
                          </div>
                          <div class="pb-0 pl-8 pr-0 text-right tabular-nums text-gray-900">
                            <CurrencyShow :price="totalAmount" />
                          </div>
                        </div>
                        <!-- Avance -->
                        <div class="flex items-center justify-between">
                          <div class="text-left font-semibold text-gray-900">
                            Avance
                          </div>
                          <div
                            class="pb-0 pl-8 pr-0 text-right font-semibold tabular-nums text-gray-900"
                            :class="{
                              'text-green-500': (totalAmount - entries_sum) > 0,
                              'text-red-500': (totalAmount - entries_sum) < 0,
                            }"
                          >
                            <span>
                              <CurrencyShow :price="(totalAmount - entries_sum)" />
                            </span>
                          </div>
                        </div>
                        <!-- Avance (%) -->
                        <div class="flex items-center justify-between">
                          <div class="text-left font-semibold text-gray-900">
                            Avance (%)
                          </div>
                          <div
                            class="pb-0 pl-8 pr-0 text-right font-semibold tabular-nums text-gray-900"
                            :class="{
                              'text-green-500': ((totalAmount - entries_sum) / entries_sum * 100).toFixed(2) > 0,
                              'text-red-500': ((totalAmount - entries_sum) / entries_sum * 100).toFixed(2) < 0,
                            }"
                          >
                            <span v-if="entries_sum != 0">
                              {{ ((totalAmount - entries_sum) / entries_sum * 100).toFixed(2) }}%
                            </span>
                            <span v-else class="text-black">-</span>
                          </div>
                        </div>
                      </FormContainer>

                      <!-- Ønsket Avance (%) -->
                      <FormContainer v-if="usePage().props.features?.desired_margin_percentage?.enabled && job.data?.desired_margin_percentage" class="mt-3">
                        <div class="flex items-center justify-between">
                          <div class="text-left font-semibold text-gray-900">
                            Ønsket avance (%)
                          </div>
                          <div
                            class="pb-0 pl-8 pr-0 text-right     tabular-nums text-gray-900"
                          >
                            <span>
                              {{ job.data?.desired_margin_percentage }}%
                            </span>
                          </div>
                        </div>
                        <!-- Manglende indtægter -->
                        <div v-if=" ((entries_sum * (1 + job.data?.desired_margin_percentage / 100)) - totalAmount) > 0" class="flex items-center justify-between">
                          <div class="text-left font-semibold text-gray-900">
                            Manglende indtægter
                          </div>
                          <div
                            class="pb-0 pl-8 pr-0 text-right  tabular-nums text-gray-900"
                          >
                            <span>
                              <CurrencyShow :price="(entries_sum * (1 + job.data?.desired_margin_percentage / 100)) - totalAmount" />
                            </span>
                          </div>
                        </div>
                      </FormContainer>
                    </div>
                  </FormContainer>
                </div>
              </BoxContainer>

              <BoxContainer>
                <div class="mb-2 flex items-center justify-between">
                  <BoxHeadline>
                    Arbejdstimer på denne opgave
                  </BoxHeadline>
                  <SecondaryButton
                    v-if="hasPermission('kan tilføje montørtimer')"
                    @click="showSalaryModal = true"
                  >
                    Tilføj
                    arbejdstimer
                  </SecondaryButton>
                  <SalaryModal
                    :key="selectedHour?.id" :hour-types="hourTypes" :job="job.data.id"
                    :hour="selectedHour" :show="showSalaryModal"
                    :manhour="manhour" :machinehour="machinehour"
                    @close="closeHourModal"
                  />
                </div>
                <Table :resource="page.props.jobhours" @custom-action="handleEditAction">
                  <template #topbar>
                    <span class="sr-only " />
                  </template>
                  <template #filters="{ table }">
                    <Filterbar>
                      <TextInput v-model="table.state.value.search" type="text" placeholder="Søg" class="!border !border-gray-200" />
                      <template v-for="filter in page.props.jobhours?.filters" :key="filter.attribute">
                        <Filter :active="table.state.value.filters[filter.attribute].enabled" @clear="removeFilter(table, filter)">
                          <Dropdown
                            v-model="table.state.value.filters[filter.attribute]"
                            :options="filter.options"
                            option-label="label"
                            data-key="value"
                            :placeholder="filter.label"
                            :reset-filter-on-hide="true"
                            class="w-full"
                            filter
                            empty-filter-message="Ingen resultater fundet"
                            @change="addFilter(table.state, filter)"
                          />
                        </Filter>
                      </template>
                      <FilterButton :disabled="!Object.values(page.props.jobhours.state.filters).some(filter => filter.enabled) && !page.props.jobhours.state.search?.length && !page.props.jobhours.state.sort" @click="clearFilter(table)">
                        Ryd filtre
                      </FilterButton>
                    </Filterbar>
                  </template>
                </Table>
              </BoxContainer>

              <BoxContainer>
                <FormContainer class="grid gap-4 md:grid-cols-2">
                  <div class="relative overflow-hidden rounded-lg bg-white p-4 shadow ">
                    <dt>
                      <div class="absolute rounded-md border border-cyan-300/75 bg-cyan-100 p-3 text-cyan-800">
                        <UserIcon class="size-6 " aria-hidden="true" />
                      </div>
                      <p class="ml-16 truncate text-sm font-medium ">
                        Mandskabstimer
                      </p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                      <p class="text-lg font-semibold text-gray-900">
                        {{ formatMinutes(job.data?.man_hours_duration) }}
                      </p>
                    </dd>
                  </div>
                  <div class="relative overflow-hidden rounded-lg bg-white p-4 shadow ">
                    <dt>
                      <div class="absolute rounded-md border border-slate-300/75 bg-slate-100 p-3   text-slate-800 ">
                        <KeyIcon class="size-6 " aria-hidden="true" />
                      </div>
                      <p class="ml-16 truncate text-sm font-medium ">
                        Maskintimer
                      </p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                      <p class="text-lg font-semibold text-gray-900">
                        {{ formatMinutes(job.data?.machine_hours_duration) }}
                      </p>
                    </dd>
                  </div>
                </FormContainer>
              </BoxContainer>

              <BoxContainer>
                <div class="mb-6">
                  <BoxHeadline>
                    Upload dokumentation
                  </BoxHeadline>
                  <FileUploader
                    v-if="hasPermission('kan tilføje jobdokumenter')"
                    accept="image/*,text/plain,.txt,.doc,.docx,.xml,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.pdf,application/vnd.ms-powerpoint,.csv,application/excel,application/vnd.ms-excel,application/x-excel,application/x-msexcel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    collection="documentation"
                    :upload-route="route('jobs.upload', job.data.id)
                    "
                  />
                </div>

                <MediaGrid
                  v-if="job.data.media.length"
                  :mediafiles="documentation" :remove-route="route('jobs.remove_file', job.data.id)
                  "
                />
              </BoxContainer>
            </div>
          </div>
        </div>
      </main>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.caseinfo > div:nth-child(odd) {
    @apply bg-gray-100/50
}
</style>
