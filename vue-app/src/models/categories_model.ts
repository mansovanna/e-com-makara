interface CategoryModel {
  id: number
  name: string
  image: string
  image_url: string
  status: string
  created_at: string
  updated_at: string
}

export interface CategoryModelResponeAPI {
  status: boolean
  message: string
  data: CategoryModel[]
}
