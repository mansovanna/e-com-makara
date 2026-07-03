import type { CategoryModelResponeAPI } from './categories_model'

export interface FoodModel {
  id: number | null
  category_id: number | null
  name: string
  image: File | string | null
  image_url: string | null
  price: number | null
  description: string
  status: 'active' | 'inactive' | ''
  categories: CategoryModelResponeAPI | null
}

export interface FoodModelResponseAPI {
  data: FoodModel[]
  message: string
  status: number
}
