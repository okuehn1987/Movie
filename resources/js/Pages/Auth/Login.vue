<script setup lang="ts">
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
// import { useOrganizationSettings } from "@/OrganizationTemplates";

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const adminForm = useForm({
    email: "",
    password: "",
});

const adminLogin = () => {
    adminForm.post(route("login"), {
        onFinish: () => {
            adminForm.reset("password");
        },
    });
};
// const settings = useOrganizationSettings();
</script>

<template>
    <GuestLayout title="Login">
        <div class="flex-grow-1 d-flex flex-column justify-center">
            <div v-if="status">
                <v-alert
                    v-if="status"
                    type="success"
                    class="mb-4"
                    :text="status"
                    variant="tonal"
                ></v-alert>
            </div>
            <v-form @submit.prevent="adminLogin" class="mt-8">
                <v-text-field
                    v-model="adminForm.email"
                    :readonly="adminForm.processing"
                    :errorMessages="adminForm.errors.email"
                    prepend-inner-icon="mdi-email-outline"
                    class="mb-2"
                    variant="solo"
                    label="Email"
                ></v-text-field>

                <v-text-field
                    v-model="adminForm.password"
                    :readonly="adminForm.processing"
                    :errorMessages="adminForm.errors.password"
                    type="password"
                    variant="solo"
                    prepend-inner-icon="mdi-lock-outline"
                    label="Passwort"
                ></v-text-field>
                <v-btn
                    :loading="adminForm.processing"
                    block
                    color="primary"
                    size="large"
                    type="submit"
                    variant="elevated"
                    >Login</v-btn
                >
                <v-card-text
                    class="pb-0 px-0 text-center mt-4 d-flex justify-space-between"
                >
                    <Link
                        :href="route('password.request')"
                        class="text-primary text-decoration-none py-2"
                    >
                        <v-icon icon="mdi-lock"></v-icon>
                        Passwort vergessen?
                    </Link>
                </v-card-text>
            </v-form>
        </div>
        <!-- <template #bottom>
            <div
                class="d-flex justify-space-evenly w-100"
                style="max-width: 600px"
            >
                <Link
                    :href="route('impressum')"
                    class="text-primary text-decoration-none py-2"
                    >Impressum</Link
                >
                <Link
                    :href="route('datenschutz')"
                    class="text-primary text-decoration-none py-2"
                    >Datenschutz</Link
                >
                <Link
                    :href="route('contact')"
                    class="text-primary text-decoration-none py-2"
                    >Kontakt</Link
                >
            </div>
        </template> -->
    </GuestLayout>
</template>
<style scoped></style>
