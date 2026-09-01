<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import CategoryForm from '@/components/categories/CategoryForm.vue'
import IconView from '@/components/icons/IconView.vue'
import { uesCategories } from '@/stores/categories_store'
import {
  CategorisIcon,
  DeleteIcon,
  EditIcon,
  ImgIcon,
  LoadingIcon,
  StatusIcon,
} from '@/stores/icon'
import { onMounted, ref } from 'vue'
const appStore = uesCategories()

const validatename = (name: string) => {
  if (!name || name == '') {
    appStore.error.name = 'Name is required'
    return appStore.error.name
  }

  appStore.error.name = ''
  return appStore.error.name
}

const validateFile = (image: File | null | undefined) => {
  if (!image) {
    appStore.error.image = 'Image is required'
    return appStore.error.image
  }

  appStore.error.image = ''
  return appStore.error.image
}

const validateStatus = (status: string) => {
  if (!status) {
    appStore.error.status = 'Status is required'
    return appStore.error.status
  }
  appStore.error.status = ''
  return appStore.error.status
}

const isValidate = () => {
  const isValidateName = validatename(appStore.formData.name) == ''
  const isValidateFile = validateFile(appStore.formData.image) == ''
  const isValidateStatus = validateStatus(appStore.formData.status) == ''
  return isValidateName && isValidateFile && isValidateStatus
}

const submit = () => {
  console.log('submit clicked')

  if (!isValidate()) {
    console.log('validation failed')
    return
  }

  console.log('Good')

  const formData = new FormData()

  formData.append('name', appStore.formData.name)

  if (appStore.formData.image instanceof File) {
    formData.append('image', appStore.formData.image)
  }

  formData.append('status', appStore.formData.status)

  appStore.create(formData)
}

const updatename = (id: number, name: string) => {
  if (id && name) {
    alert('Complete')
  }
}

const updateImage = (id: number, image: File | null | undefined) => {
  if (id && image) {
    alert('Complete')
  }
}

const updateStatus = (id: number, status: string) => {
  if (id && status) {
    alert('Complete')
  }
}

// --------------
const data = ref({
  id: 0,
  name: '',
  status: '',
  image: '',
})

const handleUpdate = (item: any) => {
  if (item) {
    data.value.id = item.id
    data.value.name = item.name
    data.value.status = item.status
    data.value.image = item.image_url
    appStore.handleUpdate()
  }
}
// --------------
onMounted(() => {
  appStore.getData(appStore.search, appStore.per_page, appStore.page)
})
</script>

