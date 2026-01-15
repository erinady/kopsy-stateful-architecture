<script setup>
    import { Head } from '@inertiajs/vue3';
    import ThemeProvider from '@/Layouts/Admin/ThemeProvider.vue';
    import ThemeToggler from '@/Components/ThemeToggler.vue';

    const props = defineProps({
        title: {
            type: String,
            default: ''
        }
    });
</script>

<template>
    <Head>
        <title>{{ title }} - Koperasi Syariah Warga Polban</title>
    </Head>
    <ThemeProvider>
      <div
        class="min-h-screen bg-no-repeat bg-center bg-cover"
        style="background-image: url('/images/auth/bg_auth.png')"
      >
        <div class="min-h-screen bg-blue-900/60 flex items-center justify-center relative">
          <div class="absolute top-6 right-6">
            <ThemeToggler />
          </div>
          <slot />
        </div>
      </div>
    </ThemeProvider>
</template>
