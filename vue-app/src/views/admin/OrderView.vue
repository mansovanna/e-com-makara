<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<script setup lang="ts">
import { useOrderStore } from '@/stores/order_store'
import { computed, onMounted, ref } from 'vue'
import {
  IconReceipt2,
  IconClock,
  IconChefHat,
  IconCircleCheck,
  IconCircleX,
  IconArmchair,
  IconCreditCard,
  IconQrcode,
  IconCash,
  IconChevronRight,
  IconX,
  IconLoader2,
  IconArrowRight,
} from '@tabler/icons-vue'

interface OrderFood {
  id: number
  name: string
  image: string
  image_url: string
}

interface OrderItem {
  id: number
  food_id: number
  quantity: number
  price: string | number
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
  table_id: number
  note: string | null
  status: OrderStatus
  payment_method: 'payway' | 'khqr' | 'cash'
  subtotal: string | number
  discount: string | number
  total: string | number
  created_at: string
  items: OrderItem[]
  table: OrderTable
}

const orderStore = useOrderStore()
const activeFilter = ref<'all' | OrderStatus>('all')

onMounted(() => {
  orderStore.getList()
})

const orders = computed<Order[]>(() => orderStore.data ?? [])

const filteredOrders = computed(() => {
  if (activeFilter.value === 'all') return orders.value
  return orders.value.filter((o) => o.status === activeFilter.value)
})

const filterTabs = [
  { key: 'all', label: 'ទាំងអស់' },
  { key: 'pending', label: 'កំពុងរង់ចាំ' },
  { key: 'preparing', label: 'កំពុងធ្វើ' },
  { key: 'completed', label: 'រួចរាល់' },
  { key: 'cancelled', label: 'បានលុបចោល' },
] as const

const countByStatus = (key: string) => {
  if (key === 'all') return orders.value.length
  return orders.value.filter((o) => o.status === key).length
}

const statusStyle: Record<OrderStatus, { bg: string; text: string; icon: any; label: string }> = {
  pending: {
    bg: 'bg-amber-50 border-amber-200',
    text: 'text-amber-700',
    icon: IconClock,
    label: 'កំពុងរង់ចាំ',
  },
  preparing: {
    bg: 'bg-blue-50 border-blue-200',
    text: 'text-blue-700',
    icon: IconChefHat,
    label: 'កំពុងធ្វើ',
  },
  completed: {
    bg: 'bg-emerald-50 border-emerald-200',
    text: 'text-emerald-700',
    icon: IconCircleCheck,
    label: 'រួចរាល់',
  },
  cancelled: {
    bg: 'bg-rose-50 border-rose-200',
    text: 'text-rose-700',
    icon: IconCircleX,
    label: 'បានលុបចោល',
  },
}

// ការផ្លាស់ប្តូរ status ត្រូវអនុញ្ញាតតាមលំដាប់ (workflow)
const nextStatusMap: Partial<Record<OrderStatus, { key: OrderStatus; label: string }>> = {
  pending: { key: 'preparing', label: 'ចាប់ផ្តើមចម្អិន' },
  preparing: { key: 'completed', label: 'សម្គាល់ថារួចរាល់' },
}

const allStatuses: { key: OrderStatus; label: string }[] = [
  { key: 'pending', label: 'កំពុងរង់ចាំ' },
  { key: 'preparing', label: 'កំពុងធ្វើ' },
  { key: 'completed', label: 'រួចរាល់' },
  { key: 'cancelled', label: 'បានលុបចោល' },
]

const paymentIcon = (method: Order['payment_method']) => {
  if (method === 'payway') return IconCreditCard
  if (method === 'khqr') return IconQrcode
  return IconCash
}

const formatMoney = (val: string | number) => `$${Number(val).toFixed(2)}`

