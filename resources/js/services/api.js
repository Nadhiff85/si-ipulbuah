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

// Penanganan error global: supaya tidak ada halaman yang gagal senyap tanpa
// pesan ke pengguna saat koneksi/server bermasalah. Error 422 (validasi form)
// sengaja TIDAK ditangani di sini - itu tetap tanggung jawab masing-masing
// komponen karena pesannya kontekstual ke form yang bersangkutan.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Lazy import supaya Pinia sudah pasti aktif saat store dipakai (dipanggil
    // saat request benar-benar gagal, bukan saat modul ini pertama dimuat).
    import('../stores/toast').then(({ useToastStore }) => {
      const toast = useToastStore()

      if (!error.response) {
        toast.show('Tidak bisa terhubung ke server. Cek koneksi internet Anda.')
      } else if (error.response.status === 401) {
        toast.show(error.response.data?.message || 'Sesi Anda telah berakhir, silakan masuk kembali.')
      } else if (error.response.status >= 500) {
        toast.show('Terjadi kesalahan di server. Coba lagi dalam beberapa saat.')
      }
    })

    if (error.response?.status === 401) {
      localStorage.removeItem('ipulbuah_token')
      localStorage.removeItem('ipulbuah_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
