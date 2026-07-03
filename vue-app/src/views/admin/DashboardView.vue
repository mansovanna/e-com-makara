<script setup lang="ts">
import { useOrderStore } from '@/stores/order_store'
import { computed, onMounted } from 'vue'
import {
  IconCash,
  IconReceipt2,
  IconClock,
  IconArmchair,
  IconTrendingUp,
  IconTrendingDown,
  IconFlame,
  IconArrowRight,
} from '@tabler/icons-vue'

interface OrderFood {
  id: number
  name: string
  image_url: string
}
interface OrderItem {
  id: number
  food_id: number
  quantity: number
  subtotal: string | number
  food: OrderFood
}
interface OrderTable {
  id: number
  table_number: string
}
type OrderStatus = 'pending' | 'preparing' | 'completed' | 'cancelled'
interface Order {
  id: number
  order_no: string
  status: OrderStatus
  payment_method: 'payway' | 'khqr' | 'cash'
  total: string | number
  created_at: string
  items: OrderItem[]
  table: OrderTable
}

const orderStore = useOrderStore()

onMounted(() => {
  orderStore.getList()
  // orderStore.getSumary()
})

const orders = computed<Order[]>(() => orderStore.data ?? [])

const isToday = (iso: string) => {
  const d = new Date(iso)
  const now = new Date()
  return d.toDateString() === now.toDateString()
}

const todayOrders = computed(() => orders.value.filter((o) => isToday(o.created_at)))

const todayRevenue = computed(() =>
  todayOrders.value
    .filter((o) => o.status !== 'cancelled')
    .reduce((sum, o) => sum + Number(o.total), 0),
)

const pendingCount = computed(
  () => orders.value.filter((o) => o.status === 'pending' || o.status === 'preparing').length,
)

const occupiedTables = computed(() => {
  const active = orders.value.filter((o) => o.status !== 'completed' && o.status !== 'cancelled')
  return new Set(active.map((o) => o.table.table_number)).size
})

const formatMoney = (val: number) => `$${val.toFixed(2)}`

// ---- ៧ថ្ងៃចុងក្រោយសម្រាប់ trend chart ----
const last7Days = computed(() => {
  const days: { label: string; total: number }[] = []
  for (let i = 6; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    const dayTotal = orders.value
      .filter(
        (o) =>
          new Date(o.created_at).toDateString() === d.toDateString() && o.status !== 'cancelled',
      )
      .reduce((sum, o) => sum + Number(o.total), 0)
    days.push({ label: d.toLocaleDateString('en-US', { weekday: 'short' }), total: dayTotal })
  }
  return days
})

const maxDayTotal = computed(() => Math.max(...last7Days.value.map((d) => d.total), 1))

const trendDirection = computed(() => {
  const vals = last7Days.value.map((d) => d.total)
  const yesterday = vals[vals.length - 2] ?? 0
  const today = vals[vals.length - 1] ?? 0
  return today >= yesterday ? 'up' : 'down'
})

// ---- Order ថ្មីៗ ----
const recentOrders = computed(() =>
  [...orders.value]
    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    .slice(0, 5),
)

const statusStyle: Record<OrderStatus, { bg: string; text: string }> = {
  pending: { bg: 'bg-amber-50', text: 'text-amber-700' },
  preparing: { bg: 'bg-blue-50', text: 'text-blue-700' },
  completed: { bg: 'bg-emerald-50', text: 'text-emerald-700' },
  cancelled: { bg: 'bg-rose-50', text: 'text-rose-700' },
}
const statusLabel: Record<OrderStatus, string> = {
  pending: 'កំពុងរង់ចាំ',
  preparing: 'កំពុងធ្វើ',
  completed: 'រួចរាល់',
  cancelled: 'បានលុប',
}

const formatTime = (iso: string) =>
  new Date(iso).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })

// ---- មុខម្ហូបលក់ដាច់ ----
const topFoods = computed(() => {
  const map = new Map<number, { name: string; image: string; qty: number; revenue: number }>()
  for (const order of orders.value) {
    if (order.status === 'cancelled') continue
    for (const item of order.items) {
      const existing = map.get(item.food_id)
      if (existing) {
        existing.qty += item.quantity
        existing.revenue += Number(item.subtotal)
      } else {
        map.set(item.food_id, {
          name: item.food.name,
          image: item.food.image_url,
          qty: item.quantity,
          revenue: Number(item.subtotal),
        })
      }
    }
  }
  return Array.from(map.values())
    .sort((a, b) => b.qty - a.qty)
    .slice(0, 5)
})
const maxFoodQty = computed(() => Math.max(...topFoods.value.map((f) => f.qty), 1))
</script>