<template>
  <!--  -->

  <category-form v-if="appStore.isUpdateForm" :data="data" />
  <!--  -->
  <div class="p-6 flex gap-6">
    <!-- Block Table -->

    <div class="w-full rounded-lg overflow-clip">
      <table v-if="appStore.data?.data.length" class="w-full text-left font-normal">
        <thead>
          <tr class="w-full bg-orange-500 text-white font-poppins font-normal">
            <th class="px-4 py-3 text-center w-15 font-poppins font-normal">No</th>
            <th class="px-4 py-3 text-left font-poppins font-normal">Nam Category</th>
            <th class="px-4 py-3 text-center font-poppins font-normal">Image</th>
            <th class="px-4 py-3 text-center font-poppins font-normal">Status</th>
            <th class="px-4 py-3 text-center font-poppins font-normal">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          <tr
            v-for="(item, index) in appStore.data?.data"
            :key="index"
            class="hover:bg-orange-100 border-t border-slate-100"
          >
            <td class="px-4 py-3 text-center w-15 font-poppins font-normal">{{ index + 1 }}</td>
            <td class="px-4 py-3 text-left font-poppins font-normal">
              {{ item.name ?? 'Null' }}
            </td>
            <td
              class="px-4 py-3 text-center flex justify-center items-center font-poppins font-normal"
            >
              <form @submit.prevent="updateImage(index, appStore.formData.image)">
                <div>
                  <label>
                    <img
                      class="size-12 rounded-full object-center object-cover border border-white ring-2 ring-orange-500"
                      :src="item.image_url"
                      alt=""
                    />
                    <input type="file" hidden accept=".png,.jpg,.jpeg,.raw,.avif" />
                  </label>
                </div>
              </form>
            </td>
            <td class="px-4 py-3 text-center font-poppins font-normal">
              <span
                class="px-3 py-1 rounded-full capitalize text-xs text-white font-afacad"
                :class="item.status == 'public' ? 'bg-green-700' : 'bg-orange-500'"
                >{{ item.status }}</span
              >
            </td>

            <td class="px-4 py-3 text-center font-poppins font-normal">
              <div class="flex gap-3 justify-center items-center">
                <button
                  @click="handleUpdate(item)"
                  class="bg-linear-to-r from-orange-600 to-orange-400 text-white p-2 rounded-md hover:scale-105 duration-500 cursor-pointer"
                >
                  <component :is="EditIcon" />
                </button>
                <!--  -->
                <button
                  @click="appStore.delete(item.id)"
                  :disabled="appStore.isLoadingAny.isLoading"
                  class="disabled:pointer-events-none disabled:bg-slate-500 bg-linear-to-r from-red-600 to-red-400 text-white p-2 rounded-md hover:scale-105 duration-500 cursor-pointer"
                >
                  <loading-icon
                    v-if="appStore.isLoadingAny.id == item.id && appStore.isLoadingAny.isLoading"
                    class="w-5 h-5"
                  />
                  <component v-else :is="DeleteIcon" />
                </button>
                <!--  -->
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div
        v-if="!appStore.isLoadingIndex && !appStore.data?.data.length"
        class="flex justify-center items-center mt-4"
      >
        <div class="w-20 h-20 p-2 text-orange-500 text-nowrap capitalize">please create a category</div>
      </div>

      <div v-if="appStore.isLoadingIndex" class="flex justify-center items-center mt-4">
        <div class="w-20 h-20 p-2 text-orange-500">
          <loading-icon class="w-full h-full" />
        </div>
      </div>
    </div>
    <!-- Block Table -->

    <!-- Block FormCreate -->
    <div class="w-1/2 h-120 flex flex-col justify-start sticky top-22">
      <div class="shadow rounded-lg bg-white p-6 text-orange-600">
        <h1 class="font-semibold text-xl">Create Categories</h1>

        <form @submit.prevent="submit" class="flex flex-col justify-between w-full">
          <div class="space-y-2">
            <!--  -->
            <div class="w-full">
              <label class="text-orange-500"
                >Name Category <span class="text-red-600">*</span></label
              >
              <div class="relative mt-1">
                <input
                  type="text"
                  v-model="appStore.formData.name"
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
            <div class="w-full">
              <label class="text-orange-500">Image <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <input
                  type="file"
                  accept=".png,.jpg,.jpeg,.raw,.avif"
                  placeholder="example"
                  @change="appStore.handleFileUpload"
                  class="w-full pl-14 p-2.5 text-orange-500 focus:outline-orange-600/50 focus:ring-4 focus:ring-orange-500 rounded-md font-open-sans text-lg"
                  :class="
                    appStore.error.image
                      ? 'border border-red-500 bg-red-100 text-red-500'
                      : 'bg-slate-100'
                  "
                />

                <div
                  class="absolute top-0 left-0 bottom-0 rounded-l-md px-3 flex justify-center items-center bg-linear-to-r from-orange-600 to-orange-400"
                >
                  <component class="text-white size-6" :is="ImgIcon" />
                </div>
              </div>
              <span class="font-poppins text-xs text-red-500">{{ appStore.error.image }}</span>
            </div>
            <!--  -->

            <!--  -->
            <!--  -->
            <div class="w-full">
              <label class="text-orange-500">Status <span class="text-red-600">*</span></label>
              <div class="relative mt-1">
                <select
                  v-model="appStore.formData.status"
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
              @click="appStore.resetClear()"
              type="button"
              class="mt-3 font-open-sans bg-linear-to-r from-red-600 to-red-400 text-white w-full p-2.5 rounded-md cursor-pointer hover:scale-105 duration-500 ease-in-out"
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
  </div>
</template>
