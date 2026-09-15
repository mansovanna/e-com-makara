<!-- eslint-disable vue/multi-word-component-names -->
<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'

import { BackIcon } from '@/stores/icon'
import { useShoppStore } from '@/stores/shopp_store'

const router = useRouter()
const cart = useShoppStore()

function toggleSelect(id: number) {
  cart.toggleSelect(id) // store action to flip item.selected
}

function toggleSelectAll(e: Event) {
  const checked = (e.target as HTMLInputElement).checked
  cart.selectAll(checked)
}

const allSelected = computed(
  () => cart.products.length > 0 && cart.products.every((i) => i.selected),
)

const selectedItems = computed(() => cart.products.filter((i) => i.selected))

const selectedSubtotal = computed(() =>
  selectedItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0),
)

const selectedTotal = computed(() => selectedSubtotal.value + cart.deliveryFee)
</script>

<template>
  <main class="w-full flex flex-col items-center justify-start​">
    <div class="w-full max-w-6xl mx-auto py-6 px-4 flex flex-col gap-6">
      <!-- Header -->
      <div class="w-full flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">កន្រ្ដក</h1>
        <button
          @click="router.push({ name: 'home' })"
          class="px-4 py-2 font-hanuman flex justify-center items-center gap-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer transition"
        >
          <component :is="BackIcon" />
          <span>ត្រឡប់ក្រោយ</span>
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="cart.products.length === 0"
        class="w-full flex flex-col items-center justify-center py-24 gap-4 bg-white rounded-lg shadow-sm"
      >
        <p class="text-gray-400 text-lg font-hanuman">មិនទាន់មានទំនិញនៅក្នុងកន្ត្រកទេ</p>
        <button
          @click="router.push({ name: 'home' })"
          class="px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition font-hanuman"
        >
          ជ្រើសរើសទំនិញ
        </button>
      </div>

      <!-- Cart content -->
      <div v-else class="w-full grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Item list -->
        <div class="lg:col-span-2 flex flex-col gap-4">
          <!-- Select all -->
          <div
            class="w-full flex items-center gap-3 bg-white rounded-lg border border-orange-500 p-4"
          >
            <input
              :checked="allSelected"
              @change="toggleSelectAll"
              type="checkbox"
              class="w-5 h-5 accent-orange-500 cursor-pointer"
            />
            <span class="text-gray-700 font-medium font-hanuman"
              >ជ្រើសរើសទាំងអស់ ({{ cart.products.length }})</span
            >
          </div>

          <label v-for="item in cart.products" :key="item.id">
            <div
              class="w-full flex items-center gap-4 bg-white rounded-lg p-4 border"
              :class="
                item.selected ? ' border-orange-600 ring-2 ring-orange-300' : ' border-slate-200'
              "
            >
              <input
                :checked="item.selected"
                @change="toggleSelect(item.id)"
                type="checkbox"
                hidden
                class="w-5 h-5 accent-orange-500 cursor-pointer flex-shrink-0"
              />

              <img
                :src="item.image_url"
                :alt="item.name"
                class="w-20 h-20 object-cover border border-orange-200 rounded-md flex-shrink-0"
              />

              <div class="flex-1 flex flex-col gap-1">
                <h3 class="font-medium text-gray-800">{{ item.name }}</h3>
                <p v-if="item.note" class="text-sm text-gray-400">{{ item.note }}</p>
                <span class="text-orange-500 font-semibold">${{ item.price.toFixed(2) }}</span>
              </div>

              <div class="flex items-center gap-3 border border-gray-200 rounded-md overflow-clip">
                <button
                  @click="cart.decrementProductQuantity(item.id)"
                  class="w-8 h-8 bg-orange-600 text-white flex items-center justify-center hover:bg-orange-500 cursor-pointer"
                >
                  −
                </button>
                <span class="w-6 text-center">{{ item.quantity }}</span>
                <button
                  @click="cart.incrementProductQuantity(item.id)"
                  class="w-8 h-8 bg-orange-600 text-white flex items-center justify-center hover:bg-orange-500 cursor-pointer"
                >
                  +
                </button>
              </div>

              <div class="w-20 text-right font-semibold text-xl text-red-500">
                ${{ (item.price * item.quantity).toFixed(2) }}
              </div>

              <button
                @click="cart.removeFromCart(item.id)"
                class="text-red-400 hover:text-red-500 cursor-pointer"
                title="លុបចេញ"
              >
                ✕
              </button>
            </div>
          </label>
        </div>

        <!-- Order summary -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-lg border border-orange-500 p-5 flex flex-col gap-4 sticky top-6"
          >
            <h3 class="font-semibold text-gray-800 border-b pb-3 font-hanuman">សរុបការកម្មង់</h3>

            <div class="flex justify-between text-sm text-gray-600 font-hanuman">
              <span>ទំនិញបានជ្រើស</span>
              <span>{{ selectedItems.length }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 font-hanuman">
              <span>តម្លៃទំនិញ</span>
              <span>${{ selectedSubtotal.toFixed(2) }}</span>
            </div>
            <!-- <div class="flex justify-between text-sm text-gray-600 font-hanuman">
              <span>ថ្លៃដឹកជញ្ជូន</span>
              <span>${{ cart.deliveryFee.toFixed(2) }}</span>
            </div> -->

            <div class="flex justify-between font-semibold text-gray-800 border-t pt-3 font-hanuman">
              <span>សរុប</span>
              <span class="text-orange-500">${{ selectedTotal.toFixed(2) }}</span>
            </div>

            <button
              :disabled="selectedItems.length === 0"
              @click="
                router.push({
                  name: 'checkout',
                  query: { item: JSON.stringify(selectedItems) },
                })
              "
              :class="[
                'w-full py-2 rounded-md font-medium transition font-hanuman',
                selectedItems.length > 0
                  ? 'bg-orange-500 text-white hover:bg-orange-600 cursor-pointer'
                  : 'bg-gray-200 text-gray-400 cursor-not-allowed',
              ]"
            >
              បន្តទៅការដាក់កម្ម៉ង់ ({{ selectedItems.length }})
            </button>
          </div>
        </div>
      </div>

      <div class="h-20 hidden max-lg:block"></div>
    </div>
  </main>
</template>
