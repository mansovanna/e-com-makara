<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<script setup lang="ts">
import { useOrderStore } from '@/stores/order_store'
import { computed, onMounted, ref } from 'vue'
import {
  IconReceipt2,
  IconClock,
  IconChefHat,
  IconBellRinging,
  IconCircleCheck,
  IconCircleX,
  IconArmchair,
  IconCreditCard,
  IconQrcode,
  IconCash,
  IconX,
  IconLoader2,
  IconArrowRight,
} from '@tabler/icons-vue'

// ---- Types matching the real API / DB enum (order_items.status) ----
type ItemStatus = 'pending' | 'preparing' | 'ready' | 'served' | 'cancelled'
type PaymentStatus = 'unpaid' | 'paid'

interface OrderFood {
  id: number
  category_id: number
  name: string
  image: string
  price: number
  discount_price: number | null
  is_discount: number
  description: string
  status: string
  image_url: string
}

interface OrderItem {
  id: number
  order_id: number
  food_id: number
  status: ItemStatus
  quantity: number
  subtotal: string | number
  created_at: string
  updated_at: string
  food: OrderFood
}

interface OrderTable {
  id: number
  table_number: string
  status: string
}

interface Order {
  id: number
  order_no: string
  table_id: number
  note: string | null
  payment_status: PaymentStatus
  payment_method: 'payway' | 'khqr' | 'cash'
  total: string | number
  created_at: string
  updated_at: string
  items: OrderItem[]
  table: OrderTable
}

const orderStore = useOrderStore()
const activeFilter = ref<'all' | ItemStatus>('all')

onMounted(() => {
  orderStore.getList()
})

const orders = computed<Order[]>(() => orderStore.data ?? [])

// stage order for the active (non-cancelled) workflow
const stageRank: Record<'pending' | 'preparing' | 'ready' | 'served', number> = {
  pending: 0,
  preparing: 1,
  ready: 2,
  served: 3,
}
const stageOrder: ('pending' | 'preparing' | 'ready' | 'served')[] = [
  'pending',
  'preparing',
  'ready',
  'served',
]

// ---- Order's overall status is derived from its items:
// cancelled if every item is cancelled; served if every non-cancelled item
// is served; otherwise the earliest stage still in progress ----
const orderStatus = (order: Order): ItemStatus => {
  const active = order.items.filter((i) => i.status !== 'cancelled')
  if (active.length === 0) return 'cancelled'
  if (active.every((i) => i.status === 'served')) return 'served'

  const minStage = Math.min(
    ...active.map((i) => stageRank[i.status as keyof typeof stageRank] ?? Number.MAX_SAFE_INTEGER),
  )

  return stageOrder[minStage] ?? 'pending'
}

const filteredOrders = computed(() => {
  if (activeFilter.value === 'all') return orders.value
  return orders.value.filter((o) => orderStatus(o) === activeFilter.value)
})

const filterTabs = [
  { key: 'all', label: 'ទាំងអស់' },
  { key: 'pending', label: 'កំពុងរង់ចាំ' },
  { key: 'preparing', label: 'កំពុងចម្អិន' },
  { key: 'ready', label: 'រួចរាល់ រង់ចាំយក' },
  { key: 'served', label: 'ដល់ដៃភ្ញៀវ' },
  { key: 'cancelled', label: 'បានលុបចោល' },
] as const

const countByStatus = (key: string) => {
  if (key === 'all') return orders.value.length
  return orders.value.filter((o) => orderStatus(o) === key).length
}

const statusStyle: Record<ItemStatus, { bg: string; text: string; icon: any; label: string }> = {
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
    label: 'កំពុងចម្អិន',
  },
  ready: {
    bg: 'bg-violet-50 border-violet-200',
    text: 'text-violet-700',
    icon: IconBellRinging,
    label: 'រួចរាល់ រង់ចាំយក',
  },
  served: {
    bg: 'bg-emerald-50 border-emerald-200',
    text: 'text-emerald-700',
    icon: IconCircleCheck,
    label: 'ដល់ដៃភ្ញៀវ',
  },
  cancelled: {
    bg: 'bg-rose-50 border-rose-200',
    text: 'text-rose-700',
    icon: IconCircleX,
    label: 'បានលុបចោល',
  },
}

const paymentStatusLabel: Record<PaymentStatus, string> = {
  unpaid: 'មិនទាន់ទូទាត់',
  paid: 'ទូទាត់រួច',
}

// ការផ្លាស់ប្តូរ status ត្រូវអនុញ្ញាតតាមលំដាប់ (workflow) - អនុវត្តលើ item ទាំងអស់ក្នុង order ជាបាច់
const nextStatusMap: Partial<Record<ItemStatus, { key: ItemStatus; label: string }>> = {
  pending: { key: 'preparing', label: 'ចាប់ផ្តើមចម្អិន' },
  preparing: { key: 'ready', label: 'សម្គាល់ថារួចរាល់' },
  ready: { key: 'served', label: 'ដល់ដៃភ្ញៀវហើយ' },
}

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
const updatingId = ref<number | null>(null) // order-level (bulk quick-advance) spinner
const updatingItemId = ref<number | null>(null) // single item spinner (inside modal)
const updateError = ref<string | null>(null)

