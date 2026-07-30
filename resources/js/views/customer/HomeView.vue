<template>
  <div>
    <!-- ===== Film "Belah Buah" - hero sinematik yang discrub scroll =====
         Satu layar di-pin oleh GSAP ScrollTrigger; tiga babak (SEGAR/LENGKAP/MURAH)
         terbuka bergantian lewat wipe clip-path diagonal seperti irisan pisau,
         lalu larut ke krem konten di bawahnya. Dengan prefers-reduced-motion,
         hanya babak pembuka yang tampil sebagai hero statis biasa. -->
    <section ref="filmRef" class="relative h-screen overflow-hidden bg-primary-dark">

      <!-- Babak 0: pembuka. Latar hijau bergradasi kaya + buah cutout HD besar
           tersebar tak beraturan, tiap buah "bernapas" (float kontinu). -->
      <div class="absolute inset-0">
        <!-- Latar bergradasi berlapis: glow hijau di atas-tengah, gelap di sudut -->
        <div class="absolute inset-0" style="background:
          radial-gradient(ellipse 75% 55% at 50% 18%, rgba(31,92,61,0.95), transparent 70%),
          radial-gradient(ellipse 60% 60% at 88% 95%, rgba(8,28,16,0.9), transparent 65%),
          radial-gradient(ellipse 55% 55% at 8% 90%, rgba(20,64,26,0.7), transparent 60%),
          linear-gradient(160deg, #164A30 0%, #0E3020 45%, #081C10 100%);"></div>
        <!-- Bintik lembut biar tidak flat -->
        <div class="absolute inset-0 opacity-[0.15]" style="background-image: radial-gradient(rgba(255,255,255,0.7) 1px, transparent 1px); background-size: 42px 42px;"></div>

        <!-- Buah tersebar tak beraturan, ukuran besar & jelas -->
        <img src="/images/jeruk.webp" alt="" class="film-fruit absolute -top-10 -left-10 w-56 md:w-80 object-contain drop-shadow-2xl -rotate-[8deg]" />
        <img src="/images/apple.png" alt="" class="film-fruit absolute top-[14%] left-[13%] w-40 md:w-56 object-contain drop-shadow-2xl rotate-[10deg]" />
        <img src="/images/anggur.png" alt="" class="film-fruit absolute -top-6 right-[6%] w-56 md:w-80 object-contain drop-shadow-2xl rotate-[7deg]" />
        <img src="/images/semangka.webp" alt="" class="film-fruit absolute -bottom-14 -left-12 w-72 md:w-[26rem] object-contain drop-shadow-2xl rotate-[6deg]" />
        <img src="/images/alpukat.png" alt="" class="film-fruit absolute -bottom-10 right-[4%] w-72 md:w-96 object-contain drop-shadow-2xl -rotate-[6deg]" />

        <div class="film-intro-inner relative h-full max-w-3xl mx-auto px-4 flex flex-col items-center justify-center text-center">
          <span class="film-intro-soft inline-block bg-ink text-badge font-black px-4 py-2 rounded-full text-xs mb-6 uppercase tracking-widest">
            Fresh • Lengkap • Murah
          </span>

          <h1 class="font-display font-extrabold text-white text-6xl md:text-8xl tracking-tight leading-none mb-5 overflow-hidden" aria-label="IPUL BUAH">
            <span v-for="(ch, i) in 'IPUL BUAH'" :key="i" class="film-char inline-block" aria-hidden="true">{{ ch === ' ' ? ' ' : ch }}</span>
          </h1>

          <p class="film-intro-soft text-white/90 text-lg md:text-xl mb-9 leading-relaxed font-medium max-w-lg">
            Pilihan buah paling lengkap dengan harga terjangkau. Melayani Kota Palu,
            Kabupaten Sigi & Kabupaten Donggala.
          </p>

          <div class="film-intro-soft flex flex-wrap justify-center gap-4 mb-5">
            <button @click="goLogin('/katalog')" class="bg-accent hover:bg-accent-light text-white font-extrabold text-base md:text-lg px-10 py-4 rounded-full transition shadow-lg hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
              Mulai Belanja
            </button>
            <button @click="goLogin('/parsel-kustom')" class="bg-transparent border-2 border-white/60 text-white font-extrabold text-base md:text-lg px-10 py-4 rounded-full hover:bg-white/10 transition inline-flex items-center gap-2 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer">
              <GiftIcon class="w-5 h-5" stroke-width="2.5" /> Buat Parsel Kustom
            </button>
          </div>

          <button @click="goLogin('/parsel-kustom')" class="film-intro-soft inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-semibold pl-2.5 pr-4 py-2 rounded-full transition cursor-pointer">
            <span class="bg-badge text-ink text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide">Baru</span>
            <SparklesIcon class="w-4 h-4 text-badge shrink-0" stroke-width="2" />
            Rangkai sendiri parsel buah untuk hadiah & momen spesial
          </button>
        </div>
      </div>

      <!-- Babak 1: SEGAR - foto kelompok buah 1 full-bleed + scrim biar teks terbaca -->
      <div class="film-panel panel-segar absolute inset-0" :class="reducedMotion ? 'hidden' : ''" style="clip-path: polygon(0% 0%, 0% 0%, -22% 100%, -22% 100%); will-change: clip-path;">
        <img src="/images/kelompok-1.jpeg" alt="" class="film-img absolute inset-0 w-full h-full object-cover blur-[2px]" style="will-change: transform;" />
        <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/40 to-primary/65"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_55%_50%_at_50%_48%,rgba(8,28,16,0.5),transparent_75%)]"></div>
        <div class="film-blade absolute inset-y-0 -left-1/3 w-56 -skew-x-12 bg-gradient-to-r from-transparent via-white/85 to-transparent"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
          <h2 class="film-word font-display font-extrabold italic text-white text-8xl md:text-[11rem] leading-[0.85] tracking-tighter" style="text-shadow: 0 8px 40px rgba(0,0,0,0.6)">SEGAR</h2>
          <span class="film-word block h-1.5 w-28 mt-5 rounded-full bg-accent"></span>
          <p class="film-word text-white text-lg md:text-2xl font-semibold mt-6 max-w-xl" style="text-shadow: 0 2px 14px rgba(0,0,0,0.7)">Dipetik &amp; masuk etalase di hari yang sama</p>
        </div>
      </div>

      <!-- Babak 2: LENGKAP - foto kelompok buah 2 full-bleed -->
      <div class="film-panel panel-lengkap absolute inset-0" :class="reducedMotion ? 'hidden' : ''" style="clip-path: polygon(122% 0%, 122% 0%, 100% 100%, 100% 100%); will-change: clip-path;">
        <img src="/images/kelompok-2.jpeg" alt="" class="film-img absolute inset-0 w-full h-full object-cover blur-[2px]" style="will-change: transform;" />
        <div class="absolute inset-0 bg-gradient-to-t from-accent/90 via-accent/40 to-accent/65"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_55%_50%_at_50%_48%,rgba(90,25,10,0.45),transparent_75%)]"></div>
        <div class="film-blade absolute inset-y-0 -right-1/3 w-56 skew-x-12 bg-gradient-to-r from-transparent via-white/85 to-transparent"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
          <h2 class="film-word font-display font-extrabold italic text-white text-8xl md:text-[11rem] leading-[0.85] tracking-tighter" style="text-shadow: 0 8px 40px rgba(0,0,0,0.6)">LENGKAP</h2>
          <span class="film-word block h-1.5 w-28 mt-5 rounded-full bg-white"></span>
          <p class="film-word text-white text-lg md:text-2xl font-semibold mt-6 max-w-xl" style="text-shadow: 0 2px 14px rgba(0,0,0,0.7)">Buah lokal, impor, musiman, sampai semangka & melon belah — semua ada</p>
        </div>
      </div>

      <!-- Babak 3: MURAH (penutup) - foto kelompok buah 3 full-bleed, scrim gelap -->
      <div class="film-panel panel-murah absolute inset-0" :class="reducedMotion ? 'hidden' : ''" style="clip-path: polygon(0% 122%, 100% 122%, 100% 100%, 0% 100%); will-change: clip-path;">
        <img src="/images/kelompok-3.jpeg" alt="" class="film-img absolute inset-0 w-full h-full object-cover blur-[2px]" style="will-change: transform;" />
        <div class="absolute inset-0 bg-gradient-to-t from-ink/92 via-ink/55 to-ink/72"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_55%_at_50%_45%,rgba(0,0,0,0.5),transparent_75%)]"></div>
        <div class="film-blade absolute inset-x-0 -bottom-1/3 h-48 bg-gradient-to-t from-transparent via-white/75 to-transparent"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-4">
          <h2 class="film-word font-display font-extrabold italic text-white text-8xl md:text-[11rem] leading-[0.85] tracking-tighter" style="text-shadow: 0 8px 40px rgba(0,0,0,0.7)">MURAH</h2>
          <span class="film-word block h-1.5 w-28 mt-5 rounded-full bg-badge"></span>
          <p class="film-word text-white text-lg md:text-2xl font-semibold mt-6 max-w-xl" style="text-shadow: 0 2px 14px rgba(0,0,0,0.75)">Harga bersahabat setiap hari, gratis ongkir*</p>
          <div class="film-word flex flex-wrap justify-center gap-4 mt-8">
            <button @click="goLogin('/katalog')" class="bg-accent hover:bg-accent-light text-white font-extrabold px-10 py-4 rounded-full transition shadow-lg cursor-pointer">
              Mulai Belanja
            </button>
            <button @click="goLogin('/parsel-kustom')" class="border-2 border-white/70 text-white font-extrabold px-10 py-4 rounded-full hover:bg-white/10 transition inline-flex items-center gap-2 cursor-pointer">
              <GiftIcon class="w-5 h-5" stroke-width="2.5" /> Buat Parsel Kustom
            </button>
          </div>
        </div>
      </div>

      <!-- Petunjuk scroll -->
      <div class="film-hint absolute bottom-5 left-1/2 -translate-x-1/2 text-white/70 text-xs font-semibold tracking-widest uppercase animate-bounce" :class="reducedMotion ? 'hidden' : ''">
        Scroll ↓
      </div>
    </section>

    <!-- ===== Produk Terlaris ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-ink">Produk Terlaris</h2>
        <button @click="goLogin('/katalog')" class="text-primary text-sm font-medium hover:underline cursor-pointer">Lihat Semua →</button>
      </div>

      <div v-if="loadingFeatured" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <div v-for="i in 4" :key="i" class="glass-card rounded-xl2 overflow-hidden animate-pulse">
          <div class="aspect-[4/3] bg-ink/10"></div>
          <div class="p-4 space-y-2">
            <div class="h-3 bg-ink/10 rounded w-3/4"></div>
            <div class="h-3 bg-ink/10 rounded w-1/2"></div>
          </div>
        </div>
      </div>

      <p v-else-if="featuredProducts.length === 0" class="text-ink/40 text-sm">Belum ada produk terlaris yang dikurasi.</p>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
        <button
          v-for="p in featuredProducts"
          :key="p.id"
          @click="goLogin(`/produk/${p.slug}`)"
          class="text-left glass-card rounded-xl2 hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden cursor-pointer group"
        >
          <div class="aspect-[4/3] bg-primary/5 flex items-center justify-center relative overflow-hidden">
            <img
              v-if="p.images?.[0]?.image_path"
              :src="p.images[0].image_path"
              :alt="p.name"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
            />
            <PhotoIcon v-else class="w-10 h-10 text-ink/20" stroke-width="1.5" />
            <span v-if="p.labels?.[0]" class="absolute top-2.5 left-2.5 bg-badge text-ink text-[10px] font-bold px-2.5 py-1 rounded-full uppercase shadow-sm">
              {{ labelText(p.labels[0]) }}
            </span>
          </div>
          <div class="p-4">
            <p class="font-semibold text-sm text-ink truncate">{{ p.name }}</p>
            <p class="text-ink/40 text-xs mt-0.5">{{ p.origin_region }}</p>
            <p class="text-accent font-bold text-base mt-2 tabular-nums">Rp {{ formatPrice(p.price_unit) }}<span class="text-ink/40 font-normal text-xs">/{{ p.unit }}</span></p>
          </div>
        </button>
      </div>
    </section>

    <!-- ===== Ulasan Pelanggan (hanya tampil kalau sudah ada ulasan asli disetujui) ===== -->
    <section v-if="recentReviews.length > 0" class="max-w-7xl mx-auto px-4 py-10">
      <h2 class="text-xl font-bold text-ink mb-5">Kata Pelanggan Kami</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="r in recentReviews" :key="r.id" class="glass-card rounded-xl2 p-5">
          <div class="flex gap-0.5 mb-3">
            <StarIcon v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= r.rating ? 'text-badge' : 'text-ink/15'" />
          </div>
          <p class="text-sm text-ink/80 leading-relaxed mb-4">"{{ r.comment }}"</p>
          <p class="text-xs font-semibold text-ink">{{ r.customer_name }}<span v-if="r.product_name" class="text-ink/40 font-normal"> · {{ r.product_name }}</span></p>
        </div>
      </div>
    </section>

    <!-- ===== Buat Parsel Kustom ===== -->
    <section class="max-w-7xl mx-auto px-4 py-10">
      <div class="bg-primary rounded-xl2 overflow-hidden grid md:grid-cols-2 items-stretch">
        <div class="p-8 md:p-10 flex flex-col justify-center text-white">
          <span class="inline-flex items-center gap-1.5 bg-white/15 text-badge font-bold px-3 py-1.5 rounded-full text-xs uppercase tracking-wide w-fit mb-4">
            <GiftIcon class="w-4 h-4" stroke-width="2" /> Parsel Kustom
          </span>
          <h2 class="text-2xl md:text-3xl font-black mb-3 leading-tight">Rangkai Sendiri Parsel Buahmu</h2>
          <p class="text-white/80 text-sm md:text-base mb-6 leading-relaxed">
            Pilih sendiri isi, kemasan, dan kartu ucapan — cocok untuk hadiah ulang tahun,
            parsel lebaran, atau kado sehat untuk orang tersayang.
          </p>
          <button
            @click="goLogin('/parsel-kustom')"
            class="bg-accent hover:bg-accent-light text-white font-bold px-6 py-3 rounded-full transition w-fit inline-flex items-center gap-2 cursor-pointer"
          >
            Mulai Rangkai Parsel <ArrowRightIcon class="w-4 h-4" stroke-width="2.5" />
          </button>
        </div>
        <div class="relative min-h-[220px] hidden md:block">
          <img src="/images/marquee/Buah%20Musiman.jpg" alt="Parsel buah kustom" class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 bg-gradient-to-l from-transparent to-primary/40"></div>
        </div>
      </div>
    </section>

    <!-- ===== Banner Musiman & Promo ===== -->
    <section class="max-w-7xl mx-auto px-4 py-4 grid md:grid-cols-2 gap-5">
      <button
        @click="goLogin('/musiman')"
        class="text-left bg-accent rounded-xl2 p-6 text-white flex items-center justify-between hover:bg-accent-light transition cursor-pointer"
      >
        <div>
          <p class="font-bold text-lg mb-1">Buah Musiman</p>
          <p class="text-white/80 text-sm">Segar, enak & sedang musim sekarang!</p>
        </div>
        <span><SunIcon class="w-9 h-9" stroke-width="1.5" /></span>
      </button>
      <button
        @click="goLogin('/promo')"
        class="text-left bg-badge rounded-xl2 p-6 text-primary-dark flex items-center justify-between hover:opacity-90 transition cursor-pointer"
      >
        <div>
          <p class="font-bold text-lg mb-1">Promo Spesial</p>
          <p class="text-primary-dark/80 text-sm">Cek penawaran & diskon minggu ini</p>
        </div>
        <span><TagIcon class="w-9 h-9" stroke-width="1.5" /></span>
      </button>
    </section>

    <!-- ===== Lokasi Toko ===== -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-xl font-bold text-ink mb-1">Lokasi Toko Kami</h2>
      <p class="text-ink/60 text-sm mb-5">Peta interaktif - bisa digeser & di-zoom langsung</p>

      <div class="grid md:grid-cols-3 gap-5">
        <div class="md:col-span-2">
          <div class="rounded-xl2 overflow-hidden shadow-sm border border-ink/10">
            <iframe
              :src="storeInfo.googleMapsEmbedUrl"
              class="w-full h-80 md:h-96"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>

          <div class="mt-3 glass-card rounded-xl2 p-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-sm text-ink truncate">{{ storeInfo.storeName }}</p>
              <p class="text-xs text-ink/50 truncate">{{ storeInfo.address }}</p>
            </div>
            <div class="flex gap-2 shrink-0">
              <a
                :href="storeInfo.googleMapsLink"
                target="_blank"
                title="Buka di Google Maps"
                class="w-9 h-9 rounded-full bg-primary/10 hover:bg-primary/20 flex items-center justify-center transition"
              >
                <ArrowTopRightOnSquareIcon class="w-4 h-4 text-primary" stroke-width="1.75" />
              </a>
              <a
                :href="storeInfo.googleMapsLink"
                target="_blank"
                title="Lihat Rute"
                class="w-9 h-9 rounded-full bg-accent/10 hover:bg-accent/20 flex items-center justify-center transition"
              >
                <MapPinIcon class="w-4 h-4 text-accent" stroke-width="1.75" />
              </a>
            </div>
          </div>
        </div>

        <div class="glass-card rounded-xl2 p-5 space-y-4">
          <div>
            <p class="text-xs text-ink/50 mb-1">Status Toko</p>
            <span
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
              :class="storeInfo.isOpenNow ? 'bg-success/20 text-success' : 'bg-danger/10 text-danger'"
            >
              ● {{ storeInfo.isOpenNow ? 'Buka Sekarang' : 'Tutup' }}
            </span>
          </div>
          <div>
            <p class="text-xs text-ink/50 mb-1">Jam Operasional</p>
            <p class="text-sm font-medium text-ink">
              {{ storeInfo.operatingHours.buka }} - {{ storeInfo.operatingHours.tutup }} WITA
            </p>
          </div>
          <div>
            <p class="text-xs text-ink/50 mb-1">Alamat</p>
            <p class="text-sm text-ink leading-relaxed">{{ storeInfo.address }}</p>
          </div>
          <a
            :href="storeInfo.googleMapsLink"
            target="_blank"
            class="flex items-center justify-center gap-1.5 text-center bg-primary text-white text-sm font-semibold py-2.5 rounded-full hover:bg-primary-dark transition"
          >
            <MapPinIcon class="w-4 h-4" stroke-width="1.75" /> Lihat Rute
          </a>
          <a
            :href="storeInfo.whatsappUrl"
            target="_blank"
            class="flex items-center justify-center gap-1.5 text-center border border-success text-success text-sm font-semibold py-2.5 rounded-full hover:bg-success/10 transition"
          >
            <ChatBubbleLeftRightIcon class="w-4 h-4" stroke-width="1.75" /> Hubungi via WhatsApp
          </a>
        </div>
      </div>
    </section>

    <!-- ===== CTA Daftar ===== -->
    <section class="max-w-7xl mx-auto px-4 pb-16">
      <div class="bg-primary-dark rounded-xl2 p-8 md:p-10 text-center text-white">
        <h2 class="text-2xl font-bold mb-2">Yuk, Mulai Belanja Buah Segar!</h2>
        <p class="text-white/70 mb-6">Daftar sekarang untuk lihat katalog lengkap, harga, dan mulai pesan dari rumah.</p>
        <div class="flex justify-center gap-3">
          <router-link to="/register" class="bg-accent hover:bg-accent-light text-white font-semibold px-6 py-3 rounded-full transition">
            Daftar Sekarang
          </router-link>
          <router-link to="/login" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-6 py-3 rounded-full transition">
            Sudah Punya Akun? Masuk
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useStoreInfoStore } from '../../stores/store'
import api from '../../services/api'
import {
  PhotoIcon, TagIcon, GiftIcon, SunIcon, MapPinIcon, ChatBubbleLeftRightIcon, ArrowTopRightOnSquareIcon,
  ArrowRightIcon, SparklesIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon } from '@heroicons/vue/24/solid'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import Lenis from 'lenis'