<template>
  <div class="min-h-screen bg-slate-50 p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-xl font-bold text-slate-800">ទិដ្ឋភាពទូទៅ</h1>
      <p class="text-sm text-slate-500">សង្ខេបសកម្មភាពហាងថ្ងៃនេះ</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center">
            <IconCash :size="18" class="text-orange-500" />
          </div>
          <span
            class="flex items-center gap-0.5 text-xs font-medium"
            :class="trendDirection === 'up' ? 'text-emerald-600' : 'text-rose-500'"
          >
            <component
              :is="trendDirection === 'up' ? IconTrendingUp : IconTrendingDown"
              :size="14"
            />
          </span>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ formatMoney(todayRevenue) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">ចំណូលថ្ងៃនេះ</p>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-2">
          <IconReceipt2 :size="18" class="text-blue-500" />
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ todayOrders.length }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Order ថ្ងៃនេះ</p>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-2">
          <IconClock :size="18" class="text-amber-500" />
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ pendingCount }}</p>
        <p class="text-xs text-slate-400 mt-0.5">កំពុងរង់ចាំ / ធ្វើ</p>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-4">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-2">
          <IconArmchair :size="18" class="text-emerald-500" />
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ occupiedTables }}</p>
        <p class="text-xs text-slate-400 mt-0.5">តុកំពុងប្រើប្រាស់</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Revenue Trend (2/3 width) -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-semibold text-slate-800 text-sm">ចំណូល ៧ថ្ងៃចុងក្រោយ</h2>
            <p class="text-xs text-slate-400">Revenue trend</p>
          </div>
        </div>
        <div class="flex items-end justify-between gap-2 h-40">
          <div
            v-for="day in last7Days"
            :key="day.label"
            class="flex-1 flex flex-col items-center gap-2 h-full justify-end"
          >
            <span class="text-xs text-slate-500 font-medium">{{
              day.total > 0 ? formatMoney(day.total) : ''
            }}</span>
            <div
              class="w-full rounded-t-md bg-gradient-to-t from-orange-500 to-orange-300 transition-all min-h-[4px]"
              :style="{ height: `${(day.total / maxDayTotal) * 100}%` }"
            ></div>
            <span class="text-xs text-slate-400">{{ day.label }}</span>
          </div>
        </div>
      </div>

      <!-- Top Foods -->
      <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center gap-2 mb-4">
          <IconFlame :size="16" class="text-orange-500" />
          <h2 class="font-semibold text-slate-800 text-sm">ម្ហូបលក់ដាច់</h2>
        </div>
        <div class="space-y-3">
          <div v-for="(food, i) in topFoods" :key="food.name" class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-300 w-4">{{ i + 1 }}</span>
            <img
              :src="food.image"
              :alt="food.name"
              class="w-8 h-8 rounded-lg object-cover shrink-0"
            />
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-700 truncate">{{ food.name }}</p>
              <div class="h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                <div
                  class="h-full bg-orange-400 rounded-full"
                  :style="{ width: `${(food.qty / maxFoodQty) * 100}%` }"
                ></div>
              </div>
            </div>
            <span class="text-xs text-slate-400 shrink-0">{{ food.qty }}</span>
          </div>
          <p v-if="topFoods.length === 0" class="text-xs text-slate-400 text-center py-4">
            មិនទាន់មានទិន្នន័យ
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 mt-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800 text-sm">Order ថ្មីៗ</h2>
        <RouterLink
          :to="{ name: 'order' }"
          class="text-xs text-orange-500 font-medium flex items-center gap-1 hover:gap-1.5 transition-all"
        >
          មើលទាំងអស់ <IconArrowRight :size="12" />
        </RouterLink>
      </div>
      <div class="divide-y divide-slate-100">
        <div
          v-for="order in recentOrders"
          :key="order.id"
          class="flex items-center justify-between py-3"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-slate-50 flex items-center justify-center">
              <IconArmchair :size="16" class="text-slate-400" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-700">តុ {{ order.table.table_number }}</p>
              <p class="text-xs text-slate-400">{{ formatTime(order.created_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span
              class="text-xs font-medium px-2 py-1 rounded-full"
              :class="[statusStyle[order.status].bg, statusStyle[order.status].text]"
            >
              {{ statusLabel[order.status] }}
            </span>
            <span class="text-sm font-bold text-slate-800 w-14 text-right">{{
              formatMoney(Number(order.total))
            }}</span>
          </div>
        </div>
        <p v-if="recentOrders.length === 0" class="text-xs text-slate-400 text-center py-8">
          មិនទាន់មាន order ទេ
        </p>
      </div>
    </div>
  </div>
</template>
