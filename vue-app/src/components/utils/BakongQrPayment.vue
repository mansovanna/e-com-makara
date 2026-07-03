<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<script setup lang="ts">
import { ref, computed, onBeforeUnmount, watch } from 'vue'
import QrcodeVue from 'qrcode.vue'
import { CloseIcon } from '@/stores/icon'
import { useShoppStore } from '@/stores/shopp_store'
import order_provider from '@/providers/order_provider'
import { useRoute, useRouter } from 'vue-router'

const card = useShoppStore()
const route = useRoute()
const router = useRouter()

const secondsLeft = ref(0)
const isPaid = ref(false)
const isExpired = ref(false)
const errorMsg = ref('')

// Logo configurations for the center of the ABA/Bakong QR
const logoSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
  <circle cx="12" cy="12" r="12" fill="white"/>
  <path fill="none" stroke="none" />
  <path fill="#f97316" d="M11.25 7.847c-.936.256-1.5.975-1.5 1.653s.564 1.397 1.5 1.652zm1.5 5.001v3.304c.936-.255 1.5-.974 1.5-1.652s-.564-1.397-1.5-1.652" />
  <path fill="#f97316" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M12 5.25a.75.75 0 0 1 .75.75v.317c1.63.292 3 1.517 3 3.183a.75.75 0 0 1-1.5 0c0-.678-.564-1.397-1.5-1.653v3.47c1.63.292 3 1.517 3 3.183s-1.37 2.891-3 3.183V18a.75.75 0 0 1-1.5 0v-.317c-1.63-.292-3-1.517-3-3.183a.75.75 0 0 1 1.5 0c0 .678.564 1.397 1.5 1.652v-3.469c-1.63-.292-3-1.517-3-3.183s1.37-2.891 3-3.183V6a.75.75 0 0 1 .75-.75" clip-rule="evenodd" />
