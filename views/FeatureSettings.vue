<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import FileUpload from 'primevue/fileupload'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import SettingGroup from '@/Components/SettingGroup.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Container from '@/Components/Container.vue'
import PageHeadline from '@/Components/PageHeadline.vue'
import FormContainer from '@/Components/FormContainer.vue'
import Saved from '@/Components/Saved.vue'
import FormHeadline from '@/Components/FormHeadline.vue'
import InputLabel from '@/Components/InputLabel.vue'

const props = defineProps<{
  settings: any
  icons: any
}>()

const form = useForm({
  machines_enabled: props.settings.machines_enabled,
  machine_name_singular: props.settings.machine_name_singular ?? 'Maskine',
  machine_name_plural: props.settings.machine_name_plural ?? 'Maskiner',
  expenses_enabled: props.settings.expenses_enabled,
  most_used_products_enabled: props.settings.most_used_products_enabled,
  desired_margin_percentage_enabled: props.settings.desired_margin_percentage_enabled,
  weather_enabled: props.settings.weather_enabled,
})
const isEditing = ref(false)

function submit() {
  form.post(route('features.update'), {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value = false
      router.get(route('features.index'))
    },
  })
}

function stopEdit() {
  if (!form.isDirty || confirm('Er du sikker på du vil lukke?')) {
    isEditing.value = false
    form.reset()
  }
}

const iconform = useForm({
  zip: null,
})

function submitIcon() {
  iconform.post(route('upload-icons'), {
    preserveScroll: true,
    onSuccess: () => {
      router.get(route('features.index'))
    },
  })
}

const logoform = useForm({
  logo: null,
})

function submitLogo() {
  logoform.post(route('upload-logo'), {
    preserveScroll: true,
    onSuccess: () => {
      router.get(route('features.index'))
    },
  })
}
</script>

