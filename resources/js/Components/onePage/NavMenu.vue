<script setup lang="ts">
import { ref } from 'vue';

const showMobileMenu = ref(false);
</script>

<template>
    <nav class="hamburger-menu" style="position: relative; height: 26px">
        <button
            id="menu__toggle"
            style="position: relative; width: 26px; height: 26px; border: 0; background-color: transparent"
            :pressed="showMobileMenu"
            @click.stop="showMobileMenu = !showMobileMenu"
            :aria-label="showMobileMenu ? 'Menü schließen' : 'Menü öffnen'"
        >
            <div class="menu__btn"></div>
        </button>
        <div class="menu__wrapper" @click="showMobileMenu = false" style="height: 100vh; width: 100vw; position: fixed; top: 0">
            <menu class="menu__box" @click.stop="() => {}" :aria-expanded="showMobileMenu">
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#tools">Tools</a></li>
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#tide">Tide</a></li>
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#flow">Flow</a></li>
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#isa">Isa</a></li>
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#preise">Preise</a></li>
                <li @click.stop="showMobileMenu = false"><a class="menuLink" href="#kontakt">Kontakt</a></li>
            </menu>
        </div>
    </nav>
</template>

<style>
.menu {
    display: block;
}

.hamburger-menu {
    display: none;
}

@media (max-width: 982px) {
    .hamburger-menu {
        display: block;
    }
    .menu {
        display: none;
    }
}
#menu__toggle {
    cursor: pointer;
    z-index: 1;
}
#menu__toggle[pressed='true'] .menu__btn {
    height: 0px;
}
#menu__toggle[pressed='true'] .menu__btn::before {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg);
    background-color: #214d63;
}
#menu__toggle[pressed='true'] .menu__btn::after {
    top: 0;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    background-color: #214d63;
}
#menu__toggle[pressed='true'] ~ .menu__wrapper > .menu__box,
#menu__toggle[pressed='true'] ~ .menu__wrapper {
    right: 0 !important;
}

#menu__toggle[pressed='false'] ~ .menu__wrapper {
    right: -100vw !important;
}

.menu__btn {
    cursor: pointer;
    z-index: 1;
    position: absolute;
    left: 50%;
    transform: translate(-50%, -50%);
}
.menu__btn,
.menu__btn::before,
.menu__btn::after {
    display: block;
    position: absolute;
    width: 100%;
    height: 2px;
    background-color: rgb(0, 189, 157);
    transition-duration: 0.25s;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.menu__btn::before {
    content: '';
    top: calc(50% - 8px);
}
.menu__btn::after {
    content: '';
    top: calc(50% + 8px);
}
.menu__box {
    --menu-width: 200px;
    display: block;
    position: fixed;
    top: 0;
    right: calc(-1 * var(--menu-width));
    width: var(--menu-width);
    height: 100vh;
    margin: 0;
    padding: 80px 0;
    list-style: none;
    background-color: rgba(0, 189, 157, 0.768);
    box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.4);
    transition-duration: 0.25s;
    backdrop-filter: blur(15px);
}

@media (min-width: 600px) {
    .menu__box {
        --menu-width: 250px;
    }
}

.menu__box a {
    display: block;
    padding: 12px 24px;
    color: #214d63;
    font-family: 'Roboto', sans-serif;
    font-size: 20px;
    font-weight: 600;
    text-decoration: none;
    transition-duration: 0.25s;
}
.menu__box a:hover {
    color: #f5f5f5;
}
</style>
