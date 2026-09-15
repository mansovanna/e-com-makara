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
  IconWallet,
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
  paid_amount?: string | number | null
  change_amount?: string | number | null
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

const formatMoney = (val: string | number | null | undefined) => `$${Number(val ?? 0).toFixed(2)}`

// ចំនួនទឹកប្រាក់ពិតប្រាកដដែលអតិថិជនត្រូវបង់ = សរុប subtotal នៃ item ដែលមិនត្រូវបាន cancel
// (order.total ដែលមកពី backend មិនកាត់ចេញ item ដែល cancel ក្រោយពេលបង្កើត order ទេ)
const payableTotal = (order: Order) =>
  order.items
    .filter((i) => i.status !== 'cancelled')
    .reduce((sum, i) => sum + Number(i.subtotal), 0)

const cancelledAmount = (order: Order) =>
  order.items
    .filter((i) => i.status === 'cancelled')
    .reduce((sum, i) => sum + Number(i.subtotal), 0)

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

// ប្រើសម្រាប់ត្រឡប់ paid -> unpaid ឬបញ្ជាក់ payway/khqr ដោយផ្ទាល់ (គ្មានលុយអាប)
const togglePaymentStatus = async (
  order: Order,
  extra?: { paid_amount?: number; change_amount?: number },
) => {
  const newStatus: PaymentStatus = order.payment_status === 'unpaid' ? 'paid' : 'unpaid'
  updatingPayment.value = order.id
  updateError.value = null

  const formData = new FormData()
  formData.append('status', newStatus)
  formData.append('paid_amount', extra?.paid_amount?.toString() ?? '')
  formData.append('change_amount', extra?.change_amount?.toString() ?? '')

  try {
    await orderStore.updatePaymentStatus(order.id, formData)
    order.payment_status = newStatus
    if (newStatus === 'paid' && extra) {
      order.paid_amount = extra.paid_amount ?? null
      order.change_amount = extra.change_amount ?? null
    }
    if (newStatus === 'unpaid') {
      order.paid_amount = null
      order.change_amount = null
    }
  } catch (err: any) {
    updateError.value =
      err?.response?.data?.message ?? 'មិនអាចប្តូរការទូទាត់បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingPayment.value = null
  }
}

// ---- Cash payment confirm modal (amount received / change) ----
const cashOrder = ref<Order | null>(null)
const receivedInput = ref<string>('')

// ប្រើ payableTotal (មិនរួមបញ្ចូល item cancel) ជំនួសឲ្យ order.total ដោយផ្ទាល់
const cashTotal = computed(() => (cashOrder.value ? payableTotal(cashOrder.value) : 0))

const changeAmount = computed(() => {
  const received = Number(receivedInput.value)
  if (!receivedInput.value || isNaN(received)) return null
  return Math.round((received - cashTotal.value) * 100) / 100
})

const canConfirmCash = computed(() => {
  const received = Number(receivedInput.value)
  return receivedInput.value !== '' && !isNaN(received) && received >= cashTotal.value
})

// បង្កើតជម្រើសលុយឆាប់ៗ (exact + round ឡើងលើ) ដើម្បីចុចលឿន មិនចាំបាច់វាយចំនួនតែងតែ
const quickAmounts = computed(() => {
  const total = cashTotal.value
  if (!total) return []
  const set = new Set<number>()
  set.add(Math.round(total * 100) / 100) // ចំនួនពិតប្រាកដ
  ;[5, 10, 20, 50, 100].forEach((step) => {
    const rounded = Math.ceil(total / step) * step
    if (rounded > total) set.add(rounded)
  })
  return Array.from(set)
    .sort((a, b) => a - b)
    .slice(0, 5)
})

const openCashConfirm = (order: Order, e?: Event) => {
  e?.stopPropagation()
  cashOrder.value = order
  receivedInput.value = ''
  updateError.value = null
}

const closeCashConfirm = () => {
  cashOrder.value = null
  receivedInput.value = ''
}

