/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      colors: {
        // Palet "Fresh Orchard" - IPUL BUAH
        primary: {
          DEFAULT: '#2E7D32', // hijau segar - header, sidebar, tombol utama
          light: '#4CAF50',
          dark: '#1B5E20',    // sidebar admin/superadmin
        },
        accent: {
          DEFAULT: '#FB8C00', // oranye hangat - CTA (checkout, beli)
          light: '#FF9800',
        },
        badge: '#FFC107',     // kuning lembut - best seller/promo/musiman
        surface: {
          DEFAULT: '#FFFFFF',
          soft: '#FAFAF7',
        },
        ink: '#2C2C2C',       // teks utama
        success: '#81C784',
        warning: '#FFA726',
        danger: '#E53935',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
      },
      borderRadius: {
        xl2: '1.25rem',
      },
    },
  },
  plugins: [],
}