</svg>`
const logoDataUri = computed(() => `data:image/svg+xml,${encodeURIComponent(logoSvg)}`)

let pollTimer: number | undefined
let countdownTimer: number | undefined
let autoCloseTimer: number | undefined

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

// Safely parsing items passed via url parameters
const checkoutItems = computed<CheckoutItem[]>(() => {
  const raw = route.query.item
  if (!raw) return []
  try {
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

// Keep an eye out for newly generated QR links
watch(
  () => card.qr_code,
  (qrCode) => {
    stopTimers()
    clearTimeout(autoCloseTimer)
    isPaid.value = false
    isExpired.value = false
    errorMsg.value = ''
    secondsLeft.value = 0

    if (!qrCode) return

    startCountdown(qrCode.expires_at)
    startPolling(qrCode.md5)
  },
  { immediate: true },
)

function startCountdown(expiresAtStr: string | undefined) {
  if (!expiresAtStr) {
    console.warn('QR expires_at field is missing')
    isExpired.value = true
    return
  }

  const timestamp = /^\d+$/.test(expiresAtStr) ? Number(expiresAtStr) : expiresAtStr
  const expiresAt = new Date(timestamp)

  if (isNaN(expiresAt.getTime())) {
    console.warn('Invalid expires_at date:', expiresAtStr)
    isExpired.value = true
    return
  }

  if (countdownTimer) clearInterval(countdownTimer)
  countdownTimer = window.setInterval(() => {
    const diff = Math.floor((expiresAt.getTime() - Date.now()) / 1000)
    secondsLeft.value = Math.max(diff, 0)

    if (diff <= 0) {
      isExpired.value = true
      stopTimers()
      scheduleAutoClose(3000)
    }
  }, 1000)
}

function startPolling(md5: string) {
  if (pollTimer) clearInterval(pollTimer)

  pollTimer = window.setInterval(async () => {
    // Escape early if payment state changed during active cycles
    if (isPaid.value || isExpired.value) {
      stopTimers()
      return
    }

    try {
      const { data } = await order_provider.verifyTransaction(md5)

      if (data.paid) {
        // 1. Instantly halt all execution loops to block double order triggers
        stopTimers()
        isPaid.value = true

        // 2. Transmit the order details payload securely
        const res = await card.createOrder({
          table_id: card.formData.table_id,
          note: card.formData.note,
          payment_method: card.paymentMethod,
          items: checkoutItems.value.map((item) => {
            const price = Number(item.discount_price ?? item.price)
            return {
              food_id: item.id,
              quantity: Number(item.quantity),
              price: price,
              subtotal: Number(price * item.quantity),
            }
          }),
          subtotal: Number(subtotal.value),
          delivery_fee: Number(deliveryFee),
          total: Number(total.value),
        })

        // 3. Initiate post-purchase cleanup delay window
        scheduleAutoClose(2000)
        if (res.status == 200 || res.status == 201) {
          close()
          router.push({ name: 'notification' })
        }
      }
    } catch (e) {
      console.error('Polling error, retrying...', e)
    }
  }, 3000)
}

function scheduleAutoClose(delayMs: number) {
  if (autoCloseTimer) clearTimeout(autoCloseTimer)
  autoCloseTimer = window.setTimeout(() => {
    close()
  }, delayMs)
}

function stopTimers() {
  clearInterval(pollTimer)
  clearInterval(countdownTimer)
  pollTimer = undefined
  countdownTimer = undefined
}

function close() {
  stopTimers()
  clearTimeout(autoCloseTimer)
  autoCloseTimer = undefined
  card.qr_code = null
}

const formattedTime = computed(() => {
  const m = Math.floor(secondsLeft.value / 60)
  const s = secondsLeft.value % 60
  return `${m}:${s.toString().padStart(2, '0')}`
})

onBeforeUnmount(() => {
  stopTimers()
  clearTimeout(autoCloseTimer)
})
</script>

<template>
  <div
    v-if="card.qr_code"
    class="fixed top-0 bottom-0 right-0 left-0 bg-black/40 z-50 flex justify-center items-center p-4"
  >
    <div
      class="w-80 max-md:w-full p-4 bg-white border border-orange-500 rounded-md flex flex-col justify-center items-center gap-2 relative"
    >
      <button
        @click="close"
        class="absolute -top-3 -right-2 p-2 bg-red-500 hover:bg-red-400 cursor-pointer rounded-full text-white"
      >
        <component :is="CloseIcon" />
      </button>

      <h1 class="text-lg font-bold">
        {{ card.qr_code.amount.toLocaleString() }}
        {{ card.qr_code.currency === 'KHR' ? '៛' : '$' }}
      </h1>

      <!-- Paid Status Display -->
      <div
        v-if="isPaid"
        class="w-full h-65 flex flex-col justify-center items-center gap-2 bg-green-50 rounded"
      >
        <p class="text-green-600 text-xl font-bold">✅ បង់ប្រាក់ជោគជ័យ!</p>
      </div>

      <!-- Expired Status Display -->
      <div
        v-else-if="isExpired"
        class="w-full h-65 flex flex-col justify-center items-center gap-3 bg-red-50 rounded"
      >
        <p class="text-red-500 font-medium">⏰ QR ផុតកំណត់ហើយ</p>
      </div>

      <!-- Live Dynamic QR Container -->
      <div v-else class="w-full h-65 flex justify-center items-center bg-white">
        <QrcodeVue
          :value="card.qr_code.qr"
          :size="220"
          level="H"
          render-as="svg"
          :image-settings="{
            src: logoDataUri,
            width: 36,
            height: 36,
            // boxId: 'aba-logo',
            excavate: true,
          }"
        />
      </div>

      <p v-if="errorMsg" class="text-red-500 text-sm">{{ errorMsg }}</p>

      <!-- Active Checkout Countdown Banner -->
      <div
        v-if="!isPaid && !isExpired"
        class="p-2 bg-slate-200 flex justify-center items-center gap-1 w-full rounded"
      >
        <p>Expires at:</p>
        <span class="text-red-500 text-xl font-black">{{ formattedTime }}</span>
      </div>
    </div>
  </div>
</template>
