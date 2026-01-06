<script setup lang="ts">
import MenuLinks from '@/Components/onePage/MenuLinks.vue';
import NavMenu from '@/Components/onePage/NavMenu.vue';
import { showSnackbar, snackbarContent } from '@/snackbarService';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <Head v-if="title" :title="title" />

    <v-app>
        <v-app-bar flat class="appbar--bordered" color="#214d63" height="80">
            <!-- Mobile Burger + Drawer -->
            <div class="d-flex mx-4 w-100 justify-space-between align-center d-md-none position-relative">
                <NavMenu></NavMenu>

                <Link href="/one" aria-label="Zur Startseite">
                    <v-img width="44px" src="/img/logo.png" alt="Hrta-Logo" />
                </Link>
            </div>

            <div class="appbar-inner d-none d-md-flex">
                <div class="left-group">
                    <Link href="/one" aria-label="Zur Startseite">
                        <v-img width="66px" src="/img/logo.png" alt="Hrta-Logo" />
                    </Link>
                    <MenuLinks />
                </div>
            </div>
        </v-app-bar>

        <v-main>
            <div style="background-image: linear-gradient(rgb(239, 246, 255), rgb(255, 255, 255))"></div>
            <slot />
        </v-main>
        <v-footer id="footer" style="background-color: #214d63" class="pt-12">
            <v-container>
                <v-img class="mx-auto" eager :width="140" src="/img/logo.png" alt="Hrta-Logo" />
                <v-divider class="mt-12 mb-4" style="border: 1px solid grey" />
                <nav aria-label="Rechtliches">
                    <div class="d-flex justify-center ga-4 ga-sm-12 mx-auto">
                        <a class="menuLink text-grey-lighten-2" href="https://mbd-team.de/agb" target="_blank" rel="noopener">AGB</a>
                        <a class="menuLink text-grey-lighten-2" href="https://mbd-team.de/datenschutz" target="_blank" rel="noopener">Datenschutz</a>
                        <a class="menuLink text-grey-lighten-2" href="https://mbd-team.de/impressum" target="_blank" rel="noopener">Impressum</a>
                    </div>
                </nav>
            </v-container>
        </v-footer>
        <v-snackbar
            v-model="showSnackbar"
            id="snackbar"
            :style="{ '--v-snackbar-wrapper-max-width': '100%' }"
            :timeout="5000"
            color="#2563eb"
            location="top"
            transition="slide-y-transition"
        >
            <div style="white-space: pre; text-wrap-mode: wrap; text-align: center">{{ snackbarContent }}</div>
        </v-snackbar>
    </v-app>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}
.menuLink {
    text-decoration: none;
}

:deep(#snackbar .v-snackbar__wrapper) {
    width: 100% !important;
    max-width: 100% !important;
}

.appbar-inner {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-left: 12.5%;
}

.left-group {
    display: flex;
    align-items: center;
    gap: 20px;
}
.right-group {
    display: flex;
    align-items: center;
}

.nav-btn {
    text-transform: none;
    transition: background-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
}
.nav-btn:hover {
    background-color: rgb(239, 246, 255);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    transform: translateY(-1px);
}

.appbar--bordered {
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 5px 10px -12px rgba(0, 0, 0, 0.15);
}

.subheadline {
    margin-inline: 25% !important;
}
@media (max-width: 960px) {
    .appbar-inner {
        padding-left: 12px;
        padding-right: 12px;
    }
    .subheadline {
        font-size: 20px !important;
        margin-inline: 10% !important;
    }
}

:deep(.v-theme--dark) .appbar--bordered {
    border-bottom-color: rgba(255, 255, 255, 0.12);
    box-shadow: 0 12px 24px -14px rgba(0, 0, 0, 0.55);
}
</style>

<style>
section {
    max-width: 1100px;
    margin-inline: auto;
}
:root {
    scroll-behavior: smooth;
    scroll-padding-top: 80px;
}
</style>