<template>
  <Head title="Indstillinger" />
  <AuthenticatedLayout>
    <Container>
      <div class="space-y-6 p-6 ">
        <div class="flex space-x-2">
          <PageHeadline>
            Features
          </PageHeadline>
          <div>
            <SecondaryButton @click="isEditing ? stopEdit() : isEditing = true">
              {{ isEditing ? 'Luk' : 'Rediger' }}
            </SecondaryButton>
          </div>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
          <FormContainer class="space-y-4">
            <FormHeadline>
              Maskinmodul
            </FormHeadline>
            <p>
              Når denne feature er aktiv, kan du oprette og administrere maskiner i systemet. Maskiner kan tilknyttes opgaver og derefter vises data om maskinernes på de enkelte opgaver.
              Navne på maskinerne kan tilpasses herunder.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
              <FormContainer>
                <SettingGroup
                  v-model="form.machine_name_singular" :editable="isEditing"
                  :error="form.errors.machine_name_singular"
                >
                  Maskinnavn <span class="text-xs">(ental)</span>
                </SettingGroup>
                <span v-if="!isEditing && !form.machine_name_singular">
                  Ikke udfyldt
                </span>
              </FormContainer>
              <FormContainer>
                <SettingGroup
                  v-model="form.machine_name_plural" :editable="isEditing"
                  :error="form.errors.machine_name_plural"
                >
                  Navn i menu
                </SettingGroup>
                <span v-if="!isEditing && !form.machine_name_plural">
                  Ikke udfyldt
                </span>
              </FormContainer>
            </div>
            <div class="border-b " />
            <div class="flex items-center gap-2">
              <input id="machines_enabled" v-model="form.machines_enabled" name="machines_enabled" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
              <InputLabel class="mb-0">
                Denne feature er <span
                  :class="{ 'text-green-500': usePage().props.features?.machines?.enabled, 'text-red-500': !usePage().props.features?.machines?.enabled }"
                >{{ usePage().props.features?.machines?.enabled ? 'aktiv' : 'ikke aktiv' }}</span>
              </InputLabel>
            </div>
          </FormContainer>
          <FormContainer class="flex flex-col space-y-4">
            <FormHeadline>
              Udgiftsmodul
            </FormHeadline>
            <p>
              Når denne feature er aktiv, kan der synkroniseres udgifter fra e-conomic til opgaver.
            </p>
            <div class="block grow border-b" />
            <div class="flex items-center gap-2">
              <input id="expenses_enabled" v-model="form.expenses_enabled" name="expenses_enabled" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
              <InputLabel class="mb-0">
                Denne feature er <span
                  :class="{ 'text-green-500': usePage().props.features?.expenses?.enabled, 'text-red-500': !usePage().props.features?.expenses?.enabled }"
                >{{ usePage().props.features?.expenses?.enabled ? 'aktiv' : 'ikke aktiv' }}</span>
              </InputLabel>
            </div>
          </FormContainer>
          <FormContainer class="flex flex-col space-y-4">
            <FormHeadline>
              Mest brugte/udvalgte produkter
            </FormHeadline>
            <p>
              Når denne feature er aktiv, vises en liste over de mest brugte produkter på opgaver.
            </p>
            <div class="block grow border-b" />
            <div class="flex items-center gap-2">
              <input id="expenses_enabled" v-model="form.most_used_products_enabled" name="expenses_enabled" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
              <InputLabel class="mb-0">
                Denne feature er <span
                  :class="{ 'text-green-500': usePage().props.features?.most_used_products?.enabled, 'text-red-500': !usePage().props.features?.most_used_products?.enabled }"
                >{{ usePage().props.features?.most_used_products?.enabled ? 'aktiv' : 'ikke aktiv' }}</span>
              </InputLabel>
            </div>
          </FormContainer>


          <FormContainer class="flex flex-col space-y-4">
            <FormHeadline>
              Vejrudsigt
            </FormHeadline>
            <p>
              Når denne feature er aktiv, kan du se vejrudsigter til hver dato til hver job.
            </p>
            <div class="block grow border-b" />
            <div class="flex items-center gap-2">
              <input id="expenses_enabled" v-model="form.weather_enabled" name="weather_enabled" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
              <InputLabel class="mb-0">
                Denne feature er <span
                  :class="{ 'text-green-500': usePage().props.features?.weather?.enabled, 'text-red-500': !usePage().props.features?.weather?.enabled }"
                >{{ usePage().props.features?.weather?.enabled ? 'aktiv' : 'ikke aktiv' }}</span>
              </InputLabel>
            </div>
          </FormContainer>


          <FormContainer v-if="form.expenses_enabled" class="flex flex-col space-y-4">
            <FormHeadline>
              Ønsket avance
            </FormHeadline>
            <p>
              Når denne feature er aktiv, kan der angives en ønsket avance på opgaver.<br>
              <span class="underline">Kræver at udgiftsmodul er aktiveret</span>.
            </p>
            <div class="block grow border-b" />
            <div class="flex items-center gap-2">
              <input id="expenses_enabled" v-model="form.desired_margin_percentage_enabled" name="expenses_enabled" type="checkbox" class="focus:main-600 size-4 rounded border-gray-300 text-main">
              <InputLabel class="mb-0">
                Denne feature er <span
                  :class="{ 'text-green-500': usePage().props.features?.desired_margin_percentage?.enabled, 'text-red-500': !usePage().props.features?.desired_margin_percentage?.enabled }"
                >{{ usePage().props.features?.desired_margin_percentage?.enabled ? 'aktiv' : 'ikke aktiv' }}</span>
              </InputLabel>
            </div>
          </FormContainer>
        </div>
        <div class="flex items-center gap-4">
          <PrimaryButton :disabled="form.processing || !form.isDirty" @click.prevent="submit">
            <span v-if="form.isDirty">Gem ændringer</span>
            <span v-else-if="form.recentlySuccessful">Gemt!</span>
            <span v-else-if="form.processing">Gemmer...</span>
            <span v-else>Gem</span>
          </PrimaryButton>

          <Transition
            enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out" leave-to-class="opacity-0"
          >
            <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
              <Saved />
            </p>
          </Transition>
        </div>

        <PageHeadline>
          Sideindstillinger
        </PageHeadline>
        <FormContainer class="flex aspect-square flex-col gap-2 2xl:aspect-auto">
          <FormHeadline class="font-light">
            Upload ikonpakke
          </FormHeadline>
          <p>Sådan laves ikonpakken:</p>
          <ul class="mb-4 list-decimal pl-4">
            <li><a href="https://www.pwabuilder.com/imageGenerator" class="underline">Tryk her</a></li>
            <li>Upload ikon</li>
            <li>Vælg farve</li>
            <li>Sørg for at KUN "iOS" er slået til</li>
            <li>Upload ZIP fil</li>
          </ul>
          <div class="flex justify-between text-sm">
            <FileUpload mode="basic" name="demo[]" accept=".zip" custom-upload choose-label="Vælg ikonpakke" @select="iconform.zip = $event.files[0]" @clear="iconform.zip = null" />

            <PrimaryButton :disabled="!iconform.zip" @click="submitIcon">
              Upload ikoner
            </PrimaryButton>
          </div>
          <InputLabel class="text-sm">
            Uploadede ikoner:
          </InputLabel>
          <div class="grid grid-cols-2 gap-4 md:grid-cols-4 2xl:grid-cols-13">
            <template v-for="icon in icons">
              <FormContainer class="flex aspect-square flex-col gap-2 2xl:aspect-auto">
                <FormHeadline class="font-light">
                  {{ icon.name }}
                </FormHeadline>
                <div class="flex grow items-center justify-center">
                  <img :src="icon.path" class="object-contain">
                </div>
              </FormContainer>
            </template>
          </div>
        </FormContainer>
        <PageHeadline>
          Logo
        </PageHeadline>
        <FormContainer>
          <FormHeadline class="font-light">
            Upload logo
          </FormHeadline>
          <div class="mb-12 flex items-center justify-between">
            <FileUpload mode="basic" custom-upload choose-label="Vælg fil" @select="logoform.logo = $event.files[0]" @clear="logoform.logo = null" />
            <SecondaryButton :disabled="!logoform.logo" @click="submitLogo">
              Upload logo
            </SecondaryButton>
          </div>
          <InputLabel class="text-sm">
            Uploadet logo:
          </InputLabel>
          <img :src="usePage().props.site_logo" class="h-20 object-contain" alt="">
        </FormContainer>
      </div>
    </Container>
  </AuthenticatedLayout>
</template>
