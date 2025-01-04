<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import JobForm from './Components/JobForm.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackButton from '@/Components/BackButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps<{
  job: any
  users: any
  economicAgreements: any[]
  categories: any[]
  primaryAgreement: any
  defaultEconomicAgreement: any
  machines: any
  customers: any
  TopDesiredMarginPercentages: any
  weatherConditions: any
}>()

const page = usePage()
const permissions = page.props.auth.permissions

// console.log(props.job);
async function deleteJob() {
  const res = await Swal.fire({
    title: 'Er du sikker på du vil slette?',
    confirmButtonText: 'Ja',
    cancelButtonText: 'Nej',
    showCancelButton: true,
    icon: 'warning',
  })

  if (res.isConfirmed) {
    // jobs.destroy
    router.delete(route('jobs.destroy', props.job.id), {
      preserveScroll: true,
      onSuccess: () => {
        router.get(route('jobs.index'))
      },
    })
  }
}

function hasPermission(checkpermission) {
  return permissions.some((permission) => {
    return permission.name === checkpermission
  })
}

//////////////////////////////////////////////////
const form = useForm({
  economicAgreements: props.defaultEconomicAgreement?.id,
  category_id: props.job?.category_id || props.economicAgreements.find(agreement => agreement.is_primary)?.category_id || null,
  title: props.job?.title || '',
  internal_description: props.job?.internal_description || '',
  start_date: props.job?.start_date || '',
  end_date: props.job?.end_date || '',
  deadline: props.job?.deadline || '',
  customer_id: props.job?.customer_id || '',
  address: props.job?.address || '',
  coordinates: props.job?.lat && props.job?.lng ? { lat: props.job.lat, lng: props.job.lng } : '',
  ref_name: props.job?.ref_name || '',
  ref_number: props.job?.ref_number || '',
  ref_phone: props.job?.ref_phone || '',
  users: props.job?.user?.map((user: { id: any }) => user.id) || [],
  expense_sync: props.job?.expense_sync || false,
  project_number: props.job?.project_number || null,
  machine_id: props.job?.machine_id || null,
  desired_margin_percentage: props.job?.desired_margin_percentage || null,
  weather_condition_ids: props.job?.weatherConditions?.map(condition => condition.id) || [],
})

function submit() {
  form.put(route('jobs.update', props.job.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      router.get(route('jobs.show', props.job.id))
    },
  })
}
</script>

<template>
  <Head title="Rediger job" />

  <AuthenticatedLayout>
    <div class="bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
      <div
        class="flex flex-col space-y-6 p-6 text-gray-900 dark:text-gray-100"
      >
        <div class="flex space-x-2">
          <h1 class="grow text-2xl">
            Opgaver
          </h1>
          <Link v-if="hasPermission('slette job')" @click="deleteJob">
            <SecondaryButton>Slet</SecondaryButton>
          </Link>
        </div>
        <BackButton>
          Tilbage til oversigt
        </BackButton>

        <JobForm v-model="form" :TopDesiredMarginPercentages="TopDesiredMarginPercentages" :customers="customers" :machines="machines" :job="job" :users="users" :economic-agreements="economicAgreements" :categories="categories" :weather-conditions="weatherConditions" @submit="submit" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
