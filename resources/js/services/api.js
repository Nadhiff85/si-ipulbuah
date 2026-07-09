import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
})

// Sisipkan token Sanctum ke setiap request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('ipulbuah_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Redirect ke login jika token kedaluwarsa/tidak valid
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('ipulbuah_token')
      localStorage.removeItem('ipulbuah_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
