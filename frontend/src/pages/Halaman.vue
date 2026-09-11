<script setup>
import { Head } from '@unhead/vue/components';
import { RouterLink as Link } from 'vue-router';
import MainLayout from '../layouts/MainLayout.vue';
import TextConponent from '../components/TextConponent.vue';
</script>

<template>

  <Head>
    <title>Beranda - halaman rental</title>
    <meta name="description" content="Sewa alat camping murah" />
  </Head>
  <MainLayout>
    <div class="p-6 bg-slate-100 min-h-screen">
      <h1 class="text-3xl font-bold text-blue-600">HALLO INI PINDAH HALAMAN</h1>
      <Link to="/home">Home</Link> <br>
      <a href="/vue/home">Home</a>
      <TextConponent></TextConponent>
    </div>
  </MainLayout>
</template>
