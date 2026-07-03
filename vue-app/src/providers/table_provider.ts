/* eslint-disable @typescript-eslint/no-explicit-any */
import api from '@/config/api'

class TableProvider {
  getTableList() {
    return api.get('/tables')
  }

  createTable(data: any) {
    return api.post('/tables', data)
  }

  updateTable(id: number, data: any) {
    return api.put(`/tables/${id}`, data)
  }

  deleteTable(id: number) {
    return api.delete(`/tables/${id}`)
  }
}

export default new TableProvider()
