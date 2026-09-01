<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import { BackIcon } from '@/stores/icon'
import { useMyOrders } from '@/stores/my_order'
import { onMounted } from 'vue'

const myOrders = useMyOrders()

// onMounted(() => {
//   myOrders.getOrders()
// })

const statusLabel: Record<string, string> = {
  pending: 'កំពុងរង់ចាំ',
  preparing: 'កំពុងចម្អិន',
  ready: 'រួចរាល់',
  served: 'បានបម្រើ',
  cancelled: 'បានលុបចោល',
}

const statusColor: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-700 border-yellow-300',
  preparing: 'bg-blue-100 text-blue-700 border-blue-300',
  ready: 'bg-purple-100 text-purple-700 border-purple-300',
  served: 'bg-green-100 text-green-700 border-green-300',
  cancelled: 'bg-red-100 text-red-700 border-red-300',
}

const paymentStatusLabel: Record<string, string> = {
  unpaid: 'មិនទាន់បង់',
  partial: 'បង់ខ្លះ',
  paid: 'បង់រួច',
  refunded: 'បានបង្វិលសង',
}

const paymentStatusColor: Record<string, string> = {
  unpaid: 'bg-red-100 text-red-700 border-red-300',
  partial: 'bg-yellow-100 text-yellow-700 border-yellow-300',
  paid: 'bg-green-100 text-green-700 border-green-300',
  refunded: 'bg-gray-100 text-gray-700 border-gray-300',
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <main class="w-full flex flex-col items-center justify-start font-hanuman">
    <div class="w-full max-w-4xl mx-auto py-6 px-4 flex flex-col gap-6">
      <!-- Header -->
      <div class="w-full flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">ព័ត៌មានលម្អិតការកម្ម៉ង់</h1>
        <button
          @click="$router.push({ name: 'card' })"
          class="px-4 py-2 flex justify-center items-center gap-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer transition"
        >
          <component :is="BackIcon" />
          <span>Back</span>
        </button>
      </div>

      <!-- Empty state -->
      <div
        v-if="!myOrders.data || myOrders.data.data.length === 0"
        class="w-full py-16 flex flex-col items-center justify-center text-gray-400 gap-2"
      >
        <span class="text-4xl">🧾</span>
        <span>មិនទាន់មានការកម្ម៉ង់</span>
      </div>

      <!-- Orders list (invoice style) -->
      <div v-else class="w-full flex flex-col gap-6">
        <div
          v-for="order in myOrders.data.data"
          :key="order.id"
          class="w-full bg-white rounded-lg border border-orange-200 shadow-sm overflow-hidden"
        >
          <!-- Invoice header -->
          <div
            class="bg-gradient-to-r from-orange-500 to-orange-400 px-5 py-4 flex flex-wrap justify-between items-center gap-2"
          >
            <div class="flex flex-col text-white">
              <span class="text-xs opacity-80">លេខការកម្ម៉ង់</span>
              <span class="font-semibold tracking-wide">{{ order.order_no }}</span>
            </div>
            <div class="flex flex-col text-white text-right">
              <span class="text-xs opacity-80">តុលេខ</span>
              <span class="font-semibold">{{ order.table?.table_number ?? '-' }}</span>
            </div>
          </div>

          <!-- Meta row -->
          <div
            class="px-5 py-3 flex flex-wrap justify-between items-center gap-2 border-b border-dashed border-gray-200 text-sm text-gray-500"
          >
            <span>{{ formatDate(order.created_at) }}</span>
            <div class="flex gap-2">
              <span
                class="px-2.5 py-1 rounded-full text-xs font-medium border"
                :class="paymentStatusColor[order.payment_status]"
              >
                {{ paymentStatusLabel[order.payment_status] ?? order.payment_status }}
              </span>
              <span
                class="px-2.5 py-1 rounded-full text-xs font-medium border bg-slate-100 text-slate-600 border-slate-300"
              >
                {{ order.payment_method === 'cash' ? 'សាច់ប្រាក់' : 'PayWay' }}
              </span>
            </div>
          </div>

          <!-- Items -->
          <div class="px-5 py-4 flex flex-col gap-3">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex items-center gap-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0"
            >
              <img
                :src="item.food.image_url"
                :alt="item.food.name"
                class="w-14 h-14 rounded-md object-cover border border-gray-200 flex-shrink-0"
              />

              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ item.food.name }}</p>
                <p class="text-sm text-orange-500 font-semibold">
                  ${{ (item.food.discount_price ?? item.food.price).toFixed(2) }}
                </p>
              </div>

              <span
                class="px-2.5 py-1 rounded-full text-xs font-medium border whitespace-nowrap"
                :class="statusColor[item.status]"
              >
                {{ statusLabel[item.status] ?? item.status }}
              </span>
            </div>
          </div>

          <!-- Note -->
          <div v-if="order.note" class="px-5 pb-3 text-sm text-gray-500">
            <span class="font-medium text-gray-600">កំណត់សម្គាល់:</span> {{ order.note }}
          </div>

          <!-- Total -->
          <div
            class="px-5 py-4 bg-orange-50 flex justify-between items-center border-t border-dashed border-orange-200"
          >
            <span class="font-medium text-gray-700">សរុប</span>
            <span class="text-lg font-bold text-orange-600">${{ order.total.toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <div class="h-30 hidden max-lg:block"></div>
    </div>
  </main>
</template>
