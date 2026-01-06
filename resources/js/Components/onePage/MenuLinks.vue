<template>
    <div class="menu-links">
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#tools" variant="text">Tools</v-btn>
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#tide" variant="text">Tide</v-btn>
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#flow" variant="text">Flow</v-btn>
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#isa" variant="text">Isa</v-btn>
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#preise" variant="text">Preise</v-btn>
        <v-btn class="nav-btn text-body-1" color="grey-lighten-2" :ripple="false" href="#kontakt" variant="text">Kontakt</v-btn>
    </div>
</template>

<style scoped>
.menu-links {
    display: flex;
    align-items: center;
    gap: 20px;
}
.nav-btn {
    text-transform: none;
    transition: background-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
}
.nav-btn:hover {
    color: rgb(0, 189, 157) !important;
}

.nav-btn :deep(.v-btn__overlay),
.nav-btn :deep(.v-btn__underlay) {
    opacity: 0 !important;
}
</style>
