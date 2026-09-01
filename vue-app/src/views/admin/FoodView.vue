<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<script setup lang="ts">
import { onMounted, ref } from 'vue'

import CreateProductForm from '@/components/foods/CreateProductForm.vue'
import { useFoodStore } from '@/stores/food_store'
import { DeleteIcon, EditIcon, LoadingIcon } from '@/stores/icon'
import IconView from '@/components/icons/IconView.vue'
import UpdateProductForm from '@/components/foods/UpdateProductForm.vue'
import type { FoodModel } from '@/models/food_model'

const appStore = useFoodStore()

onMounted(() => {
  appStore.getCategoriesList()
  appStore.getFoodsList()
})

const openCreateModal = () => {
  appStore.setModule()
}
const data = ref<FoodModel>()
const openEditModal = (product: any) => {
  // appStore.openProductModal(product)
  if (product) {
    data.value = product
    // appStore.setModuleUpdate()
    appStore.showModalUpdate = true
  }
}

const handleDelete = async (productId: number) => {
  if (productId) {
    appStore.deleteProduct(productId)
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6 font-hanuman">
    <!-- Header -->
    <div class="w-full">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-bold text-orange-600 mb-2">Products Management</h1>
          <p class="text-slate-600">Manage your restaurant products and menu items</p>
        </div>
        <button
          @click="openCreateModal()"
          class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-400 text-white rounded-lg font-semibold hover:from-orange-700 hover:to-orange-500 transition shadow-lg"
        >
          + Add Product
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-orange-600 to-orange-400 text-white">
              <tr>
                <th class="px-6 py-4 text-left font-semibold">ID</th>
                <th class="px-6 py-4 text-left font-semibold">Image</th>
                <th class="px-6 py-4 text-left font-semibold">Name</th>
                <th class="px-6 py-4 text-left font-semibold">Category</th>
                <th class="px-6 py-4 text-left font-semibold">Price</th>
                <th class="px-6 py-4 text-left font-semibold">Status</th>
                <th class="px-6 py-4 text-center font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(product, index) in appStore.products?.data"
                :key="index"
                class="border-b border-slate-200 hover:bg-orange-50 transition"
              >
                <td class="px-6 py-4 text-slate-700 font-medium">#{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <div
                    v-if="typeof product.image === 'string'"
                    class="w-12 h-12 rounded-md overflow-hidden bg-slate-100"
                  >
                    <img
                      :src="
                        product.image_url ? product.image_url : 'https://via.placeholder.com/150'
                      "
                      :alt="product.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div
                    v-else
                    class="w-12 h-12 rounded-md bg-slate-200 flex items-center justify-center"
                  >
                    <span class="text-xs text-slate-400">No image</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-slate-700 font-medium">{{ product.name }}</td>
                <td class="px-6 py-4 text-slate-600">
                  <span
                    class="px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-sm font-medium"
                  >
                    {{
                      appStore.categories?.data.find((c) => c.id === product.category_id)?.name ||
                      'Unknown'
                    }}
                  </span>
                </td>
                <td class="px-6 py-4 text-orange-500 font-semibold">
                  ${{ Number(product.price).toFixed(2) }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'px-3 py-1 rounded-full text-sm font-medium',
                      product.status === 'active'
                        ? 'bg-green-600 text-white'
                        : 'bg-orange-500 text-white',
                    ]"
                  >
                    {{ product.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2 justify-center">
                    <button
                      @click="openEditModal(product)"
                      class="p-2 bg-linear-to-r from-orange-600 to-orange-400 text-white rounded-lg flex justify-center items-center hover:bg-blue-600 transition text-sm font-medium"
                    >
                      <component
                        :is="EditIcon"
                        class="w-5 h-5 inline-block justify-center items-center"
                      />
                    </button>
                    <button
                      @click="handleDelete(product.id ?? 0)"
                      class="p-2 bg-linear-to-r from-red-600 to-red-400 text-white rounded-lg flex justify-center items-center hover:bg-red-600 transition text-sm font-medium"
                    >
                      <div
                        v-if="
                          appStore.isLoadingAny.loading && appStore.isLoadingAny.id === product.id
                        "
                        class="w-5 h-5 inline-block justify-center items-center"
                      >
                        <LoadingIcon class="w-full h-full" />
                      </div>
                      <component v-else :is="DeleteIcon" class="w-5 h-5 inline-block" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!--  -->
          <div v-if="appStore.isLoading" class="flex justify-center items-center mt-4">
            <div class="w-20 h-20 p-2 text-orange-500">
              <LoadingIcon class="w-full h-full" />
            </div>
          </div>
          <!--  -->
          <div
            v-if="!appStore.products?.data.length && !appStore.isLoading"
            class="flex justify-center items-center mt-4"
          >
            <div class="flex flex-col items-center gap-2">
              <svg
                class="size-12 text-slate-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
              </svg>
              <p class="text-lg font-medium">No products yet</p>
              <p class="text-sm">Create your first product to get started</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <CreateProductForm v-if="appStore.showModal" />
    <UpdateProductForm v-if="appStore.showModalUpdate" :product="data ?? null" />
  </div>
</template>

<style scoped>
/* Add any additional styles here */
</style>
