<!-- eslint-disable vue/multi-word-component-names -->
<script setup lang="ts">
import { computed } from 'vue'
import { BackIcon } from '@/stores/icon'
import { useShoppStore } from '@/stores/shopp_store'
const shopStore = useShoppStore()

// តម្រៀបទិន្នន័យថ្មីបំផុតឲ្យនៅលើគេ
const sortedReceipts = computed(() => {
  return [...(shopStore.recepts ?? [])].sort((a, b) => {
    return new Date(b.order.created_at).getTime() - new Date(a.order.created_at).getTime()
  })
})

const statusLabel: Record<string, string> = {
  pending: 'កំពុងរង់ចាំ',
  paid: 'បានទូទាត់',
  cancelled: 'បានលុបចោល',
  completed: 'បានបញ្ចប់',
}

const statusColor: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-700 border-yellow-300',
  paid: 'bg-green-100 text-green-700 border-green-300',
  cancelled: 'bg-red-100 text-red-700 border-red-300',
  completed: 'bg-blue-100 text-blue-700 border-blue-300',
}

const paymentLabel: Record<string, string> = {
  payway: 'PayWay',
  bakong: 'Bakong KHQR',
  cash: 'សាច់ប្រាក់',
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleString('km-KH', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function money(n: number) {
  return `$${Number(n).toFixed(2)}`
}
</script>

<template>
  <main class="w-full flex flex-col items-center justify-start bg-gray-50">
    <div class="w-full max-w-3xl px-4 py-6 flex flex-col justify-center items-start gap-6">
      <!-- Header -->
      <div class="w-full flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">ព័ត៌មានផ្សេងៗ</h1>
        <button
          @click="$router.push({ name: 'home' })"
          class="px-4 py-2 flex justify-center items-center gap-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer transition"
        >
          <component :is="BackIcon" />
          <span>ត្រឡប់ទៅទំព័រដើម</span>
        </button>
      </div>

      <!-- Receipts -->
      <div class="w-full flex flex-col gap-5">
        <div
          v-for="(item, index) in sortedReceipts"
          :key="index"
          class="w-full bg-white border border-gray-200 rounded-xl overflow-hidden"
        >
          <!-- Receipt top: order no + status -->
          <div
            class="px-5 py-3 bg-orange-50 border-b border-dashed border-orange-300 flex justify-between items-center"
          >
            <div>
              <p class="text-sm text-gray-500">លេខកម្មង់ / Order No</p>
              <p class="font-semibold text-gray-800">{{ item.order.order_no }}</p>
            </div>
            <span
              class="text-xs font-medium px-3 py-1 rounded-full border"
              :class="statusColor[item.order.status] ?? 'bg-gray-100 text-gray-600 border-gray-300'"
            >
              {{ statusLabel[item.order.status] ?? item.order.status }}
            </span>
          </div>

          <!-- Table + date + payment -->
          <div
            class="px-5 py-3 grid grid-cols-2 gap-2 text-sm text-gray-600 border-b border-gray-100"
          >
            <p>
              តុលេខ:
              <span class="font-medium text-gray-800">{{
                item.order.table?.table_number ?? '-'
              }}</span>
            </p>
            <p class="text-right">
              ការទូទាត់:
              <span class="font-medium text-gray-800">{{
                paymentLabel[item.order.payment_method] ?? item.order.payment_method
              }}</span>
            </p>
            <p class="col-span-2 text-xs text-gray-400">{{ formatDate(item.order.created_at) }}</p>
          </div>

          <!-- Items -->
          <div class="px-5 py-3 flex flex-col gap-3">
            <div
              v-for="foodItem in item.order.items"
              :key="foodItem.id"
              class="flex items-center gap-3"
            >
              <img
                :src="foodItem.food.image_url"
                :alt="foodItem.food.name"
                class="w-12 h-12 object-cover rounded-md border border-gray-200 shrink-0"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ foodItem.food.name }}</p>
                <p class="text-xs text-gray-400">
                  {{ money(foodItem.price) }} × {{ foodItem.quantity }}
                </p>
              </div>
              <p class="text-sm font-semibold text-gray-800">{{ money(foodItem.subtotal) }}</p>
            </div>
          </div>

          <!-- Totals -->
          <div
            class="px-5 py-3 bg-gray-50 border-t border-dashed border-gray-300 flex flex-col gap-1"
          >
            <div class="flex justify-between text-sm text-gray-600">
              <span>សរុបរង</span>
              <span>{{ money(item.order.subtotal) }}</span>
            </div>
            <div v-if="item.order.discount" class="flex justify-between text-sm text-red-500">
              <span>បញ្ចុះតម្លៃ</span>
              <span>-{{ money(item.order.discount) }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-orange-600 pt-1">
              <span>សរុប</span>
              <span>{{ money(item.order.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-if="!sortedReceipts.length"
          class="w-full py-16 flex flex-col items-center justify-center text-gray-400"
        >
          <p>មិនទាន់មានវិក្កយបត្រនៅឡើយទេ</p>
        </div>
      </div>

      <div class="h-20"></div>
    </div>
  </main>
</template>