const openOrder = (order: Order) => {
  selectedOrder.value = order
  updateError.value = null
}

const closeModal = () => {
  selectedOrder.value = null
  updateError.value = null
}

// Advance a single item to the next stage in the workflow
const advanceItem = async (item: OrderItem) => {
  const next = nextStatusMap[item.status]
  if (!next) return
  updatingItemId.value = item.id
  updateError.value = null
  try {
    await orderStore.updateItemStatus(item.id, next.key)
    item.status = next.key
  } catch (err: any) {
    updateError.value = err?.response?.data?.message ?? 'មិនអាចប្តូរ status បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingItemId.value = null
  }
}

// Cancel a single item (terminal, not part of the linear workflow)
const cancelItem = async (item: OrderItem) => {
  if (item.status === 'cancelled' || item.status === 'served') return
  updatingItemId.value = item.id
  updateError.value = null
  try {
    await orderStore.updateItemStatus(item.id, 'cancelled')
    item.status = 'cancelled'
  } catch (err: any) {
    updateError.value = err?.response?.data?.message ?? 'មិនអាចប្តូរ status បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingItemId.value = null
  }
}

// ---- Payment update ----
const updatingPayment = ref<number | null>(null) // order id currently updating payment

const togglePaymentStatus = async (order: Order, e?: Event) => {
  e?.stopPropagation()
  const newStatus: PaymentStatus = order.payment_status === 'unpaid' ? 'paid' : 'unpaid'
  updatingPayment.value = order.id
  updateError.value = null
  try {
    // សន្មតថា store មាន method នេះ — ប្តូរឈ្មោះបើ store ខុសពីនេះ
    await orderStore.updatePaymentStatus(order.id, newStatus)
    order.payment_status = newStatus
  } catch (err: any) {
    updateError.value =
      err?.response?.data?.message ?? 'មិនអាចប្តូរការទូទាត់បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingPayment.value = null
  }
}

