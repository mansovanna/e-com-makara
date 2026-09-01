/* eslint-disable @typescript-eslint/no-explicit-any */
import api from '@/config/api'

class MyOrderProvider {
  getMyOrders(idlist: any) {
    return api.get('my-order-id', {
      params: {
        'my-order-id': idlist,
      },
    })
  }
}

export default new MyOrderProvider()
