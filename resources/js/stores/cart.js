import { defineStore } from 'pinia'
import api from '../services/api'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),

  getters: {
    itemCount: (state) => state.items.reduce((sum, i) => sum + i.qty, 0),
    subtotal: (state) => state.items.reduce((sum, i) => sum + i.price * i.qty, 0),
  },

  actions: {
    async fetchCart() {
      const { data } = await api.get('/cart')
      this.items = data.items
    },
    async addItem(payload) {
      const { data } = await api.post('/cart/items', payload)
      this.items = data.items
    },
    async updateItem(itemId, qty) {
      const { data } = await api.patch(`/cart/items/${itemId}`, { qty })
      this.items = data.items
    },
    async removeItem(itemId) {
      const { data } = await api.delete(`/cart/items/${itemId}`)
      this.items = data.items
    },
  },
})
