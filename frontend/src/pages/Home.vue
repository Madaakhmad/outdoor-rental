<script setup>
import { Head } from '@unhead/vue/components';
import { RouterLink as Link } from 'vue-router';
import MainLayout from '../layouts/MainLayout.vue';
import TextConponent from '../components/TextConponent.vue';
</script>

<template>

  <Head>
    <title>Beranda - Outdoor Rental</title>
    <meta name="description" content="Sewa alat camping murah" />
  </Head>
  <MainLayout>
    <div class="p-6 bg-slate-100 min-h-screen">
      <h1 class="text-3xl font-bold text-blue-600">HALLO INI HALAMAN HOME</h1>
      <Link to="/halaman">Halaman</Link>
      <TextConponent></TextConponent>
    </div>
  </MainLayout>
</template>