const formatTime = (iso: string) => {
  const d = new Date(iso)
  return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

// ---- Status update state ----
const selectedOrder = ref<Order | null>(null)
const updatingId = ref<number | null>(null)
const updateError = ref<string | null>(null)

const openOrder = (order: Order) => {
  selectedOrder.value = order
  updateError.value = null
}

const closeModal = () => {
  selectedOrder.value = null
  updateError.value = null
}

const updateOrderStatus = async (order: Order, newStatus: OrderStatus) => {
  if (order.status === newStatus) return
  updatingId.value = order.id
  updateError.value = null
  try {
    // សន្មតថា store មាន method នេះ — ប្តូរឈ្មោះបើ store ខុសពីនេះ
    await orderStore.updateStatus(order.id, newStatus)
    order.status = newStatus
    if (selectedOrder.value?.id === order.id) {
      selectedOrder.value.status = newStatus
    }
  } catch (err: any) {
    updateError.value = err?.response?.data?.message ?? 'មិនអាចប្តូរ status បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingId.value = null
  }
}

const quickAdvance = (order: Order, e: Event) => {
  e.stopPropagation()
  const next = nextStatusMap[order.status]
  if (next) updateOrderStatus(order, next.key)
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 p-6">
    <!-- Header -->
    <div class="flex flex-col gap-1 mb-6">
      <div class="flex items-center gap-2">
        <div class="w-9 h-9 rounded-lg bg-orange-500 flex items-center justify-center shrink-0">
          <IconReceipt2 :size="20" class="text-white" stroke-width="2" />
        </div>
        <h1 class="text-xl font-bold text-slate-800">ការគ្រប់គ្រងការបញ្ជាទិញ</h1>
      </div>
      <p class="text-sm text-slate-500 pl-11">តាមដាន និងគ្រប់គ្រង order ទាំងអស់ពីតុភ្ញៀវ</p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button
        v-for="tab in filterTabs"
        :key="tab.key"
        @click="activeFilter = tab.key"
        class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium border transition-all"
        :class="
          activeFilter === tab.key
            ? 'bg-orange-500 border-orange-500 text-white shadow-sm shadow-orange-200'
            : 'bg-white border-slate-200 text-slate-600 hover:border-orange-300'
        "
      >
        {{ tab.label }}
        <span
          class="text-xs px-1.5 py-0.5 rounded-full"
          :class="activeFilter === tab.key ? 'bg-white/20' : 'bg-slate-100'"
        >
          {{ countByStatus(tab.key) }}
        </span>
      </button>
    </div>

    <!-- Empty state -->
    <div
      v-if="filteredOrders.length === 0"
      class="flex flex-col items-center justify-center py-24 text-slate-400"
    >
      <IconReceipt2 :size="48" stroke-width="1.5" />
      <p class="mt-3 text-sm">មិនមាន order ក្នុងប្រភេទនេះទេ</p>
    </div>

    <!-- Order Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        @click="openOrder(order)"
        class="bg-white rounded-xl border border-slate-200 hover:-translate-y-0.5 transition-all cursor-pointer overflow-hidden"
      >
        <!-- Ticket header -->
        <div class="px-4 pt-4 pb-3 flex items-start justify-between">
          <div>
            <p class="text-xs text-slate-400 font-mono">{{ order.order_no }}</p>
            <div class="flex items-center gap-1.5 mt-1">
              <IconArmchair :size="16" class="text-orange-500" />
              <span class="font-semibold text-slate-800 text-sm"
                >តុ {{ order.table.table_number }}</span
              >
            </div>
          </div>
          <div
            class="flex items-center gap-1 px-2 py-1 rounded-full border text-xs font-medium"
            :class="[statusStyle[order.status].bg, statusStyle[order.status].text]"
          >
            <component :is="statusStyle[order.status].icon" :size="13" stroke-width="2.2" />
            {{ statusStyle[order.status].label }}
          </div>
        </div>

        <!-- Perforated divider -->
        <div class="relative px-4">
          <div class="border-t border-dashed border-slate-200"></div>
          <div class="absolute -left-2 -top-2 w-4 h-4 rounded-full bg-slate-50"></div>
          <div class="absolute -right-2 -top-2 w-4 h-4 rounded-full bg-slate-50"></div>
        </div>

        <!-- Items -->
        <div class="px-4 py-3 space-y-2 max-h-32 overflow-y-auto">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="flex items-center justify-between text-sm"
          >
            <span class="text-slate-600 truncate pr-2">
              <span class="text-orange-500 font-semibold">{{ item.quantity }}×</span>
              {{ item.food.name }}
            </span>
            <span class="text-slate-500 shrink-0">{{ formatMoney(item.subtotal) }}</span>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-slate-50 flex items-center justify-between gap-2">
          <div class="flex items-center gap-1.5 text-slate-400 text-xs shrink-0">
            <component :is="paymentIcon(order.payment_method)" :size="14" />
            <span>{{ formatTime(order.created_at) }}</span>
          </div>

          <!-- Quick advance button -->
          <button
            v-if="nextStatusMap[order.status]"
            @click="quickAdvance(order, $event)"
            :disabled="updatingId === order.id"
            class="flex items-center gap-1 text-xs font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 px-2 py-1 rounded-md transition-colors disabled:opacity-50"
          >
            <IconLoader2 v-if="updatingId === order.id" :size="12" class="animate-spin" />
            <template v-else>
              {{ nextStatusMap[order.status]!.label }}
              <IconArrowRight :size="12" />
            </template>
          </button>

          <span v-else class="font-bold text-slate-800">{{ formatMoney(order.total) }}</span>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <Teleport to="body">
      <div
        v-if="selectedOrder"
        @click.self="closeModal"
        class="fixed inset-0 bg-slate-900/40 flex items-center justify-center p-4 z-50"
      >
        <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-xl">
          <!-- Modal header -->
          <div class="bg-orange-500 px-5 py-4 flex items-start justify-between">
            <div>
              <p class="text-orange-100 text-xs font-mono">{{ selectedOrder.order_no }}</p>
              <div class="flex items-center gap-1.5 mt-1 text-white">
                <IconArmchair :size="18" />
                <span class="font-semibold">តុ {{ selectedOrder.table.table_number }}</span>
              </div>
            </div>
            <button @click="closeModal" class="text-white/80 hover:text-white">
              <IconX :size="20" />
            </button>
          </div>

          <!-- Items -->
          <div
            class="px-5 py-4 space-y-2 max-h-64 overflow-y-auto border-b border-dashed border-slate-200"
          >
            <div
              v-for="item in selectedOrder.items"
              :key="item.id"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-slate-600">
                <span class="text-orange-500 font-semibold">{{ item.quantity }}×</span>
                {{ item.food.name }}
              </span>
              <span class="text-slate-700 font-medium">{{ formatMoney(item.subtotal) }}</span>
            </div>
            <div v-if="selectedOrder.note" class="text-xs text-slate-400 pt-2 italic">
              ចំណាំ: {{ selectedOrder.note }}
            </div>
          </div>

          <!-- Total -->
          <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100">
            <span class="text-sm text-slate-500">សរុប</span>
            <span class="text-lg font-bold text-slate-800">{{
              formatMoney(selectedOrder.total)
            }}</span>
          </div>

          <!-- Status update -->
          <div class="px-5 py-4">
            <p class="text-xs font-medium text-slate-400 mb-2">ប្តូរ status</p>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="s in allStatuses"
                :key="s.key"
                @click="updateOrderStatus(selectedOrder!, s.key)"
                :disabled="updatingId === selectedOrder.id || selectedOrder.status === s.key"
                class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium border transition-all disabled:cursor-not-allowed"
                :class="
                  selectedOrder.status === s.key
                    ? [
                        statusStyle[s.key].bg,
                        statusStyle[s.key].text,
                        'ring-1 ring-inset',
                        'ring-current',
                      ]
                    : 'bg-white border-slate-200 text-slate-500 hover:border-orange-300'
                "
              >
                <component :is="statusStyle[s.key].icon" :size="14" />
                {{ s.label }}
              </button>
            </div>

            <div
              v-if="updatingId === selectedOrder.id"
              class="flex items-center gap-2 text-xs text-slate-400 mt-3"
            >
              <IconLoader2 :size="14" class="animate-spin" />
              កំពុងរក្សាទុក...
            </div>
            <div v-if="updateError" class="text-xs text-rose-500 mt-3">
              {{ updateError }}
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
