// types/order.ts

export interface Food {
  id: number
  category_id: number
  name: string
  image: string
  price: number
  discount_price: number | null
  is_discount: number
  description: string
  status: 'active' | 'inactive'
  created_at: string
  updated_at: string
  image_url: string
}

export interface OrderItem {
  id: number
  order_id: number
  food_id: number
  status: 'pending' | 'preparing' | 'ready' | 'served' | 'cancelled'
  created_at: string
  updated_at: string
  food: Food
}

export interface Table {
  id: number
  table_number: string
  status: 'available' | 'occupied' | string
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  order_no: string
  table_id: number
  note: string | null
  payment_status: 'unpaid' | 'partial' | 'paid' | 'refunded'
  payment_method: 'cash' | 'payway'
  total: number
  created_at: string
  updated_at: string
  items: OrderItem[]
  table: Table
}

export interface MyOrdersResponse {
  message: string
  data: Order[]
}