const confirmCashPayment = async () => {
  if (!cashOrder.value || !canConfirmCash.value) return
  const order = cashOrder.value
  const received = Number(receivedInput.value)
  // ប្រើ cashTotal (payableTotal ដែលកាត់ចេញ item cancel) មិនមែន order.total ទេ
  const change = Math.round((received - cashTotal.value) * 100) / 100

  updatingPayment.value = order.id
  updateError.value = null

  const formData = new FormData()
  formData.append('status', 'paid')
  formData.append('paid_amount', received.toString())
  formData.append('change_amount', change.toString())

  try {
    await orderStore.updatePaymentStatus(order.id, formData)
    order.payment_status = 'paid'
    order.paid_amount = received
    order.change_amount = change
    closeCashConfirm()
  } catch (err: any) {
    updateError.value =
      err?.response?.data?.message ?? 'មិនអាចបញ្ជាក់ការទូទាត់បានទេ សូមព្យាយាមម្តងទៀត'
  } finally {
    updatingPayment.value = null
  }
}

// ចំណុចចូល unified: ប៊ូតុង "សម្គាល់ថាទូទាត់រួច" លើ card និង modal ហៅ function នេះ
// - unpaid + cash -> បើក form បញ្ចូលលុយទទួលបាន
// - unpaid + payway/khqr -> បញ្ជាក់ភ្លាមៗ (ចំនួនច្បាស់លាស់ស្រាប់ គ្មានលុយអាប)
// - paid -> ត្រឡប់ទៅ unpaid វិញ (គ្មានត្រូវការ form)
const handlePaymentClick = (order: Order, e?: Event) => {
  e?.stopPropagation()
  if (order.payment_status === 'paid') {
    togglePaymentStatus(order)
    return
  }
  if (order.payment_method === 'cash') {
    openCashConfirm(order, e)
  } else {
    togglePaymentStatus(order)
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
              @click="handlePaymentClick(order, $event)"
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

          <span v-else class="font-bold text-slate-800">{{ formatMoney(payableTotal(order)) }}</span>
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
              @click="handlePaymentClick(selectedOrder!)"
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
          <div class="px-5 py-3">
            <div v-if="cancelledAmount(selectedOrder) > 0" class="flex items-center justify-between text-xs text-slate-400 mb-1">
              <span>តម្លៃដើម (រួមទំនិញលុបចោល)</span>
              <span class="line-through">{{ formatMoney(selectedOrder.total) }}</span>
            </div>
            <div v-if="cancelledAmount(selectedOrder) > 0" class="flex items-center justify-between text-xs text-rose-500 mb-1">
              <span>កាត់ចេញ (ទំនិញលុបចោល)</span>
              <span>−{{ formatMoney(cancelledAmount(selectedOrder)) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-slate-500">ត្រូវបង់ជាក់ស្តែង</span>
              <span class="text-lg font-bold text-slate-800">{{
                formatMoney(payableTotal(selectedOrder))
              }}</span>
            </div>

            <!-- បង្ហាញព័ត៌មានលុយប្រសិនបើបានទូទាត់ជាសាច់ប្រាក់ -->
            <div
              v-if="selectedOrder.payment_status === 'paid' && selectedOrder.payment_method === 'cash' && selectedOrder.paid_amount != null"
              class="mt-2 pt-2 border-t border-dashed border-slate-200 space-y-1"
            >
              <div class="flex items-center justify-between text-xs text-slate-500">
                <span>ទទួលបាន</span>
                <span>{{ formatMoney(selectedOrder.paid_amount) }}</span>
              </div>
              <div class="flex items-center justify-between text-xs text-slate-500">
                <span>លុយអាប</span>
                <span class="font-semibold text-orange-600">{{
                  formatMoney(selectedOrder.change_amount)
                }}</span>
              </div>
            </div>
          </div>

          <div v-if="updateError" class="px-5 pb-4 text-xs text-rose-500">
            {{ updateError }}
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Cash Payment Confirm Modal: បញ្ចូលលុយទទួលបាន + គណនាលុយអាប -->
    <Teleport to="body">
      <div
        v-if="cashOrder"
        @click.self="closeCashConfirm"
        class="fixed inset-0 bg-slate-900/40 flex items-center justify-center p-4 z-[60] font-hanuman"
      >
        <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-xl">
          <!-- Header -->
          <div class="bg-orange-500 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2 text-white">
              <IconWallet :size="20" />
              <span class="font-semibold">បញ្ជាក់ការទូទាត់ជាសាច់ប្រាក់</span>
            </div>
            <button @click="closeCashConfirm" class="text-white/80 hover:text-white">
              <IconX :size="20" />
            </button>
          </div>

          <div class="px-5 py-4 space-y-4">
            <!-- Order summary -->
            <div class="flex items-center justify-between text-sm">
              <span class="text-slate-500">តុ {{ cashOrder.table.table_number }} · {{ cashOrder.order_no }}</span>
            </div>
            <div v-if="cancelledAmount(cashOrder) > 0" class="flex items-center justify-between text-xs text-rose-500">
              <span>មិនគិតលុយទំនិញលុបចោល</span>
              <span>−{{ formatMoney(cancelledAmount(cashOrder)) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-slate-600">ត្រូវបង់សរុប</span>
              <span class="text-xl font-bold text-slate-800">{{ formatMoney(cashTotal) }}</span>
            </div>

            <!-- Quick amount buttons -->
            <div v-if="quickAmounts.length" class="flex flex-wrap gap-2">
              <button
                v-for="amt in quickAmounts"
                :key="amt"
                type="button"
                @click="receivedInput = String(amt)"
                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors"
                :class="
                  Number(receivedInput) === amt
                    ? 'bg-orange-500 border-orange-500 text-white'
                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-orange-300'
                "
              >
                {{ formatMoney(amt) }}
              </button>
            </div>

            <!-- Amount received input -->
            <div>
              <label class="text-sm text-slate-600 mb-1 block">លុយដែលទទួលបានពីភ្ញៀវ</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                <input
                  v-model="receivedInput"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  class="w-full pl-7 p-2.5 text-lg rounded-md border border-slate-200 focus:outline-orange-500 focus:ring-2 focus:ring-orange-200"
                  @keyup.enter="canConfirmCash && confirmCashPayment()"
                />
              </div>
            </div>

            <!-- Change display -->
            <div
              class="rounded-lg px-4 py-3 flex items-center justify-between"
              :class="
                changeAmount === null
                  ? 'bg-slate-50 text-slate-400'
                  : changeAmount < 0
                    ? 'bg-rose-50 text-rose-600'
                    : 'bg-emerald-50 text-emerald-700'
              "
            >
              <span class="text-sm font-medium">
                {{ changeAmount !== null && changeAmount < 0 ? 'ខ្វះលុយ' : 'លុយអាបត្រូវដាក់ជូនភ្ញៀវ' }}
              </span>
              <span class="text-lg font-bold">
                {{ changeAmount === null ? '—' : formatMoney(Math.abs(changeAmount)) }}
              </span>
            </div>

            <p v-if="updateError" class="text-xs text-rose-500">{{ updateError }}</p>

            <!-- Actions -->
            <div class="flex gap-2 pt-1">
              <button
                type="button"
                @click="closeCashConfirm"
                class="flex-1 py-2.5 rounded-md text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200"
              >
                បោះបង់
              </button>
              <button
                type="button"
                :disabled="!canConfirmCash || updatingPayment === cashOrder.id"
                @click="confirmCashPayment"
                class="flex-1 py-2.5 rounded-md text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 disabled:opacity-50 flex items-center justify-center gap-1"
              >
                <IconLoader2
                  v-if="updatingPayment === cashOrder.id"
                  :size="14"
                  class="animate-spin"
                />
                <template v-else>បញ្ជាក់ការទូទាត់</template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
