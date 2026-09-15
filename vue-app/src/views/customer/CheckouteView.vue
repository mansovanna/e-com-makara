<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { BackIcon, CategorisIcon, CloseIcon, LoadingIcon } from '@/stores/icon'
import { useShoppStore } from '@/stores/shopp_store'
import IconSearch from '@/components/icons/IconSearch.vue'
import { useTableStore } from '@/stores/table_store'
import BakongQrPayment from '@/components/utils/BakongQrPayment.vue'

const route = useRoute()

const cart = useShoppStore()
const appTable = useTableStore()

interface CheckoutItem {
  id: number
  name: string
  image: string
  price: number
  discount_price: number | null
  quantity: number
  selected: boolean
  category?: { name: string }
}

const form = ref({
  table: '',
  note: '',
})

// Select category
const selectCategory = (categoryId: number, categoryName: string) => {
  cart.formData.table_id = categoryId
  cart.selectedCategoryName = categoryName
  cart.showCategoryDropdown = false
}

// Filter categories based on search
const filteredCategories = computed(() => {
  if (!appTable.tables) return []
  return appTable.tables.filter((cat) =>
    cat.table_number.toLowerCase().includes(cart.search.toLowerCase()),
  )
})

const checkoutItems = computed<CheckoutItem[]>(() => {
  const raw = route.query.item
  if (!raw) return []
  try {
    // route.query.item អាចជា string ឬ (string | null)[]
    const jsonStr = Array.isArray(raw) ? raw[0] : raw
    const parsed = JSON.parse(jsonStr as string)
    return Array.isArray(parsed) ? parsed : [parsed]
  } catch (e) {
    console.error('Failed to parse checkout items:', e)
    return []
  }
})

const subtotal = computed(() =>
  checkoutItems.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
)

const deliveryFee = 0
const total = computed(() => subtotal.value + deliveryFee)

onMounted(() => {
  appTable.fetchTables()
})

const router = useRouter()

interface StoredOrder {
  id: number
  order_no: string
}

const STORAGE_KEY = 'my_orders'

const saveOrderToStorage = (order: StoredOrder) => {
  const raw = localStorage.getItem(STORAGE_KEY)
  const list: StoredOrder[] = raw ? JSON.parse(raw) : []

  list.push({ id: order.id, order_no: order.order_no })

  localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
}