gsap.registerPlugin(ScrollTrigger)

const router = useRouter()
const auth = useAuthStore()
const storeInfo = useStoreInfoStore()

// ===== Film "Belah Buah" =====
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const filmRef = ref(null)
let lenis = null
let filmCtx = null
let introSafety = null
const lenisTick = (t) => lenis?.raf(t * 1000)

function initFilm() {
  // Kontrak dev: ?jump=<y> mendarat langsung di posisi scroll itu (untuk verifikasi),
  // window.__ready menandai halaman siap di-screenshot.
  const jump = new URLSearchParams(location.search).get('jump')
  if (jump !== null) history.scrollRestoration = 'manual'

  if (reducedMotion || !filmRef.value) {
    window.__ready = true
    return
  }

  lenis = new Lenis({ lerp: 0.09, smoothWheel: true })
  lenis.on('scroll', ScrollTrigger.update)
  gsap.ticker.add(lenisTick)
  gsap.ticker.lagSmoothing(0)

  filmCtx = gsap.context(() => {
    // Sembunyikan elemen intro & kata SEBELUM paint (gsap.set = inline style,
    // menang atas class Tailwind - ini yang bikin reveal dulu selalu blank).
    gsap.set('.film-char', { yPercent: 120, autoAlpha: 0 })
    gsap.set('.film-intro-soft', { y: 18, autoAlpha: 0 })
    gsap.set('.film-word', { autoAlpha: 0 })

    gsap.set('.film-fruit', { autoAlpha: 0, scale: 0.55 })
    gsap.to('.film-fruit', {
      autoAlpha: 1, scale: 1, duration: 0.3, ease: 'power2.out',
      stagger: { each: 0.08, from: 'random' }, delay: 0.15,
      onComplete() {
        gsap.utils.toArray('.film-fruit').forEach((el) => {
          gsap.to(el, {
            yPercent: gsap.utils.random(-9, -3),
            xPercent: gsap.utils.random(-4, 4),
            duration: gsap.utils.random(2.8, 4.4),
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: gsap.utils.random(0, 0.4),
          })
        })
      },
    })

    // Reveal pembuka saat load (sekali jalan, bukan scroll-driven)
    const introTl = gsap.timeline({ delay: 0.15 })
    introTl.to('.film-char', { yPercent: 0, autoAlpha: 1, stagger: 0.045, duration: 0.9, ease: 'power4.out' })
    introTl.to('.film-intro-soft', { y: 0, autoAlpha: 1, stagger: 0.08, duration: 0.7, ease: 'power3.out' }, 0.4)

    // Jaring pengaman keandalan demo: kalau rAF sempat beku saat load (tab
    // belum fokus) dan reveal tak sempat selesai, paksa tampil - progress(1)
    // sinkron, tak butuh rAF. Di kondisi normal ini no-op (sudah selesai).
    introSafety = setTimeout(() => {
      if (introTl.progress() < 1) introTl.progress(1)
      gsap.set('.film-fruit', { autoAlpha: 1, scale: 1 })
    }, 2600)

    // HUKUM URUTAN ScrollTrigger: scene yang di-pin HARUS dibuat lebih dulu -
    // trigger yang dibuat setelahnya baru menghitung posisi dengan benar
    // terhadap pin spacer.
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: filmRef.value,
        start: 'top top',
        end: '+=200%',
        pin: true,
        scrub: 1,
        invalidateOnRefresh: true,
      },
    })

    tl.to('.film-hint', { autoAlpha: 0, duration: 0.03, ease: 'none' }, 0.01)
    tl.to('.film-intro-inner', { scale: 0.9, autoAlpha: 0.25, duration: 0.22, ease: 'power1.in' }, 0.02)

    // Helper satu babak: bilah pisau menyapu -> irisan clip terbuka -> foto
    // ken-burns -> kata muncul overshoot. "at" = titik mulai di timeline 0..1.
    const babak = (sel, clipTo, bladeTo, at) => {
      tl.fromTo(`${sel} .film-blade`,
        { autoAlpha: 0, [bladeTo.axis]: bladeTo.from },
        { autoAlpha: 1, [bladeTo.axis]: bladeTo.mid, duration: 0.06, ease: 'none' }, at)
      tl.to(`${sel} .film-blade`,
        { [bladeTo.axis]: bladeTo.to, autoAlpha: 0, duration: 0.16, ease: 'none' }, at + 0.06)
      tl.fromTo(sel,
        { clipPath: clipTo.from },
        { clipPath: clipTo.to, duration: 0.22, ease: 'power2.inOut' }, at + 0.02)
      tl.fromTo(`${sel} .film-img`,
        { scale: 1.25 }, { scale: 1.03, duration: 0.5, ease: 'none' }, at + 0.02)
      tl.fromTo(`${sel} .film-word`,
        { yPercent: 55, autoAlpha: 0, scale: 0.9 },
        { yPercent: 0, autoAlpha: 1, scale: 1, stagger: 0.05, duration: 0.16, ease: 'back.out(1.6)' }, at + 0.12)
    }

    // Babak 1: SEGAR - irisan diagonal dari kiri, bilah sapu kiri->kanan
    babak('.panel-segar',
      { from: 'polygon(0% 0%, 0% 0%, -22% 100%, -22% 100%)', to: 'polygon(0% 0%, 122% 0%, 100% 100%, -22% 100%)' },
      { axis: 'xPercent', from: -120, mid: 40, to: 260 }, 0.06)

    // Babak 2: LENGKAP - irisan diagonal dari kanan, bilah sapu kanan->kiri
    babak('.panel-lengkap',
      { from: 'polygon(122% 0%, 122% 0%, 100% 100%, 100% 100%)', to: 'polygon(-22% 0%, 122% 0%, 100% 100%, 0% 100%)' },
      { axis: 'xPercent', from: 120, mid: -40, to: -260 }, 0.34)

    // Babak 3: MURAH (penutup) - irisan naik dari bawah, bilah sapu bawah->atas.
    // Selesai reveal ~0.84 lalu DITAHAN sampai ujung pin (1.0) supaya tidak
    // gampang terlewat saat scroll pelan. Fotonya terus zoom pelan biar hidup.
    babak('.panel-murah',
      { from: 'polygon(0% 122%, 100% 122%, 100% 100%, 0% 100%)', to: 'polygon(0% -22%, 100% -22%, 100% 100%, 0% 100%)' },
      { axis: 'yPercent', from: 120, mid: -40, to: -280 }, 0.60)
    tl.to('.panel-murah .film-img', { scale: 1.08, duration: 0.16, ease: 'none' }, 0.84)

    // TANPA fade krem: begitu MURAH selesai ditahan, pin lepas dan langsung
    // masuk ke "Produk Terlaris" - tidak ada lagi scroll kosong.
  }, filmRef.value)

  if (jump !== null) {
    lenis.scrollTo(+jump || 0, { immediate: true })
    ScrollTrigger.update()
  }
  window.__ready = true
}

