<template>
  <router-view />
  <GlobalToast />
</template>

<script setup>
// Root komponen - seluruh layout ditentukan per-route (customer/admin/superadmin layout)
import GlobalToast from './components/shared/GlobalToast.vue'
</script>
