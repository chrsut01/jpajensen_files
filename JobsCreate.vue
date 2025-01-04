<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

import JobForm from './Components/JobForm.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BackButton from '@/Components/BackButton.vue'

const props = defineProps<{
  job: any
  users: any
  economicAgreements: any[]
  categories: any
  defaultEconomicAgreement: any
  machines: any
  customers: any
  TopDesiredMarginPercentages: any
  weatherConditions: any[] 
}>()

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
  coordinates: props.job?.coordinates || '',
  ref_name: props.job?.ref_name || '',
  ref_number: props.job?.ref_number || '',
  ref_phone: props.job?.ref_phone || '',

  users: props.job?.user?.map((user: { id: any }) => user.id) || [],
  expense_sync: props.job?.expense_sync || true,

  project_number: props.job?.project_number || null,
  machine_id: props.job?.machine_id || null,
  desired_margin_percentage: props.job?.desired_margin_percentage || null,
  weather_condition_ids: props.job?.weatherConditions?.map(c => c.id) || [],
})

function submit() {
  form.post(route('jobs.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <Head title="Opret opgave" />

  <AuthenticatedLayout>
    <div class="bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
      <div
        class="flex flex-col space-y-6 p-6 text-gray-900 dark:text-gray-100"
      >
        <BackButton :route="route('jobs.index')">
          Tilbage til oversigt
        </BackButton>

        <JobForm v-model="form" :TopDesiredMarginPercentages="TopDesiredMarginPercentages" :customers="customers" :machines="machines" :categories="categories" :users="users" :economic-agreements="economicAgreements" :weather-conditions="weatherConditions" @submit="submit" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
