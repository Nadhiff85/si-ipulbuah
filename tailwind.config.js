/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      colors: {
        // Palet "IPUL BUAH" - "Tropical Bold": coral berani + hijau tua + lime
        primary: {
          DEFAULT: '#123524', // hijau tua - header, sidebar, tombol utama
          light: '#1F5C3D',
          dark: '#081C10',    // sidebar admin/superadmin (digelapkan)
        },
        accent: {
          DEFAULT: '#FF5A36', // coral berani - CTA (checkout, beli)
          light: '#FF7A55',
        },
        badge: '#7CA300',      // lime tua - best seller/promo/musiman (tetap terbaca sbg teks)
        surface: {
          DEFAULT: '#FFFFFF',
          soft: '#FFF6EC',
        },
        ink: '#1F1206',       // teks utama
        success: '#34D399',
        warning: '#F2A900',
        danger: '#E63946',
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui'],
        display: ['Bricolage Grotesque', 'Plus Jakarta Sans', 'ui-sans-serif'],
      },
      borderRadius: {
        xl2: '1.25rem',
      },
    },
  },
  plugins: [],
}