import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('ipulbuah_user') || 'null'),
    token: localStorage.getItem('ipulbuah_token') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
  },

  actions: {
    async login(credentials) {
      const { data } = await api.post('/login', credentials)
      this.setSession(data.user, data.token)
      return data.user
    },

    async register(payload) {
      const { data } = await api.post('/register', payload)
      this.setSession(data.user, data.token)
      return data.user
    },

    async logout() {
      try { await api.post('/logout') } catch (e) { /* ignore */ }
      this.user = null
      this.token = null
      localStorage.removeItem('ipulbuah_user')
      localStorage.removeItem('ipulbuah_token')
    },

    setSession(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('ipulbuah_user', JSON.stringify(user))
      localStorage.setItem('ipulbuah_token', token)
    },
  },
})
