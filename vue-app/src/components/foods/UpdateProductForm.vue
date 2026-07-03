<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

import { CategorisIcon, CloseIcon, StatusIcon } from '@/stores/icon'
import IconSearch from '../icons/IconSearch.vue'
import { useFoodStore } from '@/stores/food_store.ts'
import type { FoodModel } from '@/models/food_model.ts'

const appStore = useFoodStore()
const imagePreview = ref<string | null>(null)
const imageFile = ref<File | null>(null)
const showCategoryDropdown = ref(false)

// -----------
const prop = defineProps<{
  product: FoodModel | null
}>()

// Filter categories based on search
const filteredCategories = computed(() => {
  if (!appStore.categories?.data) return []
  return appStore.categories.data.filter((cat) =>
    cat.name.toLowerCase().includes(appStore.search.toLowerCase()),
  )
})

// Handle image upload
const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    imageFile.value = file
    appStore.formData.image = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

// Select category
const selectCategory = (categoryId: number, categoryName: string) => {
  appStore.formData.category_id = categoryId
  appStore.selectedCategoryName = categoryName
  showCategoryDropdown.value = false
}

// Close modal
const closeModal = () => {
  appStore.setModuleUpdate()
}

// Submit form
const handleSubmit = async () => {
  if (!appStore.validateFormUpdate()) return
  const formData = new FormData()
  formData.append('name', appStore.formData.name)
  formData.append('category_id', appStore.formData.category_id?.toString() || '')
  formData.append('price', appStore.formData.price?.toString() || '')
  formData.append('status', appStore.formData.status)
  formData.append('description', appStore.formData.description)
  if (imageFile.value) {
    formData.append('image', imageFile.value)
  }
  formData.append('_method', 'PUT') // For Laravel to recognize as PUT request
  await appStore.updateProduct(prop.product?.id as number, formData)
}

onMounted(() => {
  if (prop.product) {
    appStore.formData = { ...prop.product }
    appStore.formData.image = null // Reset image to null for new upload
    appStore.selectedCategoryName =
      appStore.categories?.data.find((c) => c.id === prop.product?.category_id)?.name || ''
    imagePreview.value = prop.product.image_url || null
  }
})
</script>

