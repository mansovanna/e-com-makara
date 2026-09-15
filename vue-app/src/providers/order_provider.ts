/* eslint-disable @typescript-eslint/no-explicit-any */
import api from '@/config/api'

class OrderProvider {
  checkOut(data: any) {
    return api.post('/check-out', data)
  }

  verifyTransaction(data: any) {
    return api.post('/verify-transaction', {
      md5: data,
    })
  }

  // oxlint-disable-next-line no-unused-vars
  createOrder(data: any) {
    return api.post('/orders', data)
  }

  // -----
  getList() {
    return api.get('/orders')
  }

  updateStatus(id: number, status: string) {
    return api.put(`/orders/${id}`, {
      status: status,
    })
  }

  getDash() {
    return api.get('//dashboard/orders')
  }

  updateItemStatus(itemId: number, status: string) {
    return api.put(`/orders/items/${itemId}`, {
      status: status,
    })
  }

  updatePaymentStatus(orderId: number, data: FormData) {
    return api.put(`/orders/${orderId}/payment`, data)
  }
}

export default new OrderProvider()
