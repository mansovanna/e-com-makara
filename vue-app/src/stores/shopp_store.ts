/* eslint-disable @typescript-eslint/no-explicit-any */
import type { FoodModelResponseAPI } from '@/models/food_model'
import order_provider from '@/providers/order_provider'
// import router from '@/router'

import { defineStore } from 'pinia'
// oxlint-disable-next-line no-unused-vars
import { nextTick, reactive, ref } from 'vue'

interface CartProduct {
  id: number
  quantity: number
  price: number
  selected?: boolean
  [key: string]: any
}

// ✅ FIX: generic function
function loadFromStorage<T>(key: string): T[] {
  try {
    const raw = localStorage.getItem(key)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

// oxlint-disable-next-line no-unused-vars
interface OrderPayload {
  table_id: number | null // ពី cart.formData.table_id
  note: string // ពី form.value.note
  payment_method: 'cash' | 'payway'
  items: {
    product_id: number
    quantity: number
    price: number // snapshot តម្លៃពេលបញ្ជាទិញ (កុំពឹង product.price នាពេលក្រោយ)
  }[]
  subtotal: number
  delivery_fee: number
  total: number
}

export const useShoppStore = defineStore('shopp', {
  state: () => ({
    data: null as FoodModelResponseAPI | null,
    isLoading: false,
    search: '',
    products: loadFromStorage<CartProduct>('cart') || null,
    favoriteProducts: loadFromStorage<any>('favorite'),
    recepts: loadFromStorage<any>('recepts'),
    showCategoryDropdown: false,
    selectedCategoryName: '',
    errors: {
      table_id: '',
      items: '',
      submit: '',
    },
    paymentMethod: 'cash' as 'cash' | 'payway' | null,
    formData: {
      table_id: null as number | null,
      note: null as string | null,
    },
    tables: null as any,
    qr_code: null as any | null,
  }),

  getters: {
    subtotal(): number {
      return this.products
        .filter((item) => item.selected)
        .reduce((sum, item) => {
          return sum + item.price * item.quantity
        }, 0)
    },

    deliveryFee(): number {
      const hasSelected = this.products.some((item) => item.selected)

      return hasSelected ? 0 : 0
    },

    total(): number {
      return this.subtotal + this.deliveryFee
    },
  },

  actions: {
    // stores/cart.ts
    toggleSelect(id: number) {
      const item = this.products.find((i) => i.id === id)
      if (item) item.selected = !item.selected
    },
    selectAll(value: boolean) {
      this.products.forEach((i) => (i.selected = value))
    },
    saveCart() {
      localStorage.setItem('cart', JSON.stringify(this.products))
    },

    addToCart(product: any, quantity: number = 1) {
      const existingProduct = this.products.find((item) => item.id === product.id)

      if (existingProduct) {
        existingProduct.quantity += quantity
      } else {
        this.products.push({
          ...product,
          quantity,
          selected: true,
        })
      }

      this.saveCart()
    },

    incrementProductQuantity(productId: number) {
      const product = this.products.find((p) => p.id === productId)

      if (product) {
        product.quantity += 1
        this.saveCart()
      }
    },

    decrementProductQuantity(productId: number) {
      const product = this.products.find((p) => p.id === productId)

      if (product && product.quantity > 1) {
        product.quantity -= 1
        this.saveCart()
      }
    },

    removeFromCart(productId: number) {
      this.products = this.products.filter((p) => p.id !== productId)

      this.saveCart()
    },

    clearCart() {
      this.products = []
      localStorage.removeItem('cart')
    },

    toggleFavorite(product: any) {
      const index = this.favoriteProducts.findIndex((item) => item.id === product.id)

      if (index === -1) {
        this.favoriteProducts.push(product)
      } else {
        this.favoriteProducts.splice(index, 1)
      }

      localStorage.setItem('favorite', JSON.stringify(this.favoriteProducts))
    },

    // Block order payemtn
    async placeOrder(_payload: any) {
      return await order_provider.createOrder(_payload)
    },

    async createOrder(_payload: any) {
      try {
        const res = await order_provider.createOrder(_payload)
        // console.log('createOrder success:', res)

        if (res.status === 200 || res.status === 201) {
          const orderedIds = res.data.order.items.map((i: any) => i.food_id)

          // remove cart items
          this.products = this.products.filter((item: any) => !orderedIds.includes(item.id))

          localStorage.setItem('cart', JSON.stringify(this.products))

          this.recepts.push(res.data)

          localStorage.setItem('recepts', JSON.stringify(this.recepts))
        }
        return res
      } catch (error: any) {
        console.log('createOrder error:', error?.response?.data || error)
        throw error
      }
    },
  },
})
