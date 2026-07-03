<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import { uesCategories } from '@/stores/categories_store'
import { CategorisIcon, CloseIcon, ImgIcon, StatusIcon } from '@/stores/icon'
import { onMounted, ref } from 'vue'

const appStore = uesCategories()
const prop = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
})
const formData = ref<{
  name: string
  image: File | null
  status: string
}>({
  name: prop.data?.name || '',
  image: null,
  status: prop.data?.status || '',
})

const previewImage = ref<string | null>(null)
const size = ref<number | null>(null)
const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] || null
  size.value = file ? file.size : null
  formData.value.image = file
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  } else {
    previewImage.value = null
  }
}

onMounted(() => {
  formData.value.name = prop.data?.name || ''
  formData.value.status = prop.data?.status || ''
  appStore.isLoading = false
})

const handleSubmit = async () => {
  appStore.error.name = ''
  appStore.error.status = ''
  if (!formData.value.name || !formData.value.status) {
    appStore.error.name = !formData.value.name ? 'Name is required' : ''
    appStore.error.status = !formData.value.status ? 'Status is required' : ''
    return
  }
  const form = new FormData()
  form.append('name', formData.value.name)
  if (formData.value.image) {
    form.append('image', formData.value.image)
  }
  form.append('status', formData.value.status)
  form.append('_method', 'PUT')

  await appStore.update(form, prop.data?.id)
}

const handleCancel = () => {
  appStore.handleUpdate()
  appStore.resetClear()
}
</script>

<template>
  <div
    class="fixed top-0 left-0 bottom-0 right-0 flex justify-center items-center p-4 bg-black/30 z-50"
  >
    <!--  -->
    <div class="shadow rounded-lg bg-white p-4 text-orange-600 relative">
      <h1 class="font-semibold text-xl mb-4">Create Categories</h1>
      <hr class="py-2" />

      <!-- Button -->
      <div class="absolute top-4 right-4">
        <button
          @click="handleCancel()"
          class="bg-linear-to-r from-orange-600 to-orange-400 text-white p-2 rounded-md cursor-pointer hover:scale-105 duration-500"
        >
          <component :is="CloseIcon" />
        </button>
      </div>
      <!-- End Button -->

      <form @submit.prevent="handleSubmit" class="flex flex-col justify-between w-full">
        <div class="space-y-2">
          <!--  -->
          <div class="w-full">
            <label class="text-orange-500">Name Category <span class="text-red-600">*</span></label>
            <div class="relative mt-1">
              <input
                type="text"
                v-model="formData.name"
                placeholder="example"
                class="w-full pl-14 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                :class="
                  appStore.error.name
                    ? 'border border-red-500 bg-red-100 text-red-500'
                    : 'bg-slate-100'
                "
              />

              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-6" :is="CategorisIcon" />
              </div>
            </div>
            <span class="font-poppins text-xs text-red-500">{{ appStore.error.name }}</span>
          </div>
          <!--  -->

          <!--  -->
          <div class="w-full flex gap-4">
            <div class="w-1/2 h-40 border border-orange-500 rounded-md overflow-hidden">
              <div
                v-if="!previewImage && !data.image"
                class="w-full h-40 flex justify-center items-center p-4 flex-col"
              >
                <h1 class="text-orange-500">No Image</h1>
              </div>
              <img
                v-else
                class="w-full h-40 object-center object-cover"
                :src="previewImage ?? data.image"
                alt=""
              />
            </div>
            <div>
              <label class="text-orange-500">Image <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  type="file"
                  @change="handleImageChange($event)"
                  accept="image/*"
                  class="w-full h-33 pl-14 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                  :class="
                    appStore.error.image
                      ? 'border border-red-500 bg-red-100 text-red-500'
                      : 'bg-slate-100'
                  "
                />
                <div
                  class="absolute z-0 right-0 bottom-0 rounded-r-md px-3 flex justify-center items-center"
                >
                  <div v-if="size" class="text-lg text-orange-500 mt-1">
                    Size: {{ (size / 1024).toFixed(2) }} KB
                  </div>
                </div>

                <div
                  class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
                >
                  <component class="text-white size-6" :is="ImgIcon" />
                </div>
              </div>
              <span class="font-poppins text-xs text-red-500">{{ appStore.error.image }}</span>
            </div>
          </div>
          <!--  -->

          <!--  -->
          <!--  -->
          <div class="w-full">
            <label class="text-orange-500">Status <span class="text-red-600">*</span></label>
            <div class="relative mt-1">
              <select
                v-model="formData.status"
                class="w-full pl-14 p-2.5 text-orange-500 capitalize focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                :class="
                  appStore.error.status
                    ? 'border border-red-500 bg-red-100 text-red-500'
                    : 'bg-slate-100'
                "
              >
                <option value="" disabled>--Please Select --</option>
                <option value="public">public</option>
                <option value="private">private</option>
              </select>

              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-6" :is="StatusIcon" />
              </div>
            </div>
            <span class="font-poppins text-xs text-red-500">{{ appStore.error.status }}</span>
          </div>

          <!--  -->
        </div>

        <!--  -->
        <div class="flex gap-6 mt-6">
          <!-- Button -->
          <button
            type="button"
            class="mt-3 font-open-sans bg-linear-to-r from-red-600 to-red-400 text-white w-full p-2.5 rounded-md cursor-pointer hover:scale-105 duration-500 ease-in-out"
            @click="handleCancel"
          >
            Cancel
          </button>

          <!-- Button -->
          <button
            type="submit"
            class="mt-3 font-open-sans bg-linear-to-r from-orange-600 to-orange-400 text-white w-full p-2.5 rounded-md cursor-pointer hover:scale-105 duration-500 ease-in-out"
          >
            <span v-if="!appStore.isLoading">Save</span>
            <span v-else>Loading...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