onMounted(() => {
  storeInfo.fetchStoreInfo()
  fetchFeaturedProducts()
  fetchRecentReviews()
  initFilm()
})

onUnmounted(() => {
  // Bersihkan pin/trigger & smooth-scroll saat pindah halaman SPA,
  // supaya halaman lain tidak ikut terpengaruh.
  clearTimeout(introSafety)
  filmCtx?.revert()
  gsap.ticker.remove(lenisTick)
  lenis?.destroy()
  lenis = null
})

// Fungsi login check
function goLogin(path) {
  if (auth.isLoggedIn) {
    router.push(path)
  } else {
    router.push({
      path: '/login',
      query: { redirect: path, reason: 'Masuk atau daftar dulu untuk melihat detail produk dan mulai belanja 🍊' },
    })
  }
}

const featuredProducts = ref([])
const loadingFeatured = ref(true)

async function fetchFeaturedProducts() {
  loadingFeatured.value = true
  try {
    const { data } = await api.get('/products/featured')
    featuredProducts.value = data.products
  } finally {
    loadingFeatured.value = false
  }
}

const recentReviews = ref([])

async function fetchRecentReviews() {
  try {
    const { data } = await api.get('/reviews/recent')
    recentReviews.value = data.reviews
  } catch {
    recentReviews.value = []
  }
}

function labelText(label) {
  return { segar: 'Segar', best_seller: 'Best Seller', musiman: 'Musiman', promo: 'Promo' }[label] || label
}

function formatPrice(v) {
  return new Intl.NumberFormat('id-ID').format(v)
}
</script>