const isLoading = ref(false)
const isErrorMessage = ref<any>(null)
const success = ref('')
// --------------- push order --------------------
const orders = async (data: any) => {
  isLoading.value = true
  isErrorMessage.value = null
  success.value = ''

  try {
    const res = await cart.placeOrder(data)

    if (res.status === 200 || res.status === 201) {
      success.value = 'Order success!'

      const order = res.data.order
      saveOrderToStorage(order)
      localStorage.removeItem('cart')
      cart.products = []

      // redirect ទៅ order confirmation/status page
      router.push({ name: 'orders' })
    }
  } catch (error: any) {
    console.error(error)
    isErrorMessage.value = error.response?.data ?? 'Something went wrong.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <!-- Block QR Code Sanner Module -->
  <bakong-qr-payment />
  <!-- End Block QR Code Scanner -->
  <main class="w-full flex flex-col items-center justify-start font-hanuman">
    <div class="w-full max-w-6xl mx-auto py-6 px-4 flex flex-col gap-6">
      <!-- Header -->
      <div class="w-full flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">ព័ត៌មានលម្អិត</h1>
        <button
          @click="$router.push({ name: 'card' })"
          class="px-4 py-2 flex justify-center items-center gap-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer transition"
        >
          <component :is="BackIcon" />
          <span>ត្រឡប់ក្រោយ</span>
        </button>
      </div>

      <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: form -->
        <div class="lg:col-span-2 flex flex-col gap-6">
          <!-- Delivery info -->
          <div class="bg-white rounded-lg border border-orange-500 p-5 flex flex-col gap-4">
            <h3 class="font-semibold text-gray-800 border-b pb-3">ព័ត៌មានកន្លែងអង្គុយ</h3>

            <div class="flex flex-col gap-1">
              <!-- Category Field -->
              <div class="relative">
                <label class="block text-orange-500 font-medium mb-1">
                  លេខតុរបស់អ្នក <span class="text-red-600">*</span>
                </label>
                <div class="relative">
                  <button
                    type="button"
                    @click="cart.showCategoryDropdown = !cart.showCategoryDropdown"
                    class="w-full pl-12 pr-4 py-2.5 text-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-lg font-sans text-base text-left transition"
                    :class="
                      cart.errors.table_id
                        ? 'border-2 border-red-500 bg-red-50'
                        : 'bg-slate-100 border border-slate-200'
                    "
                  >
                    {{
                      cart.selectedCategoryName
                        ? cart.selectedCategoryName
                        : '-- Select category --'
                    }}
                  </button>

                  <div
                    class="absolute top-0 left-0 bottom-0 rounded-l-lg px-3 flex justify-center items-center bg-gradient-to-r from-orange-600 to-orange-400"
                  >
                    <component class="text-white size-5" :is="CategorisIcon" />
                  </div>
                </div>

                <!-- Category Dropdown Menu -->
                <div
                  v-show="cart.showCategoryDropdown"
                  class="absolute top-full left-0 right-0 mt-2 w-full max-h-60 z-40 flex flex-col gap-2 bg-white border border-orange-300 shadow-lg rounded-lg overflow-hidden"
                >
                  <div class="p-3 border-b border-orange-200">
                    <div class="relative">
                      <input
                        type="text"
                        v-model="cart.search"
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
                      @click="selectCategory(category.id, category.table_number)"
                      class="w-full text-left px-3 py-2.5 text-orange-600 hover:bg-orange-50 rounded transition text-sm font-medium"
                      :class="
                        cart.formData.table_id === category.id
                          ? 'bg-orange-100 border-l-4 border-orange-500'
                          : ''
                      "
                    >
                      {{ category.table_number }}
                    </button>
                  </div>
                </div>

                <span v-if="cart.errors.table_id" class="text-xs text-red-500 mt-1 block">{{
                  cart.errors.table_id
                }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-sm text-gray-600">កំណត់សម្គាល់ (ស្រេចចិត្ត)</label>
              <textarea
                v-model="form.note"
                rows="2"
                class="border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:border-orange-400"
                placeholder="ឧ. សូមកុំដាក់ម្សៅ..."
              />
            </div>
          </div>
        </div>

        <!-- Right: order summary -->
        <div class="lg:col-span-1">
          <div
            class="bg-white rounded-lg border border-orange-400 p-5 flex flex-col gap-4 sticky top-6"
          >
            <h3 class="font-semibold text-gray-800 border-b pb-3">ការកម្មង់របស់អ្នក</h3>
            <p
              v-if="isErrorMessage"
              class="text-red-400 bg-red-500/20 rounded-lg px-3 py-1 capitalize text-xs"
            >
              {{ isErrorMessage.errors?.table_id[0]== 'The table id field is required.'? 'សូមជ្រើរើសលេខតុ':  isErrorMessage.errors}}
            </p>

            <div class="flex flex-col gap-3 max-h-64 overflow-y-auto">
              <div
                v-for="(item, index) in checkoutItems"
                :key="index"
                class="flex justify-between text-sm text-gray-600"
              >
                <span>{{ item.name }} × {{ item.quantity }}</span>
                <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
              </div>
            </div>

            <div class="flex flex-col gap-2 border-t pt-3">
              <div class="flex justify-between text-sm text-gray-600">
                <span>តម្លៃទំនិញ</span>
                <span>${{ subtotal.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between font-semibold text-gray-800 border-t pt-2">
                <span>សរុប</span>
                <span class="text-orange-500">${{ total.toFixed(2) }}</span>
              </div>
            </div>

            <button
              @click="
                orders({
                  table_id: cart.formData.table_id,
                  note: form.note,
                  payment_method: cart.paymentMethod,
                  items: checkoutItems.map((item) => ({
                    food_id: item.id,
                    quantity: item.quantity,
                  })),
                })
              "
              class="w-full py-2 bg-orange-500 text-white rounded-md font-medium hover:bg-orange-600 transition cursor-pointer"
            >
              <div v-if="isLoading" class="flex justify-center items-center gap-1">
                <loading-icon />
                <span> សូមរងចាំបន្តិច... </span>
              </div>
              <span v-else>ដាក់កម្ម៉ង់ឥឡូវនេះ</span>
            </button>
          </div>
        </div>
      </div>
      <div class="h-30 hidden max-lg:block"></div>
    </div>
  </main>
</template>