// Quick-advance from the card: bumps every item currently at the order's
// overall stage to the next stage in one go (e.g. all "pending" -> "preparing")
const quickAdvance = async (order: Order, e: Event) => {
  e.stopPropagation()
  const current = orderStatus(order)
  const next = nextStatusMap[current]
  if (!next) return

  const itemsToUpdate = order.items.filter((i) => i.status === current)
  updatingId.value = order.id
  updateError.value = null
  try {
    await Promise.all(itemsToUpdate.map((i) => orderStore.updateItemStatus(i.id, next.key)))
    itemsToUpdate.forEach((i) => (i.status = next.key))
  } catch (err: any) {
    updateError.value = err?.response?.data?.message ?? 'មិនអាចប្តូរ status បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingId.value = null
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 p-6 font-hanuman">
    <!-- Header -->
    <div class="flex flex-col gap-1 mb-6">
      <div class="flex items-center gap-2">
        <div class="w-9 h-9 rounded-lg bg-orange-500 flex items-center justify-center shrink-0">
          <IconReceipt2 :size="20" class="text-white" stroke-width="2" />
        </div>
        <h1 class="text-xl font-bold text-slate-800 font-hanuman">ការគ្រប់គ្រងការបញ្ជាទិញ</h1>
      </div>
      <p class="text-sm text-slate-500 pl-11 font-hanuman">
        តាមដាន និងគ្រប់គ្រង order ទាំងអស់ពីតុភ្ញៀវ
      </p>
    </div>

    <!-- Filter Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
      <button
        v-for="tab in filterTabs"
        :key="tab.key"
        @click="activeFilter = tab.key"
        class="flex items-center gap-2 px-4 font-hanuman py-2 rounded-full text-sm font-medium border transition-all"
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
      <p class="mt-3 text-sm font-hanuman">មិនមាន order ក្នុងប្រភេទនេះទេ</p>
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
            <p class="text-xs text-slate-400 font-poppins">{{ order.order_no }}</p>
            <div class="flex items-center gap-1.5 mt-1 font-hanuman">
              <IconArmchair :size="16" class="text-orange-500" />
              <span class="font-semibold text-slate-800 text-sm"
                >តុ {{ order.table.table_number }}</span
              >
            </div>
          </div>
          <div
            class="flex items-center gap-1 px-2 py-1 rounded-full border text-xs font-medium font-hanuman"
            :class="[statusStyle[orderStatus(order)].bg, statusStyle[orderStatus(order)].text]"
          >
            <component :is="statusStyle[orderStatus(order)].icon" :size="13" stroke-width="2.2" />
            {{ statusStyle[orderStatus(order)].label }}
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
            <span class="flex items-center gap-1.5 text-slate-600 truncate pr-2">
              <span
                class="w-1.5 h-1.5 rounded-full shrink-0"
                :class="statusStyle[item.status].text.replace('text-', 'bg-')"
              ></span>
              <span :class="item.status === 'cancelled' ? 'text-slate-400 line-through' : ''"
                ><span class="text-orange-500 font-semibold">{{ item.quantity }}×</span>
                {{ item.food.name }}</span
              >
            </span>
            <span class="text-slate-500 shrink-0">{{ formatMoney(item.subtotal) }}</span>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 bg-slate-50 flex items-center justify-between gap-2">
          <div class="flex flex-col gap-0.5 text-slate-400 text-xs shrink-0">
            <span class="flex items-center gap-1.5">
              <component :is="paymentIcon(order.payment_method)" :size="14" />
              {{ formatTime(order.created_at) }}
            </span>
            <button
              @click="togglePaymentStatus(order, $event)"
              :disabled="updatingPayment === order.id"
              class="text-left w-fit disabled:opacity-50"
              :class="
                order.payment_status === 'unpaid'
                  ? 'text-rose-400 hover:text-rose-500'
                  : 'text-emerald-500'
              "
            >
              <IconLoader2
                v-if="updatingPayment === order.id"
                :size="10"
                class="animate-spin inline"
              />
              <template v-else>{{ paymentStatusLabel[order.payment_status] }}</template>
            </button>
          </div>

          <!-- Quick advance button -->
          <button
            v-if="nextStatusMap[orderStatus(order)]"
            @click="quickAdvance(order, $event)"
            :disabled="updatingId === order.id"
            class="flex items-center gap-1 text-xs font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 px-2 py-1 rounded-md transition-colors disabled:opacity-50"
          >
            <IconLoader2 v-if="updatingId === order.id" :size="12" class="animate-spin" />
            <template v-else>
              {{ nextStatusMap[orderStatus(order)]!.label }}
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
        class="fixed inset-0 bg-slate-900/40 flex items-center justify-center p-4 z-50 font-hanuman"
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

          <!-- Items with per-item status control -->
          <div
            class="px-5 py-4 space-y-3 max-h-72 overflow-y-auto border-b border-dashed border-slate-200"
          >
            <div v-for="item in selectedOrder.items" :key="item.id" class="text-sm">
              <div class="flex items-center justify-between">
                <span
                  class="text-slate-700 font-medium"
                  :class="item.status === 'cancelled' ? 'line-through text-slate-400' : ''"
                >
                  <span class="text-orange-500 font-semibold">{{ item.quantity }}×</span>
                  {{ item.food.name }}
                </span>
                <span class="text-slate-500">{{ formatMoney(item.subtotal) }}</span>
              </div>

              <div class="flex items-center gap-1.5 mt-1.5">
                <span
                  class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium border"
                  :class="[statusStyle[item.status].bg, statusStyle[item.status].text]"
                >
                  <component :is="statusStyle[item.status].icon" :size="10" />
                  {{ statusStyle[item.status].label }}
                </span>

                <button
                  v-if="nextStatusMap[item.status]"
                  @click="advanceItem(item)"
                  :disabled="updatingItemId === item.id"
                  class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 disabled:opacity-50"
                >
                  <IconLoader2 v-if="updatingItemId === item.id" :size="10" class="animate-spin" />
                  <template v-else>{{ nextStatusMap[item.status]!.label }}</template>
                </button>

                <button
                  v-if="item.status !== 'cancelled' && item.status !== 'served'"
                  @click="cancelItem(item)"
                  :disabled="updatingItemId === item.id"
                  class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium text-rose-500 bg-rose-50 hover:bg-rose-100 disabled:opacity-50"
                >
                  <IconCircleX :size="10" />
                  លុបចោល
                </button>
              </div>
            </div>
            <div v-if="selectedOrder.note" class="text-xs text-slate-400 pt-2 italic">
              ចំណាំ: {{ selectedOrder.note }}
            </div>
          </div>

          <!-- Payment -->
          <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-2 text-sm text-slate-500">
              <component :is="paymentIcon(selectedOrder.payment_method)" :size="16" />
              <span
                :class="
                  selectedOrder.payment_status === 'unpaid' ? 'text-rose-500' : 'text-emerald-600'
                "
                >{{ paymentStatusLabel[selectedOrder.payment_status] }}</span
              >
            </div>
            <button
              @click="togglePaymentStatus(selectedOrder!)"
              :disabled="updatingPayment === selectedOrder.id"
              class="flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md transition-colors disabled:opacity-50"
              :class="
                selectedOrder.payment_status === 'unpaid'
                  ? 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100'
                  : 'text-slate-500 bg-slate-100 hover:bg-slate-200'
              "
            >
              <IconLoader2
                v-if="updatingPayment === selectedOrder.id"
                :size="12"
                class="animate-spin"
              />
              <template v-else>
                {{
                  selectedOrder.payment_status === 'unpaid'
                    ? 'សម្គាល់ថាទូទាត់រួច'
                    : 'ត្រឡប់ជាមិនទាន់ទូទាត់'
                }}
              </template>
            </button>
          </div>

          <!-- Total -->
          <div class="px-5 py-3 flex items-center justify-between">
            <span class="text-sm text-slate-500">សរុប</span>
            <span class="text-lg font-bold text-slate-800">{{
              formatMoney(selectedOrder.total)
            }}</span>
          </div>

          <div v-if="updateError" class="px-5 pb-4 text-xs text-rose-500">
            {{ updateError }}
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
