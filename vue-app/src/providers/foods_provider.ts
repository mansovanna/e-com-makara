/* eslint-disable @typescript-eslint/no-explicit-any */
import api from '@/config/api'

class FoodsProvider {
  getCategoriesList(search: string = '') {
    return api.get('/categories-list', {
      params: {
        search: search,
      },
    })
  }

  createFood(data: any) {
    return api.post('foods', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  }

  getFoodsList() {
    return api.get('/foods')
  }

  getFoodById(id: number) {
    return api.get(`foods/${id}`)
  }

  updateFood(id: number, data: any) {
    return api.post(`foods/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  }

  deleteFood(id: number) {
    return api.delete(`foods/${id}`)
  }

  // -----------------------------------------------------------
  getFoodCategoriesList(category_id: number) {
    return api.get('/food-categories-list', {
      params: {
        category_id: category_id,
      },
    })
  }
}

export default new FoodsProvider()