<template>
  <div
    @click="closeModal()"
    class="top-0 bottom-0 right-0 left-0 fixed flex justify-center items-center z-50 bg-black/30"
  >
    <div @click.stop class="w-full max-w-2xl bg-white rounded-xl p-8 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="w-full flex justify-between items-center gap-3 mb-6">
        <h1 class="text-3xl text-orange-500 font-semibold">Edit Product</h1>
        <button
          @click="closeModal"
          class="p-2 rounded-lg bg-gradient-to-r from-orange-600 to-orange-400 cursor-pointer text-white hover:from-orange-700 hover:to-orange-500 transition"
        >
          <component :is="CloseIcon" class="size-5" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit()" class="space-y-5">
        <!-- Row 1: Name & Category -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Name Field -->
          <div>
            <label class="block text-orange-500 font-medium mb-1">
              Name <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <input
                type="text"
                v-model="appStore.formData.name"
                placeholder="Product name"
                class="w-full pl-12 pr-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base transition"
                :class="
                  appStore.errors.name
                    ? 'border-2 border-red-500 bg-red-50'
                    : 'bg-slate-100 border border-slate-200'
                "
              />
              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-5" :is="CategorisIcon" />
              </div>
            </div>
            <span v-if="appStore.errors.name" class="text-xs text-red-500 mt-1 block">{{
              appStore.errors.name
            }}</span>
          </div>

          <!-- Category Field -->
          <div class="relative">
            <label class="block text-orange-500 font-medium mb-1">
              Category <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <button
                type="button"
                @click="showCategoryDropdown = !showCategoryDropdown"
                class="w-full pl-12 pr-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base text-left transition"
                :class="
                  appStore.errors.category_id
                    ? 'border-2 border-red-500 bg-red-50'
                    : 'bg-slate-100 border border-slate-200'
                "
              >
                {{ appStore.selectedCategoryName || '-- Select category --' }}
              </button>

              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-5" :is="CategorisIcon" />
              </div>
            </div>

            <!-- Category Dropdown Menu -->
            <div
              v-show="showCategoryDropdown"
              class="absolute top-full left-0 right-0 mt-2 w-full max-h-60 z-40 flex flex-col gap-2 bg-white border border-orange-300 shadow-lg rounded-lg overflow-hidden"
            >
              <div class="p-3 border-b border-orange-200">
                <div class="relative">
                  <input
                    type="text"
                    v-model="appStore.search"
                    placeholder="Search category..."
                    class="w-full pl-10 pr-3 py-2 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-400 rounded-md bg-slate-50 text-sm"
                  />
                  <component
                    class="absolute left-3 top-2.5 text-orange-400 size-4"
                    :is="IconSearch"
                  />
                </div>
              </div>

              <div class="overflow-y-auto px-2 pb-2">
                <div
                  v-if="filteredCategories.length === 0"
                  class="text-center py-4 text-slate-400 text-sm"
                >
                  No categories found
                </div>
                <button
                  v-for="category in filteredCategories"
                  :key="category.id"
                  type="button"
                  @click="selectCategory(category.id, category.name)"
                  class="w-full text-left px-3 py-2.5 text-orange-600 hover:bg-orange-50 rounded transition text-sm font-medium"
                  :class="
                    appStore.formData.category_id === category.id
                      ? 'bg-orange-100 border-l-4 border-orange-500'
                      : ''
                  "
                >
                  {{ category.name }}
                </button>
              </div>
            </div>

            <span v-if="appStore.errors.category_id" class="text-xs text-red-500 mt-1 block">{{
              appStore.errors.category_id
            }}</span>
          </div>
        </div>

        <!-- Row 2: Price & Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Price Field -->
          <div>
            <label class="block text-orange-500 font-medium mb-1">
              Price ($) <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <input
                type="number"
                v-model.number="appStore.formData.price"
                placeholder="0.00"
                step="0.01"
                min="0"
                class="w-full pl-12 pr-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base transition"
                :class="
                  appStore.errors.price
                    ? 'border-2 border-red-500 bg-red-50'
                    : 'bg-slate-100 border border-slate-200'
                "
              />
              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
              >
                <span class="text-white text-sm font-bold">$</span>
              </div>
            </div>
            <span v-if="appStore.errors.price" class="text-xs text-red-500 mt-1 block">{{
              appStore.errors.price
            }}</span>
          </div>

          <!-- Status Field -->
          <div>
            <label class="block text-orange-500 font-medium mb-1">
              Status <span class="text-red-600">*</span>
            </label>
            <div class="relative">
              <select
                v-model="appStore.formData.status"
                class="w-full pl-12 pr-4 py-2.5 text-orange-500 capitalize focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base appearance-none transition"
                :class="
                  appStore.errors.status
                    ? 'border-2 border-red-500 bg-red-50'
                    : 'bg-slate-100 border border-slate-200'
                "
              >
                <option value="" disabled>-- Select status --</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>

              <div
                class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
              >
                <component class="text-white size-5" :is="StatusIcon" />
              </div>

              <!-- Dropdown arrow -->
              <div
                class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none text-orange-500"
              >
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                  />
                </svg>
              </div>
            </div>
            <span v-if="appStore.errors.status" class="text-xs text-red-500 mt-1 block">{{
              appStore.errors.status
            }}</span>
          </div>
        </div>

        <!-- Row 3: Image Upload -->
        <div>
          <label class="block text-orange-500 font-medium mb-1">
            Image <span class="text-red-600">*</span>
          </label>
          <div class="flex gap-4">
            <!-- Image Preview -->
            <div
              class="w-32 h-32 rounded-lg border-2 border-dashed border-orange-300 flex items-center justify-center bg-orange-50 overflow-hidden"
            >
              <img
                v-if="imagePreview"
                :src="imagePreview"
                alt="Preview"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-center">
                <svg
                  class="mx-auto size-8 text-orange-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <p class="text-xs text-orange-500 mt-2">Image</p>
              </div>
            </div>

            <!-- File Input -->
            <div class="flex-1">
              <input
                type="file"
                @change="handleImageUpload"
                accept="image/*"
                class="hidden"
                id="imageInput"
              />
              <label
                for="imageInput"
                class="block w-full px-4 py-3 border-2 border-dashed border-orange-300 rounded-lg text-center cursor-pointer hover:bg-orange-50 transition"
              >
                <svg
                  class="mx-auto size-6 text-orange-500 mb-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                  />
                </svg>
                <p class="text-sm font-medium text-orange-600">Click to upload or drag</p>
                <p class="text-xs text-orange-500">PNG, JPG, GIF up to 10MB</p>
              </label>
            </div>
          </div>
          <span v-if="appStore.errors.image" class="text-xs text-red-500 mt-1 block">{{
            appStore.errors.image
          }}</span>
        </div>

        <!-- Row 4: Description -->
        <div>
          <label class="block text-orange-500 font-medium mb-1">
            Description <span class="text-red-600">*</span>
          </label>
          <textarea
            v-model="appStore.formData.description"
            placeholder="Product description..."
            rows="4"
            class="w-full px-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base resize-none transition"
            :class="
              appStore.errors.description
                ? 'border-2 border-red-500 bg-red-50'
                : 'bg-slate-100 border border-slate-200'
            "
          ></textarea>
          <span v-if="appStore.errors.description" class="text-xs text-red-500 mt-1 block">{{
            appStore.errors.description
          }}</span>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 justify-end pt-4 border-t border-slate-200">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2.5 text-orange-600 border border-orange-300 rounded-lg hover:bg-orange-50 transition font-medium"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="appStore.isLoading"
            class="px-8 py-2.5 bg-gradient-to-r from-orange-600 to-orange-400 text-white rounded-lg hover:from-orange-700 hover:to-orange-500 transition font-medium shadow-md"
          >
            <span>{{ appStore.isLoading ? 'Updating...' : 'Update Product' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Hide number spinner */
input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
}

/* Select dropdown styling */
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23f97316' stroke-width='2'%3E%3Cpath d='M19 14l-7 7m0 0l-7-7m7 7V3'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1.25rem;
  padding-right: 2.5rem;
}
</style>
