/* eslint-disable @typescript-eslint/no-explicit-any */
import api from '@/config/api'

class CategoriesProviders {
  getAllData(search: string, per_page: number, page: number) {
    return api.get('/categories', {
      params: {
        search: search,
        per_page: per_page,
        page: page,
      },
    })
  }
  create(data: any) {
    return api.post('/categories', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  }
  update(id: number, data: any) {
    return api.post(`/categories/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  }
  delete(id: number) {
    return api.delete(`/categories/${id}`)
  }
}

export default new CategoriesProviders()
